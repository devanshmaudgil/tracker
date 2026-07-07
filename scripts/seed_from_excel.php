<?php
/**
 * Seed clients, regions, candidates, tracker_info, tracker_candidates and
 * candidate_pipeline_status from the 2026 recruitment tracker Excel workbook.
 *
 * Usage:  php scripts/seed_from_excel.php [--fresh]
 *   --fresh  wipes existing imported data (trackers/candidates/clients) first.
 *            Use this whenever the Excel file is updated — it is the source of truth.
 *
 * Safe to re-run: uses firstOrCreate / updateOrCreate keyed on natural keys.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Client;
use App\Models\Region;
use App\Models\StaffUser;
use App\Models\Month;
use App\Models\Candidate;
use App\Models\TrackerInfo;
use App\Models\TrackerCandidate;
use App\Models\CandidatePipelineStatus;
use App\Models\JobStatus;
use App\Models\UserLogin;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;

$FILE = __DIR__ . '/../Recruitment_Staffing_Tracker_RADiiX_INFINITEii_Year_2026_SYNCED.xlsx';
$SHEET = 'Recruitment Tracker-RADiiX_2026';
$DATA_START = 5;
$fresh = in_array('--fresh', $argv, true);

/* --------------------------------------------------------------------- */
/* Helpers                                                                */
/* --------------------------------------------------------------------- */

function cell($sheet, $col, $row)
{
    $v = $sheet->getCell($col . $row)->getValue();
    if (is_string($v)) {
        $v = trim($v);
        // normalise the odd characters used in the workbook
        $v = str_replace(["\xC2\xA0"], ' ', $v); // non-breaking space
        $v = trim(preg_replace('/\s+/', ' ', $v));
        if ($v === '') return null;
    }
    return $v;
}

function toDate($v)
{
    if ($v === null || $v === '') return null;
    try {
        if (is_numeric($v)) {
            return XlsDate::excelToDateTimeObject($v)->format('Y-m-d');
        }
        $s = trim((string) $v);
        // handle dd/mm/yyyy and dd.mm.yyyy
        if (preg_match('#^(\d{1,2})[/.\-](\d{1,2})[/.\-](\d{4})$#', $s, $m)) {
            $d = (int) $m[1]; $mo = (int) $m[2]; $y = (int) $m[3];
            if ($mo > 12 && $d <= 12) { [$d, $mo] = [$mo, $d]; } // swap if clearly wrong
            if ($d > 31) return null;
            return sprintf('%04d-%02d-%02d', $y, $mo, $d);
        }
        // ISO-like "2025-12-16" or "2025-12-16 00:00:00"
        if (preg_match('#^\d{4}-\d{2}-\d{2}#', $s)) {
            $ts = strtotime($s);
            return $ts ? date('Y-m-d', $ts) : null;
        }
        // Reject anything that is not clearly a date (e.g. "48 Hours", "PENDING").
        return null;
    } catch (\Throwable $e) {
        return null;
    }
}

function truthyYes($v)
{
    if ($v === null) return false;
    return in_array(strtolower(trim((string) $v)), ['yes', 'y', '1', 'true', 'completed', 'compelted'], true);
}

/** Split "Completed - 22-05-2026" style cells into [status text, date]. */
function splitStatusDate($v)
{
    if ($v === null) return [null, null];
    $s = trim((string) $v);
    if (strpos($s, ' - ') !== false) {
        $parts = explode(' - ', $s, 2);
        return [trim($parts[0]), toDate(trim($parts[1]))];
    }
    // date-only cell?
    $d = toDate($s);
    if ($d && preg_match('#^[0-9./\-\s]+$#', $s)) {
        return [null, $d];
    }
    return [$s, null];
}

/* ------- value normalisers matching the DB enums ---------------------- */

function normCf($v)
{
    if ($v === null) return null;
    $s = strtolower(trim((string) $v));
    if (str_contains($s, 'india')) return 'India';
    if (str_contains($s, 'canada') && str_contains($s, 'usa')) return 'Canada'; // "Canada/ USA" -> primary
    if (str_contains($s, 'canada')) return 'Canada';
    if (str_contains($s, 'usa') || str_contains($s, 'us')) return 'USA';
    return null;
}

function normPriority($v)
{
    if ($v === null) return null;
    $s = strtolower(trim((string) $v));
    return match (true) {
        str_starts_with($s, 'urgent') => 'Urgent',
        str_starts_with($s, 'intermediate') => 'Intermediate',
        str_starts_with($s, 'high') => 'High',
        str_starts_with($s, 'medium') => 'Medium',
        str_starts_with($s, 'low') => 'Low',
        default => null,
    };
}

