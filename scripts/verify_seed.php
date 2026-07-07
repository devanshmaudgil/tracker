<?php
/**
 * Verify seeded data against the source Excel.
 * Reports aggregate counts + row-by-row candidate-level comparison.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Client;
use App\Models\Month;
use App\Models\Region;
use App\Models\StaffUser;
use App\Models\Candidate;
use App\Models\TrackerInfo;
use App\Models\TrackerCandidate;
use App\Models\CandidatePipelineStatus;
use PhpOffice\PhpSpreadsheet\IOFactory;

$FILE = __DIR__ . '/../Recruitment_Staffing_Tracker_RADiiX_INFINITEii_Year_2026_SYNCED.xlsx';
$SHEET = 'Recruitment Tracker-RADiiX_2026';

function cell($sheet, $col, $row)
{
    $v = $sheet->getCell($col . $row)->getValue();
    if (is_string($v)) {
        $v = trim(preg_replace('/\s+/', ' ', str_replace("\xC2\xA0", ' ', $v)));
        if ($v === '') return null;
    }
    return $v;
}

function toDate($v)
{
    if ($v === null || $v === '') return null;
    try {
        if (is_numeric($v)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($v)->format('Y-m-d');
        }
        $s = trim((string) $v);
        if (preg_match('#^(\d{1,2})[/.\-](\d{1,2})[/.\-](\d{4})$#', $s, $m)) {
            $d = (int) $m[1]; $mo = (int) $m[2]; $y = (int) $m[3];
            if ($mo > 12 && $d <= 12) { [$d, $mo] = [$mo, $d]; }
            if ($d > 31) return null;
            return sprintf('%04d-%02d-%02d', $y, $mo, $d);
        }
        if (preg_match('#^\d{4}-\d{2}-\d{2}#', $s)) {
            $ts = strtotime($s);
            return $ts ? date('Y-m-d', $ts) : null;
        }
        return null;
    } catch (\Throwable $e) {
        return null;
    }
}

function monthLabelFromPrd(?string $prd): string
{
    return $prd ? date('F Y', strtotime($prd)) : 'October 2025';
}

$spreadsheet = IOFactory::load($FILE);
$sheet = $spreadsheet->getSheetByName($SHEET);

/* ---- Excel-side tallies ---- */
$xlPositions = 0; $xlCandidates = 0; $xlEmails = [];
$xlClients = []; $xlIndia = 0;
for ($r = 5; $r <= 103; $r++) {
    $pos = cell($sheet, 'D', $r);
    if ($pos === null) continue;
    $xlPositions++;
    $c = cell($sheet, 'G', $r);
    if ($c) $xlClients[strtolower($c) === 'n/a' ? 'N/A' : $c] = true;
    if (($cf = cell($sheet, 'C', $r)) && stripos($cf, 'india') !== false) $xlIndia++;
    $name = cell($sheet, 'L', $r);
    if ($name) {
        $xlCandidates++;
        $em = cell($sheet, 'M', $r);
        if ($em) $xlEmails[strtolower($em)] = $name;
    }
}

echo "================ AGGREGATE COUNTS ================\n";
printf("%-28s %8s %8s\n", '', 'EXCEL', 'DB');
printf("%-28s %8d %8d\n", 'Positions (rows w/ role)', $xlPositions, '-');
printf("%-28s %8d %8d\n", 'Unique clients', count($xlClients), Client::count());
printf("%-28s %8d %8d\n", 'Candidate rows', $xlCandidates, '-');
printf("%-28s %8d %8d\n", 'Unique candidate emails', count($xlEmails), Candidate::count());
printf("%-28s %8s %8d\n", 'Tracker records', '-', TrackerInfo::count());
printf("%-28s %8s %8d\n", 'Tracker-candidate links', '-', TrackerCandidate::count());
printf("%-28s %8s %8d\n", 'Pipeline records', '-', CandidatePipelineStatus::count());
printf("%-28s %8d %8d\n", 'India trackers', $xlIndia, TrackerInfo::where('cf', 'India')->count());