function normJobType($v)
{
    if ($v === null) return null;
    $s = strtolower(str_replace([' ', '-'], '', (string) $v));
    return match (true) {
        str_contains($s, 'onsite') => 'onsite',
        str_contains($s, 'remote') => 'remote',
        str_contains($s, 'hybrid') => 'hybrid',
        default => null,
    };
}

function normCsi($v)
{
    if ($v === null) return null;
    $s = strtolower(trim((string) $v));
    return match (true) {
        str_contains($s, 'dice') => 'Dice',
        str_contains($s, 'link') || str_contains($s, 'lined') => 'Linkedin',
        str_contains($s, 'internal') => 'Internal',
        str_contains($s, 'external') => 'External',
        default => 'Others',
    };
}

function normWorkStatus($v)
{
    if ($v === null) return null;
    $s = strtolower(trim((string) $v));
    return match (true) {
        str_contains($s, 'citizen') => 'Citizen',
        $s === 'gc' || str_contains($s, 'green') => 'GC',
        $s === 'pr' => 'PR',
        str_contains($s, 'h1') => 'H1B',
        str_contains($s, 'opt') => 'OPT',
        default => null,
    };
}

/** enum: Completed / Pending  (+ we return date separately) */
function normCompletedPending($text)
{
    if ($text === null) return null;
    $s = strtolower($text);
    if (str_contains($s, 'complet') || str_contains($s, 'compelt')) return 'Completed';
    if (str_contains($s, 'pending')) return 'Pending';
    return null;
}

/** enum: Completed / Pending / No Show */
function normScreening($text)
{
    if ($text === null) return null;
    $s = strtolower($text);
    if (str_contains($s, 'no show')) return 'No Show';
    if (str_contains($s, 'complet') || str_contains($s, 'compelt')) return 'Completed';
    if (str_contains($s, 'pending')) return 'Pending';
    return null;
}

/** enum: Submitted / Not Submitted */
function normSubmitted($text)
{
    if ($text === null) return null;
    $s = strtolower($text);
    if (str_contains($s, 'not submit')) return 'Not Submitted';
    if (str_contains($s, 'submit')) return 'Submitted';
    return null; // "Rejected by DXC" etc -> leave enum null (kept in notes)
}

/** enum: Completed / Planned / Not Required */
function normPrep($text)
{
    if ($text === null) return null;
    $s = strtolower($text);
    if (str_contains($s, 'complet')) return 'Completed';
    if (str_contains($s, 'plan')) return 'Planned';
    if (str_contains($s, 'not req')) return 'Not Required';
    return null;
}

/** enum: Approved / Rejected */
function normReview($text)
{
    if ($text === null) return null;
    $s = strtolower($text);
    if (str_contains($s, 'approv')) return 'Approved';
    if (str_contains($s, 'reject')) return 'Rejected';
    return null;
}

/** enum: Selected / Rejected / On Hold / Selected but declined the offer */
function normDecision($text)
{
    if ($text === null) return null;
    $s = strtolower($text);
    if (str_contains($s, 'declin')) return 'Selected but declined the offer';
    if (str_contains($s, 'select')) return 'Selected';
    if (str_contains($s, 'reject')) return 'Rejected';
    if (str_contains($s, 'hold')) return 'On Hold';
    return null;
}

/** enum: Completed / Initiated / Pending */
function normBgCheck($text)
{
    if ($text === null) return null;
    $s = strtolower($text);
    if (str_contains($s, 'complet')) return 'Completed';
    if (str_contains($s, 'initiat')) return 'Initiated';
    if (str_contains($s, 'pending')) return 'Pending';
    return null;
}

/** enum: Confirmed / Not Confirmed */
function normFinal($text)
{
    if ($text === null) return null;
    $s = strtolower($text);
    if (str_contains($s, 'not confirm')) return 'Not Confirmed';
    if (str_contains($s, 'confirm')) return 'Confirmed';
    return null;
}