echo "\ncf distribution (DB): ";
foreach (TrackerInfo::selectRaw('cf, count(*) c')->groupBy('cf')->pluck('c', 'cf') as $k => $v) {
    echo ($k ?? 'NULL') . "=$v  ";
}
echo "\npriority distribution (DB): ";
foreach (TrackerInfo::selectRaw('priority, count(*) c')->groupBy('priority')->pluck('c', 'priority') as $k => $v) {
    echo ($k ?? 'NULL') . "=$v  ";
}

/* ---- placement / confirmed sanity ---- */
echo "\n\n================ PIPELINE STAGE TALLIES ================\n";
$p = new CandidatePipelineStatus;
printf("candidate_identified = true : %d\n", CandidatePipelineStatus::where('candidate_identified', true)->count());
printf("shortlisted = true          : %d\n", CandidatePipelineStatus::where('candidate_shortlisted', true)->count());
printf("submitted to client         : %d\n", CandidatePipelineStatus::where('resume_submitted_to_client', 'Submitted')->count());
printf("client_decision Selected    : %d\n", CandidatePipelineStatus::where('client_decision', 'Selected')->count());
printf("client_decision Rejected    : %d\n", CandidatePipelineStatus::where('client_decision', 'Rejected')->count());
printf("declined offer              : %d\n", CandidatePipelineStatus::where('client_decision', 'Selected but declined the offer')->count());
printf("final Confirmed             : %d\n", CandidatePipelineStatus::where('final_status_placement_completion', 'Confirmed')->count());
printf("recruiter_notes filled      : %d\n", CandidatePipelineStatus::whereNotNull('recruiter_notes')->count());

/* ---- missing emails check ---- */
echo "\n================ EMAIL RECONCILIATION ================\n";
$missing = [];
foreach ($xlEmails as $em => $name) {
    if (!Candidate::whereRaw('LOWER(email) = ?', [$em])->exists()) {
        $missing[] = "$name <$em>";
    }
}
echo "Excel emails NOT found in DB: " . count($missing) . "\n";
foreach (array_slice($missing, 0, 20) as $m) echo "  MISSING: $m\n";

/* ---- detailed spot-check of the 5 placed candidates ---- */
echo "\n================ DETAILED CHECK: CONFIRMED PLACEMENTS ================\n";
$rowsConfirmed = [];
for ($r = 5; $r <= 103; $r++) {
    if (($f = cell($sheet, 'AJ', $r)) && stripos((string) $f, 'confirm') !== false) {
        $rowsConfirmed[] = $r;
    }
}
foreach ($rowsConfirmed as $r) {
    $name = cell($sheet, 'L', $r);
    $em = cell($sheet, 'M', $r);
    echo "\nExcel row $r: $name <$em>\n";
    $cand = $em ? Candidate::whereRaw('LOWER(email)=?', [strtolower($em)])->first() : Candidate::where('full_name', $name)->first();
    if (!$cand) { echo "  !! candidate not found in DB\n"; continue; }
    $link = TrackerCandidate::where('candidate_id', $cand->id)->first();
    $ps = $link ? CandidatePipelineStatus::where('tracker_candidate_id', $link->id)->first() : null;
    echo "  DB name        : {$cand->full_name}\n";
    echo "  DB pay_rate    : " . ($cand->pay_rate ?? '-') . "  | placement: " . ($cand->placement_pay_rate ?? '-') . "\n";
    echo "  Excel start    : " . cell($sheet, 'AI', $r) . " | DB: " . ($ps?->candidate_project_start_date) . "\n";
    echo "  Excel final    : " . cell($sheet, 'AJ', $r) . " | DB: " . ($ps?->final_status_placement_completion) . "\n";
    echo "  Excel decision : " . cell($sheet, 'AE', $r) . " | DB: " . ($ps?->client_decision) . "\n";
}