/** Map a free "City, State" string to a region id (best effort). */
function resolveRegion($location)
{
    if ($location === null) return [null, $location];
    $raw = trim((string) $location);
    if ($raw === '' || strtolower($raw) === 'remote' || strtolower($raw) === 'n/a') {
        return [null, $raw];
    }

    // Try "City, State" or "City State" (e.g. "Frisco TX")
    $parts = preg_split('/[,]+/', $raw);
    $first = trim($parts[0]);

    // exact city match
    $region = Region::whereRaw('LOWER(city) = ?', [strtolower($first)])->first();
    if ($region) return [$region->id, $raw];

    // "Frisco TX" -> take first word as city
    $tokens = preg_split('/\s+/', $first);
    $cityGuess = trim($tokens[0]);
    $region = Region::whereRaw('LOWER(city) = ?', [strtolower($cityGuess)])->first();
    if ($region) return [$region->id, $raw];

    // match as a state/region name
    $region = Region::whereRaw('LOWER(region) = ?', [strtolower($first)])->whereNull('city')->first();
    if ($region) return [$region->id, $raw];

    // create a loose region row so nothing is lost
    $region = Region::firstOrCreate(['region' => $raw, 'city' => null]);
    return [$region->id, $raw];
}

/* --------------------------------------------------------------------- */
/* Lead-recruiter resolution -> staff_users                              */
/* --------------------------------------------------------------------- */

$recruiterCache = [];
function resolveRecruiter($name)
{
    global $recruiterCache;
    if ($name === null) return null;
    $raw = trim((string) $name);
    if ($raw === '' || in_array(strtolower($raw), ['wip', 'unserved', 'na', 'n/a'], true)) {
        return null;
    }
    if (isset($recruiterCache[$raw])) return $recruiterCache[$raw];

    // "Priya, Devansh" -> use the first name listed
    $primary = trim(preg_split('/[,\/]+/', $raw)[0]);

    // known aliases -> existing staff usernames
    $aliases = [
        'atul' => 'Atul.Gautam',
        'atul g' => 'Atul.Gautam',
        'devansh' => 'Devansh.IT',
    ];
    $key = strtolower($primary);
    $username = $aliases[$key] ?? null;

    if ($username) {
        $user = StaffUser::where('username', $username)->first();
    }

    if (empty($user)) {
        $user = StaffUser::whereRaw('LOWER(username) = ?', [strtolower($primary)])->first()
            ?? StaffUser::whereRaw('LOWER(username) LIKE ?', [strtolower($primary) . '%'])->first();
    }

    if (! $user) {
        $user = StaffUser::create([
            'username' => $username ?? $primary,
            'email' => strtolower(str_replace(' ', '.', $username ?? $primary)) . '@rinfinite.com',
        ]);
    }

    $recruiterCache[$raw] = $user->id;
    return $user->id;
}

function isUnservedRecruiter($name): bool
{
    if ($name === null) {
        return false;
    }

    return strtolower(trim((string) $name)) === 'unserved';
}

/* --------------------------------------------------------------------- */
/* Load workbook                                                          */
/* --------------------------------------------------------------------- */

echo "Loading workbook...\n";
$spreadsheet = IOFactory::load($FILE);
$sheet = $spreadsheet->getSheetByName($SHEET);
if (!$sheet) {
    fwrite(STDERR, "Sheet '$SHEET' not found\n");
    exit(1);
}
$highestRow = $sheet->getHighestDataRow();
echo "Highest data row: $highestRow\n";

if ($fresh) {
    echo "--fresh: clearing imported tables...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    CandidatePipelineStatus::truncate();
    TrackerCandidate::truncate();
    DB::table('tracker_info_region')->truncate();
    TrackerInfo::truncate();
    Candidate::truncate();
    Client::truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
}

$stats = [
    'rows' => 0, 'trackers' => 0, 'candidates' => 0, 'links' => 0,
    'pipelines' => 0, 'skipped' => 0,
];
$warnings = [];