/* ---- three full-row spot checks (rows 5,6,7) ---- */
echo "\n================ FULL ROW SPOT CHECK (rows 5-7) ================\n";
foreach ([5, 6, 7] as $r) {
    echo "\n--- Excel row $r ---\n";
    echo "  Position : " . cell($sheet, 'D', $r) . "\n";
    echo "  Client   : " . cell($sheet, 'G', $r) . "\n";
    echo "  cf       : " . cell($sheet, 'C', $r) . "\n";
    echo "  Recruiter: " . cell($sheet, 'E', $r) . "\n";
    echo "  Candidate: " . cell($sheet, 'L', $r) . " <" . cell($sheet, 'M', $r) . ">\n";

    $client = Client::where('client', cell($sheet, 'G', $r) ?: 'N/A')->first();
    $tr = TrackerInfo::where('position', cell($sheet, 'D', $r))->when($client, fn($q) => $q->where('client_id', $client->id))->first();
    if ($tr) {
        echo "  -> DB tracker id={$tr->id} client=" . ($tr->client->client ?? '-')
            . " cf=" . ($tr->cf ?? '-')
            . " region=" . ($tr->region?->city ?? $tr->region?->region ?? '-')
            . " recruiter=" . ($tr->leadRecruiter?->username ?? '-')
            . " status={$tr->job_status_FK}\n";
    } else {
        echo "  -> DB tracker NOT FOUND\n";
    }
}

/* ---- row-by-row candidate ↔ tracker month/PRD alignment ---- */
echo "\n================ CANDIDATE ↔ TRACKER ALIGNMENT (every Excel row) ================\n";
$mismatches = [];
$checked = 0;
$highestRow = $sheet->getHighestDataRow();
for ($r = 5; $r <= $highestRow; $r++) {
    $pos = cell($sheet, 'D', $r);
    if ($pos === null) continue;
    $candName = cell($sheet, 'L', $r);
    if ($candName === null) continue;

    $prd = toDate(cell($sheet, 'B', $r));
    $expectedMonth = monthLabelFromPrd($prd);
    $clientName = cell($sheet, 'G', $r);
    if ($clientName === null || strtolower($clientName) === 'n/a') {
        $clientName = 'N/A';
    }
    $email = cell($sheet, 'M', $r);

    $cand = $email
        ? Candidate::whereRaw('LOWER(email) = ?', [strtolower($email)])->first()
        : Candidate::where('full_name', $candName)->first();
    if (!$cand) {
        $mismatches[] = "Row $r: candidate not in DB — $candName";
        continue;
    }

    $client = Client::where('client', $clientName)->first();
    $expectedMonthId = Month::where('month', $expectedMonth)->value('id');
    $tracker = TrackerInfo::where('position', trim(preg_replace('/\s+/', ' ', (string) $pos)))
        ->when($client, fn ($q) => $q->where('client_id', $client->id))
        ->when($expectedMonthId, fn ($q) => $q->where('month_id', $expectedMonthId))
        ->first();

    $link = TrackerCandidate::where('candidate_id', $cand->id)
        ->where('tracker_info_id', $tracker->id)
        ->first();
    $checked++;

    if (!$tracker) {
        $mismatches[] = "Row $r: $candName — expected tracker not found (pos+client+month=$expectedMonth)";
        continue;
    }
    if (!$link) {
        $other = TrackerCandidate::where('candidate_id', $cand->id)->with('trackerInfo.month')->get();
        $otherDesc = $other->map(fn ($tc) => '#'.$tc->tracker_info_id.' ('.($tc->trackerInfo?->month?->month ?? '?').')')->implode(', ');
        $mismatches[] = "Row $r: $candName — not linked to expected tracker #{$tracker->id} ($expectedMonth). Has: $otherDesc";
        continue;
    }
}

echo "Checked $checked candidate rows.\n";
echo "Mismatches: " . count($mismatches) . "\n";
foreach ($mismatches as $m) {
    echo "  !! $m\n";
}
if (count($mismatches) === 0) {
    echo "All candidate rows match Excel position + client + PRD month.\n";
}