DB::beginTransaction();
try {
    for ($r = $DATA_START; $r <= $highestRow; $r++) {
        $position = cell($sheet, 'D', $r);
        if ($position === null) {
            $stats['skipped']++;
            continue;
        }
        $position = trim(preg_replace('/\s+/', ' ', (string) $position));
        $stats['rows']++;

        /* ---- tracker / job info ---- */
        $prd = toDate(cell($sheet, 'B', $r));
        $cf = normCf(cell($sheet, 'C', $r));
        $recruiterRaw = cell($sheet, 'E', $r);
        $recruiterId = resolveRecruiter($recruiterRaw);
        $csi = normCsi(cell($sheet, 'F', $r));

        $clientName = cell($sheet, 'G', $r);
        if ($clientName === null || strtolower($clientName) === 'n/a') {
            $clientName = 'N/A';
        }
        $client = Client::firstOrCreate(['client' => $clientName]);

        [$regionId, $regionText] = resolveRegion(cell($sheet, 'H', $r));
        $jobType = normJobType(cell($sheet, 'I', $r));
        $priority = normPriority(cell($sheet, 'J', $r));
        $deadlineRaw = cell($sheet, 'K', $r);
        $deadlineDate = \App\Services\Tracker\SubmissionDeadlineResolver::toDateString($deadlineRaw, $prd);

        // Month from PRD, else default to October 2025 (first tracked month)
        $monthLabel = $prd ? date('F Y', strtotime($prd)) : 'October 2025';
        $month = Month::firstOrCreate(['month' => $monthLabel]);
        // Same role re-opened later (e.g. Oct 2025 vs May 2026) must be separate trackers.
        // Rows in the same month with slightly different PRD days share one tracker (earliest PRD wins).
        $tracker = TrackerInfo::firstOrNew([
            'position' => $position,
            'client_id' => $client->id,
            'month_id' => $month->id,
        ]);
        $isNewTracker = !$tracker->exists;
        if ($prd && (!$tracker->prd || $prd < $tracker->prd->format('Y-m-d'))) {
            $tracker->prd = $prd;
        } elseif ($isNewTracker) {
            $tracker->prd = $prd;
            $tracker->month_id = $month->id;
        }
        if ($isNewTracker && ! isUnservedRecruiter($recruiterRaw)) {
            $tracker->job_status_FK = 1;
        }
        // Always apply latest job-level values from the workbook.
        // region_id stays as the primary (first) location; extra cities are
        // attached to the tracker_info_region pivot below.
        $tracker->region_id = $tracker->region_id ?: $regionId;
        $tracker->cf = $cf ?: $tracker->cf;
        if (isUnservedRecruiter($recruiterRaw)) {
            $tracker->job_status_FK = JobStatus::unservedId();
            $tracker->lr = null;
            $tracker->is_unserved = true;
        } else {
            $tracker->lr = $recruiterId ?: $tracker->lr;
            $tracker->is_unserved = false;
        }
        $tracker->csi = $csi ?: $tracker->csi;
        $tracker->type_of_job = $jobType ?: $tracker->type_of_job;
        $tracker->priority = $priority ?: $tracker->priority;
        $tracker->submission_deadline = $deadlineDate ?: $tracker->submission_deadline;
        $tracker->save();
        if ($isNewTracker) $stats['trackers']++;

        // Attach this row's region to the job without overwriting earlier cities.
        if ($regionId) {
            $tracker->regions()->syncWithoutDetaching([$regionId]);
        }

        /* ---- candidate ---- */
        $candName = cell($sheet, 'L', $r);
        if ($candName === null) {
            // job with no candidate yet
            continue;
        }

        $email = cell($sheet, 'M', $r);
        if ($email !== null) {
            $email = strtolower(trim(preg_replace('/\s+/', '', (string) $email)));
            if ($email === '') {
                $email = null;
            }
        }
        [$candRegionId, $candLocText] = resolveRegion(cell($sheet, 'O', $r));

        $candidatePayload = [
            'full_name' => $candName,
            'phone' => cell($sheet, 'N', $r),
            'location_id' => $candRegionId,
            'location_text' => $candLocText,
            'work_status' => normWorkStatus(cell($sheet, 'P', $r)),
            'pay_rate' => cell($sheet, 'Q', $r) !== null ? (string) cell($sheet, 'Q', $r) : null,
            'placement_pay_rate' => cell($sheet, 'AL', $r) !== null ? (string) cell($sheet, 'AL', $r) : null,
            'agency_name' => cell($sheet, 'R', $r),
            'agency_poc' => cell($sheet, 'S', $r),
            'agency_poc_phone' => cell($sheet, 'T', $r) !== null ? (string) cell($sheet, 'T', $r) : null,
            'summary' => cell($sheet, 'AW', $r),
        ];

        $candidate = null;
        if ($email) {
            $candidate = Candidate::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
        }
        if (!$candidate) {
            $candidate = Candidate::create(array_merge($candidatePayload, [
                'email' => $email ?: (strtolower(str_replace(' ', '.', $candName)) . '@noemail.local'),
            ]));
            $stats['candidates']++;
        } else {
            $candidate->update($candidatePayload);
        }

        /* ---- link ---- */
        $link = TrackerCandidate::firstOrCreate(
            ['tracker_info_id' => $tracker->id, 'candidate_id' => $candidate->id],
            ['current_status_id' => 1]
        );
        if ($link->wasRecentlyCreated) $stats['links']++;

        /* ---- pipeline ---- */
        [$revText, $revDate] = splitStatusDate(cell($sheet, 'V', $r));
        [$scrText, $scrDate] = splitStatusDate(cell($sheet, 'W', $r));
        [$subText, ] = splitStatusDate(cell($sheet, 'Y', $r));
        [$prepText, $prepDate] = splitStatusDate(cell($sheet, 'Z', $r));
        [$decText, $decDate] = splitStatusDate(cell($sheet, 'AE', $r));
        [$confText, $confDate] = splitStatusDate(cell($sheet, 'AF', $r));
        [$offText, $offDate] = splitStatusDate(cell($sheet, 'AG', $r));
        [$finText, $finDate] = splitStatusDate(cell($sheet, 'AJ', $r));

        $pipeline = [
            'candidate_identified' => truthyYes(cell($sheet, 'U', $r)),
            'resume_reviewed_by_recruiter' => normCompletedPending($revText),
            'resume_reviewed_date' => $revDate,
            'recruiter_screening_call' => normScreening($scrText),
            'recruiter_screening_call_date' => $scrDate,
            'candidate_shortlisted' => truthyYes(cell($sheet, 'X', $r)),
            'resume_submitted_to_client' => normSubmitted($subText ?? cell($sheet, 'Y', $r)),
            'radix_internal_interview_prep' => normPrep($prepText),
            'radix_internal_interview_prep_date' => $prepDate,
            'client_resume_review' => normReview(cell($sheet, 'AA', $r)),
            'client_interview_round_1_date' => toDate(cell($sheet, 'AB', $r)),
            'client_interview_round_2_date' => toDate(cell($sheet, 'AC', $r)),
            'additional_rounds' => truthyYes(cell($sheet, 'AD', $r)),
            'client_decision' => normDecision($decText ?? cell($sheet, 'AE', $r)),
            'client_decision_date' => $decDate,
            'client_confirmation_received' => truthyYes($confText ?? cell($sheet, 'AF', $r)) || $confDate !== null,
            'client_confirmation_date' => $confDate,
            'offer_extended_to_candidate' => truthyYes($offText ?? cell($sheet, 'AG', $r)) || $offDate !== null,
            'offer_extended_date' => $offDate,
            'background_check' => normBgCheck(cell($sheet, 'AH', $r)),
            'candidate_project_start_date' => toDate(cell($sheet, 'AI', $r)),
            'final_status_placement_completion' => normFinal($finText ?? cell($sheet, 'AJ', $r)),
            'placement_completion_date' => $finDate,
            'recruiter_notes' => cell($sheet, 'AU', $r),
        ];

        CandidatePipelineStatus::updateOrCreate(
            ['tracker_candidate_id' => $link->id],
            $pipeline
        );
        $stats['pipelines']++;

        /* ---- derive current status id from furthest completed stage ---- */
        $statusId = 1; // Demand Raised
        if ($pipeline['candidate_identified']) $statusId = 2;
        if ($pipeline['resume_reviewed_by_recruiter']) $statusId = 3;
        if ($pipeline['recruiter_screening_call']) $statusId = 4;
        if ($pipeline['candidate_shortlisted']) $statusId = 5;
        if ($pipeline['resume_submitted_to_client'] === 'Submitted') $statusId = 6;
        if ($pipeline['radix_internal_interview_prep']) $statusId = 7;
        if ($pipeline['client_resume_review']) $statusId = 8;
        if ($pipeline['client_interview_round_1_date']) $statusId = 9;
        if ($pipeline['client_interview_round_2_date']) $statusId = 10;
        if ($pipeline['additional_rounds']) $statusId = 11;
        if ($pipeline['client_decision']) $statusId = 12;
        if ($pipeline['client_confirmation_received']) $statusId = 13;
        if ($pipeline['offer_extended_to_candidate']) $statusId = 14;
        if ($pipeline['background_check']) $statusId = 15;
        if ($pipeline['candidate_project_start_date']) $statusId = 16;
        if ($pipeline['final_status_placement_completion'] === 'Confirmed') $statusId = 17;
        if ($pipeline['client_decision'] === 'Rejected') $statusId = 18;

        $link->update(['current_status_id' => $statusId]);
        $tracker->update(['job_status_FK' => $statusId]);
    }

    DB::commit();
} catch (\Throwable $e) {
    DB::rollBack();
    fwrite(STDERR, "FAILED at row: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
    exit(1);
}

echo "\n=== IMPORT COMPLETE ===\n";
foreach ($stats as $k => $v) {
    echo str_pad($k, 12) . ": $v\n";
}
if ($warnings) {
    echo "\nWarnings:\n";
    foreach ($warnings as $w) echo " - $w\n";
}
