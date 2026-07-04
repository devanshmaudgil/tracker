<?php

namespace App\Http\Controllers;

use App\Models\TrackerInfo;
use App\Models\Month;
use App\Models\StaffUser;
use App\Models\Client;
use App\Models\Region;
use App\Models\TrackerCandidate;
use App\Models\CandidatePipelineStatus;
use App\Models\Candidate;
use App\Models\JobStatus;
use App\Services\Tracker\CandidatePipelineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use App\Exports\TrackerExport;
use App\Imports\TrackerImport;
use Carbon\Carbon;

class TrackerController extends Controller
{
    public function __construct(
        private CandidatePipelineService $pipelineService,
    ) {
    }

    private function applyTrackerFilters($query, Request $request, ?int $selectedMonthId, bool $includeSearch = true): void
    {
        if ($includeSearch && $request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('position', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q2) use ($search) {
                        $q2->where('client', 'like', "%{$search}%");
                    });
            });
        }

        if ($selectedMonthId) {
            $query->where('month_id', $selectedMonthId);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('lead_recruiter_id')) {
            $query->where('lr', $request->lead_recruiter_id);
        }
    }

    private function attentionSummary(Request $request, ?int $selectedMonthId): array
    {
        $query = TrackerInfo::query()->whereNotIn('job_status_FK', [17, 18]);
        $this->applyTrackerFilters($query, $request, $selectedMonthId);

        $today = Carbon::today();
        $weekEnd = Carbon::today()->endOfWeek();

        return [
            'overdue' => (clone $query)
                ->whereNotNull('submission_deadline')
                ->whereDate('submission_deadline', '<', $today)
                ->count(),
            'due_soon' => (clone $query)
                ->whereNotNull('submission_deadline')
                ->whereBetween('submission_deadline', [$today, $weekEnd])
                ->count(),
            'urgent' => (clone $query)
                ->where('priority', 'Urgent')
                ->count(),
        ];
    }

    public function index(Request $request)
    {
        if (! $request->ajax() && ! $request->filled('month_id')) {
            $defaultMonthId = Month::latestMonth()?->id;

            if ($defaultMonthId) {
                return redirect()->route('tracker.index', array_merge(
                    $request->query(),
                    ['month_id' => $defaultMonthId]
                ));
            }
        }

        $months = Month::orderedLatestFirst();
        $selectedMonthId = Month::resolveSelectedId($request);

        $query = TrackerInfo::with(['month', 'client', 'region', 'leadRecruiter', 'jobStatus']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('type_of_job', 'like', "%{$search}%")
                  ->orWhere('bill_rate_salary_range', 'like', "%{$search}%")
                  ->orWhere('priority', 'like', "%{$search}%")
                  ->orWhere('cf', 'like', "%{$search}%")
                  ->orWhere('csi', 'like', "%{$search}%")
                  ->orWhereHas('month', function($q) use ($search) {
                      $q->where('month', 'like', "%{$search}%");
                  })
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('client', 'like', "%{$search}%");
                  })
                  ->orWhereHas('region', function($q) use ($search) {
                      $q->where('region', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                  })
                  ->orWhereHas('leadRecruiter', function($q) use ($search) {
                      $q->where('username', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($selectedMonthId) {
            $query->where('month_id', $selectedMonthId);
        }
        
        // Filter by client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        
        // Filter by lead recruiter
        if ($request->filled('lead_recruiter_id')) {
            $query->where('lr', $request->lead_recruiter_id);
        }

        // Tab filtering
        $tab = $request->get('tab', 'demand_raised');
        if ($tab) {
            switch ($tab) {
                case 'demand_raised':
                    $query->where('job_status_FK', 1);
                    break;
                case 'identified':
                    $query->where('job_status_FK', 2);
                    break;
                case 'screening':
                    $query->whereIn('job_status_FK', [3, 4, 5]);
                    break;
                case 'submission':
                    $query->whereIn('job_status_FK', [6, 7, 8]);
                    break;
                case 'interview':
                    $query->whereIn('job_status_FK', [9, 10, 11]);
                    break;
                case 'decision':
                    $query->where('job_status_FK', 12);
                    break;
                case 'accepted':
                    $query->where('job_status_FK', 17);
                    break;
                case 'rejected':
                    $query->where('job_status_FK', 18);
                    break;
            }
        }
        
        $trackerInfos = $query->orderBy('id', 'desc')->paginate(20);

        // Calculate counts for tabs based on current filters (excluding tab itself)
        $baseCountQuery = TrackerInfo::query();
        $this->applyTrackerFilters($baseCountQuery, $request, $selectedMonthId);

        $counts = [
            'demand_raised' => (clone $baseCountQuery)->where('job_status_FK', 1)->count(),
            'identified' => (clone $baseCountQuery)->where('job_status_FK', 2)->count(),
            'screening' => (clone $baseCountQuery)->whereIn('job_status_FK', [3, 4, 5])->count(),
            'submission' => (clone $baseCountQuery)->whereIn('job_status_FK', [6, 7, 8])->count(),
            'interview' => (clone $baseCountQuery)->whereIn('job_status_FK', [9, 10, 11])->count(),
            'decision' => (clone $baseCountQuery)->where('job_status_FK', 12)->count(),
            'accepted' => (clone $baseCountQuery)->where('job_status_FK', 17)->count(),
            'rejected' => (clone $baseCountQuery)->where('job_status_FK', 18)->count(),
        ];
        
        $totalRequisition = $selectedMonthId
            ? TrackerInfo::where('month_id', $selectedMonthId)->count()
            : 0;

        $attentionSummary = $this->attentionSummary($request, $selectedMonthId);

        if ($request->ajax()) {
            return response()->json([
                'table' => view('tracker._table', compact('trackerInfos'))->render(),
                'pagination' => $trackerInfos->appends(request()->query())->links('vendor.pagination.custom')->render(),
                'count_text' => $trackerInfos->total() > 0
                    ? "Showing {$trackerInfos->firstItem()} to {$trackerInfos->lastItem()} of {$trackerInfos->total()} entries"
                    : 'No entries found',
                'counts' => $counts,
                'total_requisition' => $totalRequisition,
                'attention_strip' => view('tracker._attention_strip', compact('attentionSummary'))->render(),
            ]);
        }
        
        $clients = Client::orderBy('client', 'asc')->get();
        $regions = Region::orderBy('region', 'asc')->get();
        $leadRecruiters = StaffUser::orderBy('id', 'desc')->get();
        $selectedMonth = $months->firstWhere('id', $selectedMonthId);
        
        return view('tracker.index', compact(
            'trackerInfos',
            'months',
            'clients',
            'regions',
            'leadRecruiters',
            'counts',
            'selectedMonthId',
            'selectedMonth',
            'totalRequisition',
            'attentionSummary'
        ));
    }

    public function create()
    {
        $months = Month::orderBy('id', 'desc')->get();
        $clients = Client::orderBy('client', 'asc')->get();
        $regions = Region::orderBy('region', 'asc')->get();
        $leadRecruiters = StaffUser::orderBy('id', 'desc')->get();
        
        return view('tracker.create', compact('months', 'clients', 'regions', 'leadRecruiters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'month_id' => 'required|exists:months,id',
            'client_id' => 'nullable|exists:clients,id',
            'region_id' => 'nullable|exists:regions,id',
            'prd' => 'nullable|date',
            'cf' => 'nullable|in:Canada,USA',
            'country' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'job_description' => 'nullable|string',
            'type_of_job' => 'nullable|in:onsite,remote,hybrid',
            'bill_rate_salary_range' => 'nullable|string|max:255',
            'priority' => 'nullable|in:Urgent,Low,High,Medium',
            'submission_deadline' => 'nullable|date',
            'lr' => 'nullable|exists:staff_users,id',
            'csi' => 'nullable|in:Internal,External,Dice,Linkedin,Others',
        ]);

        $data = $request->all();
        $data['job_status_FK'] = 1; // Default to Demand Raised
        TrackerInfo::create($data);

        return redirect()->route('tracker.index')->with('success', 'Tracker info added successfully.');
    }

    public function show(string $id)
    {
        $trackerInfo = TrackerInfo::with(['month', 'client', 'region', 'leadRecruiter'])->findOrFail($id);
        return response()->json([
            'id' => $trackerInfo->id,
            'month_id' => $trackerInfo->month_id,
            'month' => $trackerInfo->month,
            'client_id' => $trackerInfo->client_id,
            'client' => $trackerInfo->client,
            'region_id' => $trackerInfo->region_id,
            'region' => $trackerInfo->region,
            'prd' => $trackerInfo->prd ? $trackerInfo->prd->format('Y-m-d') : null,
            'cf' => $trackerInfo->cf,
            'country' => $trackerInfo->country,
            'position' => $trackerInfo->position,
            'type_of_job' => $trackerInfo->type_of_job,
            'bill_rate_salary_range' => $trackerInfo->bill_rate_salary_range,
            'priority' => $trackerInfo->priority,
            'submission_deadline' => $trackerInfo->submission_deadline ? $trackerInfo->submission_deadline->format('Y-m-d') : null,
            'lr' => $trackerInfo->lr,
            'lead_recruiter' => $trackerInfo->leadRecruiter,
            'csi' => $trackerInfo->csi,
        ]);
    }

    public function info(string $id)
    {
        $trackerInfo = TrackerInfo::with(['month', 'client', 'region', 'leadRecruiter', 'jobStatus'])
            ->findOrFail($id);

        $notPlaced = fn ($q) => $q->where(function ($inner) {
            $inner->whereDoesntHave('pipelineStatus')
                ->orWhereHas('pipelineStatus', function ($pq) {
                    $pq->where(function ($p) {
                        $p->whereNull('final_status_placement_completion')
                            ->orWhere('final_status_placement_completion', '!=', 'Confirmed');
                    });
                });
        });

        $activeCandidates = $trackerInfo->trackerCandidates()
            ->with(['candidate.location', 'pipelineStatus', 'status'])
            ->whereNull('rejected_at')
            ->whereNull('approved_at')
            ->where($notPlaced)
            ->get();

        $approvedCandidates = $trackerInfo->trackerCandidates()
            ->with(['candidate.location', 'pipelineStatus', 'status'])
            ->whereNull('rejected_at')
            ->whereNotNull('approved_at')
            ->where($notPlaced)
            ->orderByDesc('approved_at')
            ->get();

        $placedCandidates = $trackerInfo->trackerCandidates()
            ->with(['candidate.location', 'pipelineStatus', 'status'])
            ->whereNull('rejected_at')
            ->whereHas('pipelineStatus', fn ($q) => $q->where('final_status_placement_completion', 'Confirmed'))
            ->get()
            ->sortByDesc(fn ($tc) => $tc->pipelineStatus?->placement_completion_date)
            ->values();

        $rejectedCandidates = $trackerInfo->trackerCandidates()
            ->with(['candidate.location', 'pipelineStatus', 'status'])
            ->whereNotNull('rejected_at')
            ->orderByDesc('rejected_at')
            ->get();

        $pipelineCount = $activeCandidates->count() + $approvedCandidates->count();
        $placementConfirmedLabel = 'Candidate Placement Confirmed';

        $assignedCandidateIds = $trackerInfo->trackerCandidates()->pluck('candidate_id');

        $availableCandidates = Candidate::with('location')->orderBy('full_name', 'asc')->get();
        $jobStatuses = JobStatus::all();
        $pipelineService = app(CandidatePipelineService::class);
        $approvedStageLabels = TrackerCandidate::approvedStageLabels();

        $staffEmails = StaffUser::whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('username')
            ->get(['id', 'username', 'email']);

        $recruiterEmail = Auth::user()?->staffUser?->email;
        $recruiterName = Auth::user()?->staffUser?->username ?? Auth::user()?->username ?? 'Recruiter';

        $trackerLocation = $trackerInfo->region
            ? trim(($trackerInfo->region->city ? $trackerInfo->region->city . ', ' : '') . $trackerInfo->region->region . ($trackerInfo->cf ? ', ' . $trackerInfo->cf : ''))
            : ($trackerInfo->cf ?? '');

        if ($trackerInfo->type_of_job) {
            $jobTypeLabel = ucfirst($trackerInfo->type_of_job);
            $trackerLocation .= $trackerLocation ? " ({$jobTypeLabel})" : $jobTypeLabel;
        }

        return view('tracker.info', compact(
            'trackerInfo',
            'activeCandidates',
            'approvedCandidates',
            'placedCandidates',
            'rejectedCandidates',
            'pipelineCount',
            'placementConfirmedLabel',
            'assignedCandidateIds',
            'availableCandidates',
            'jobStatuses',
            'pipelineService',
            'approvedStageLabels',
            'staffEmails',
            'recruiterEmail',
            'recruiterName',
            'trackerLocation',
        ));
    }

    public function edit(string $id)
    {
        $trackerInfo = TrackerInfo::findOrFail($id);
        $months = Month::orderBy('id', 'desc')->get();
        $clients = Client::orderBy('client', 'asc')->get();
        $regions = Region::orderBy('region', 'asc')->get();
        $leadRecruiters = StaffUser::orderBy('id', 'desc')->get();
        
        return view('tracker.edit', compact('trackerInfo', 'months', 'clients', 'regions', 'leadRecruiters'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'month_id' => 'required|exists:months,id',
            'client_id' => 'nullable|exists:clients,id',
            'region_id' => 'nullable|exists:regions,id',
            'prd' => 'nullable|date',
            'cf' => 'nullable|in:Canada,USA',
            'country' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'job_description' => 'nullable|string',
            'type_of_job' => 'nullable|in:onsite,remote,hybrid',
            'bill_rate_salary_range' => 'nullable|string|max:255',
            'priority' => 'nullable|in:Urgent,Low,High,Medium',
            'submission_deadline' => 'nullable|date',
            'lr' => 'nullable|exists:staff_users,id',
            'csi' => 'nullable|in:Internal,External,Dice,Linkedin,Others',
        ]);

        $trackerInfo = TrackerInfo::findOrFail($id);
        $trackerInfo->update($request->all());

        return redirect()->route('tracker.index')->with('success', 'Tracker info updated successfully.');
    }

    public function destroy(string $id)
    {
        $trackerInfo = TrackerInfo::findOrFail($id);
        $trackerInfo->delete();

        return redirect()->route('tracker.index')->with('success', 'Tracker info deleted successfully.');
    }

    public function export(string $id)
    {
        $exporter = new TrackerExport($id);
        return $exporter->export();
    }

    public function exportAll(Request $request)
    {
        $query = TrackerInfo::query();
        
        // Apply exactly the same filters as in index()
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('type_of_job', 'like', "%{$search}%")
                  ->orWhere('bill_rate_salary_range', 'like', "%{$search}%")
                  ->orWhere('priority', 'like', "%{$search}%")
                  ->orWhere('cf', 'like', "%{$search}%")
                  ->orWhere('csi', 'like', "%{$search}%")
                  ->orWhereHas('month', function($q) use ($search) {
                      $q->where('month', 'like', "%{$search}%");
                  })
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('client', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->filled('month_id')) {
            $query->where('month_id', $request->month_id);
        }
        
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        
        if ($request->filled('lead_recruiter_id')) {
            $query->where('lr', $request->lead_recruiter_id);
        }

        $tab = $request->get('tab');
        if ($tab) {
            switch ($tab) {
                case 'demand_raised': $query->where('job_status_FK', 1); break;
                case 'identified': $query->where('job_status_FK', 2); break;
                case 'screening': $query->whereIn('job_status_FK', [3, 4, 5]); break;
                case 'submission': $query->whereIn('job_status_FK', [6, 7, 8]); break;
                case 'interview': $query->whereIn('job_status_FK', [9, 10, 11]); break;
                case 'decision': $query->where('job_status_FK', 12); break;
                case 'accepted': $query->where('job_status_FK', 17); break;
                case 'rejected': $query->where('job_status_FK', 18); break;
            }
        }

        $trackerIds = $query->pluck('id')->toArray();
        
        if (empty($trackerIds)) {
            return back()->with('error', 'No data found to export.');
        }

        $exporter = new TrackerExport($trackerIds);
        return $exporter->export();
    }

    public function showImportForm()
    {
        return view('tracker.import');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('excel_file');
            $filePath = $file->getRealPath();

            $importer = new TrackerImport();
            $result = $importer->import($filePath);

            if ($result['success']) {
                $message = "Import completed! Imported: {$result['imported']}, Skipped: {$result['skipped']}";
                
                if (!empty($result['errors'])) {
                    $message .= " | Errors: " . count($result['errors']);
                    session()->flash('import_errors', $result['errors']);
                }

                return redirect()->route('tracker.index')->with('success', $message);
            } else {
                return back()->with('error', 'Import failed: ' . $result['message']);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function assignCandidate(Request $request, string $id)
    {
        $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
        ]);

        $trackerInfo = TrackerInfo::findOrFail($id);
        
        // Check if already assigned
        $existing = TrackerCandidate::where('tracker_info_id', $id)
            ->where('candidate_id', $request->candidate_id)
            ->first();
        
        if ($existing) {
            return redirect()->route('tracker.info', $id)->with('error', 'Candidate already assigned to this job.');
        }

        $trackerCandidate = TrackerCandidate::create([
            'tracker_info_id' => $id,
            'candidate_id' => $request->candidate_id,
            'current_status_id' => 2, // Candidate Identified
        ]);

        // Create initial pipeline status record
        CandidatePipelineStatus::create([
            'tracker_candidate_id' => $trackerCandidate->id,
            'candidate_identified' => true,
        ]);

        // Update overall job status based on majority
        $trackerInfo->updateStatusFromCandidates();

        return redirect()->route('tracker.info', $id)->with('success', 'Candidate assigned successfully.');
    }

    public function unassignCandidate(Request $request, string $trackerId, string $trackerCandidateId)
    {
        $trackerCandidate = TrackerCandidate::findOrFail($trackerCandidateId);
        
        // Delete pipeline status if exists
        if ($trackerCandidate->pipelineStatus) {
            $trackerCandidate->pipelineStatus->delete();
        }
        
        // Delete the assignment
        $trackerCandidate->delete();

        // Update overall job status based on majority
        $trackerInfo = TrackerInfo::find($trackerId);
        if ($trackerInfo) {
            $trackerInfo->updateStatusFromCandidates();
        }

        return redirect()->route('tracker.info', $trackerId)->with('success', 'Candidate removed successfully.');
    }

    public function getPipelineStatus(string $trackerId, string $trackerCandidateId)
    {
        $trackerCandidate = TrackerCandidate::with(['pipelineStatus', 'status', 'candidate'])->findOrFail($trackerCandidateId);
        $hasResume = (bool) $trackerCandidate->candidate?->resume_file_url;

        if (!$trackerCandidate->pipelineStatus) {
            return response()->json([
                'candidate_identified' => false,
                'requirement_reviewed' => false,
                'doc_resume_collected' => false,
                'doc_govt_id_collected' => false,
                'doc_work_auth_collected' => false,
                'doc_linkedin_collected' => false,
                'rtr_signed' => false,
                'has_resume' => $hasResume,
                'checklist_items' => [],
                'checklist_progress' => 0,
                'resume_reviewed_by_recruiter' => null,
                'resume_reviewed_date' => null,
                'recruiter_screening_call' => null,
                'recruiter_screening_call_date' => null,
                'candidate_shortlisted' => false,
                'resume_submitted_to_client' => null,
                'radix_internal_interview_prep' => null,
                'radix_internal_interview_prep_date' => null,
                'client_resume_review' => null,
                'client_interview_round_1_date' => null,
                'client_interview_round_2_date' => null,
                'additional_rounds' => false,
                'client_decision' => null,
                'client_decision_date' => null,
                'client_confirmation_received' => false,
                'client_confirmation_date' => null,
                'offer_extended_to_candidate' => false,
                'offer_extended_date' => null,
                'background_check' => null,
                'candidate_project_start_date' => null,
                'final_status_placement_completion' => null,
                'placement_completion_date' => null,
                'current_status_id' => $trackerCandidate->current_status_id,
                'current_status_label' => $trackerCandidate->status?->status ?? 'Unknown',
            ]);
        }

        $status = $trackerCandidate->pipelineStatus;
        $items = $this->pipelineService->checklistItems($status, $hasResume);
        $placementLabel = $status->final_status_placement_completion === 'Confirmed'
            ? 'Candidate Placement Confirmed'
            : ($trackerCandidate->status?->status ?? 'Unknown');

        return response()->json([
            'candidate_identified' => $status->candidate_identified,
            'requirement_reviewed' => $status->requirement_reviewed,
            'doc_resume_collected' => (bool) $status->doc_resume_collected,
            'doc_govt_id_collected' => $status->doc_govt_id_collected,
            'doc_work_auth_collected' => $status->doc_work_auth_collected,
            'doc_linkedin_collected' => $status->doc_linkedin_collected,
            'rtr_signed' => $status->rtr_signed,
            'has_resume' => $hasResume,
            'checklist_items' => $items,
            'checklist_progress' => $this->pipelineService->checklistProgress($items),
            'resume_reviewed_by_recruiter' => $status->resume_reviewed_by_recruiter,
            'resume_reviewed_date' => $status->resume_reviewed_date ? $status->resume_reviewed_date->format('Y-m-d') : null,
            'recruiter_screening_call' => $status->recruiter_screening_call,
            'recruiter_screening_call_date' => $status->recruiter_screening_call_date ? $status->recruiter_screening_call_date->format('Y-m-d') : null,
            'candidate_shortlisted' => $status->candidate_shortlisted,
            'resume_submitted_to_client' => $status->resume_submitted_to_client,
            'radix_internal_interview_prep' => $status->radix_internal_interview_prep,
            'radix_internal_interview_prep_date' => $status->radix_internal_interview_prep_date ? $status->radix_internal_interview_prep_date->format('Y-m-d') : null,
            'client_resume_review' => $status->client_resume_review,
            'client_interview_round_1_date' => $status->client_interview_round_1_date ? $status->client_interview_round_1_date->format('Y-m-d') : null,
            'client_interview_round_2_date' => $status->client_interview_round_2_date ? $status->client_interview_round_2_date->format('Y-m-d') : null,
            'additional_rounds' => $status->additional_rounds,
            'client_decision' => $status->client_decision,
            'client_decision_date' => $status->client_decision_date ? $status->client_decision_date->format('Y-m-d') : null,
            'client_confirmation_received' => $status->client_confirmation_received,
            'client_confirmation_date' => $status->client_confirmation_date ? $status->client_confirmation_date->format('Y-m-d') : null,
            'offer_extended_to_candidate' => $status->offer_extended_to_candidate,
            'offer_extended_date' => $status->offer_extended_date ? $status->offer_extended_date->format('Y-m-d') : null,
            'background_check' => $status->background_check,
            'candidate_project_start_date' => $status->candidate_project_start_date ? $status->candidate_project_start_date->format('Y-m-d') : null,
            'final_status_placement_completion' => $status->final_status_placement_completion,
            'placement_completion_date' => $status->placement_completion_date ? $status->placement_completion_date->format('Y-m-d') : null,
            'current_status_id' => $trackerCandidate->current_status_id,
            'current_status_label' => $placementLabel,
        ]);
    }

    public function updatePipelineStatus(Request $request, string $trackerId, string $trackerCandidateId)
    {
        $request->validate([
            'requirement_reviewed' => 'nullable|boolean',
            'doc_govt_id_collected' => 'nullable|boolean',
            'doc_work_auth_collected' => 'nullable|boolean',
            'doc_linkedin_collected' => 'nullable|boolean',
            'rtr_signed' => 'nullable|boolean',
            'candidate_identified' => 'nullable|boolean',
            'resume_reviewed_by_recruiter' => 'nullable|in:Completed,Pending',
            'resume_reviewed_date' => 'nullable|date',
            'recruiter_screening_call' => 'nullable|in:Completed,Pending,No Show',
            'recruiter_screening_call_date' => 'nullable|date',
            'candidate_shortlisted' => 'nullable|boolean',
            'resume_submitted_to_client' => 'nullable|in:Submitted,Not Submitted',
            'radix_internal_interview_prep' => 'nullable|in:Completed,Planned,Not Required',
            'radix_internal_interview_prep_date' => 'nullable|date',
            'client_resume_review' => 'nullable|in:Approved,Rejected',
            'client_interview_round_1_date' => 'nullable|date',
            'client_interview_round_2_date' => 'nullable|date',
            'additional_rounds' => 'nullable|boolean',
            'client_decision' => 'nullable|in:Selected,Rejected,On Hold',
            'client_decision_date' => 'nullable|date',
            'client_confirmation_received' => 'nullable|boolean',
            'client_confirmation_date' => 'nullable|date',
            'offer_extended_to_candidate' => 'nullable|boolean',
            'offer_extended_date' => 'nullable|date',
            'background_check' => 'nullable|in:Completed,Initiated,Pending',
            'candidate_project_start_date' => 'nullable|date',
            'final_status_placement_completion' => 'nullable|in:Confirmed,Not Confirmed',
            'placement_completion_date' => 'nullable|date',
        ]);

        $trackerCandidate = TrackerCandidate::with(['pipelineStatus', 'candidate'])->findOrFail($trackerCandidateId);

        if ($request->client_decision === 'Rejected') {
            return $this->markCandidateRejected($trackerId, $trackerCandidate, 'Client decision: Rejected');
        }

        if ($request->client_resume_review === 'Rejected') {
            return $this->markCandidateRejected($trackerId, $trackerCandidate, 'Client resume review: Rejected');
        }

        $data = $this->buildPipelineDataFromRequest($request, $trackerCandidate);

        $pipeline = $this->upsertPipelineStatus($trackerCandidate, $data);
        $hasResume = (bool) $trackerCandidate->candidate?->resume_file_url;
        $newStatusId = $this->resolveTrackerStatusId($trackerCandidate, $pipeline, $hasResume);

        $trackerCandidate->update(['current_status_id' => $newStatusId]);

        $trackerInfo = TrackerInfo::find($trackerId);
        if ($trackerInfo) {
            $trackerInfo->updateStatusFromCandidates();
        }

        return redirect()->route('tracker.info', $trackerId)->with('success', 'Pipeline status updated successfully.');
    }

    public function updateChecklist(Request $request, string $trackerId, string $trackerCandidateId)
    {
        $request->validate([
            'field' => 'required|string|max:64',
            'checked' => 'sometimes|boolean',
            'details' => 'sometimes|array',
            'details.resume_reviewed_by_recruiter' => 'nullable|in:Completed,Pending',
            'details.resume_reviewed_date' => 'nullable|date',
            'details.recruiter_screening_call' => 'nullable|in:Completed,Pending,No Show',
            'details.recruiter_screening_call_date' => 'nullable|date',
            'details.resume_submitted_to_client' => 'nullable|in:Submitted,Not Submitted',
        ]);

        $field = $request->input('field');
        if (!$this->pipelineService->isChecklistField($field)) {
            return response()->json(['message' => 'Invalid checklist field.'], 422);
        }

        $trackerCandidate = TrackerCandidate::with(['pipelineStatus', 'candidate', 'status'])->findOrFail($trackerCandidateId);
        $hasResume = (bool) $trackerCandidate->candidate?->resume_file_url;

        if ($request->has('details')) {
            $updates = $this->pipelineService->buildChecklistDetailUpdates($field, $request->input('details'));
            $cascaded = [];

            if ($updates === []) {
                return response()->json(['message' => 'No valid pipeline details for this field.'], 422);
            }

            $pipeline = $this->upsertPipelineStatus($trackerCandidate, $updates)->fresh();

            if (!$this->pipelineService->isChecklistFieldDone($field, $pipeline, $hasResume)) {
                $cascade = $this->pipelineService->downstreamCascadeUpdates($field, $hasResume);
                if ($cascade !== []) {
                    $cascaded = $this->pipelineService->cascadeUncheckPreview($field, $hasResume);
                    $pipeline = $this->upsertPipelineStatus($trackerCandidate, $cascade)->fresh();
                }
            }
        } else {
            if (!$request->has('checked')) {
                return response()->json(['message' => 'Either checked or details is required.'], 422);
            }

            $checked = (bool) $request->boolean('checked');

            if ($field === 'doc_resume' && $hasResume && !$checked) {
                return response()->json(['message' => 'Resume is already on file from the uploaded CV.'], 422);
            }

            $updates = $this->pipelineService->applyChecklistUpdate($field, $checked, $hasResume);
            $cascaded = !$checked
                ? $this->pipelineService->cascadeUncheckPreview($field, $hasResume)
                : [$field];
        }

        if ($updates === []) {
            return response()->json(['message' => 'Invalid checklist field.'], 422);
        }

        $pipeline = $this->upsertPipelineStatus($trackerCandidate, $updates);
        $pipeline = $pipeline->fresh();
        $newStatusId = $this->pipelineService->resolveChecklistStatusId($pipeline, $hasResume);

        $trackerCandidate->update(['current_status_id' => $newStatusId]);
        $trackerCandidate->load('status');

        $trackerInfo = TrackerInfo::find($trackerId);
        if ($trackerInfo) {
            $trackerInfo->updateStatusFromCandidates();
        }

        return $this->checklistJsonResponse($trackerCandidate, $pipeline, $hasResume, $cascaded);
    }

    /**
     * @param  list<string>  $cascadedFields
     */
    private function checklistJsonResponse(
        TrackerCandidate $trackerCandidate,
        CandidatePipelineStatus $pipeline,
        bool $hasResume,
        array $cascadedFields = []
    ) {
        $items = $this->pipelineService->checklistItems($pipeline, $hasResume);

        return response()->json([
            'success' => true,
            'current_status_id' => $trackerCandidate->current_status_id,
            'current_status_label' => $trackerCandidate->status?->status ?? 'Unknown',
            'checklist_items' => $items,
            'checklist_progress' => $this->pipelineService->checklistProgress($items),
            'cascaded_fields' => $cascadedFields,
            'pipeline' => $this->pipelineService->preSubmissionPipelineSnapshot($pipeline, $hasResume),
        ]);
    }

    private function markCandidateRejected(string $trackerId, TrackerCandidate $trackerCandidate, string $reason)
    {
        $trackerCandidate->update([
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'current_status_id' => 18,
        ]);

        $trackerInfo = TrackerInfo::find($trackerId);
        if ($trackerInfo) {
            $trackerInfo->updateStatusFromCandidates();
        }

        return redirect()->route('tracker.info', $trackerId)->with('success', 'Candidate marked as rejected.');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPipelineDataFromRequest(Request $request, TrackerCandidate $trackerCandidate): array
    {
        $fields = [
            'resume_reviewed_by_recruiter', 'resume_reviewed_date',
            'recruiter_screening_call', 'recruiter_screening_call_date',
            'resume_submitted_to_client',
            'radix_internal_interview_prep', 'radix_internal_interview_prep_date',
            'client_resume_review',
            'client_interview_round_1_date', 'client_interview_round_2_date',
            'client_decision', 'client_decision_date',
            'client_confirmation_date', 'offer_extended_date',
            'background_check', 'candidate_project_start_date',
            'final_status_placement_completion', 'placement_completion_date',
        ];

        $data = [];
        foreach ($fields as $field) {
            if ($request->has($field) && $request->input($field) !== '') {
                $data[$field] = $request->input($field);
            }
        }

        $existing = $trackerCandidate->pipelineStatus;

        $data['candidate_identified'] = $request->boolean('candidate_identified') || $trackerCandidate->current_status_id >= 2;
        $data['requirement_reviewed'] = $request->has('requirement_reviewed')
            ? $request->boolean('requirement_reviewed')
            : (bool) ($existing?->requirement_reviewed);
        $data['doc_govt_id_collected'] = $request->has('doc_govt_id_collected')
            ? $request->boolean('doc_govt_id_collected')
            : (bool) ($existing?->doc_govt_id_collected);
        $data['doc_work_auth_collected'] = $request->has('doc_work_auth_collected')
            ? $request->boolean('doc_work_auth_collected')
            : (bool) ($existing?->doc_work_auth_collected);
        $data['doc_linkedin_collected'] = $request->has('doc_linkedin_collected')
            ? $request->boolean('doc_linkedin_collected')
            : (bool) ($existing?->doc_linkedin_collected);
        $data['rtr_signed'] = $request->has('rtr_signed')
            ? $request->boolean('rtr_signed')
            : (bool) ($existing?->rtr_signed);
        $data['candidate_shortlisted'] = $request->boolean('candidate_shortlisted');
        $data['client_confirmation_received'] = $request->boolean('client_confirmation_received');
        $data['offer_extended_to_candidate'] = $request->boolean('offer_extended_to_candidate');

        if ($request->has('additional_rounds_select')) {
            $data['additional_rounds'] = $request->input('additional_rounds_select') === 'Yes';
        } elseif ($request->has('additional_rounds')) {
            $data['additional_rounds'] = $request->boolean('additional_rounds');
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function upsertPipelineStatus(TrackerCandidate $trackerCandidate, array $data): CandidatePipelineStatus
    {
        if ($trackerCandidate->pipelineStatus) {
            $trackerCandidate->pipelineStatus->update($data);

            return $trackerCandidate->pipelineStatus->fresh();
        }

        $data['tracker_candidate_id'] = $trackerCandidate->id;
        $data['candidate_identified'] = $data['candidate_identified'] ?? true;

        return CandidatePipelineStatus::create($data);
    }

    public function rejectCandidate(Request $request, string $trackerId, string $trackerCandidateId)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $trackerCandidate = TrackerCandidate::findOrFail($trackerCandidateId);

        $trackerCandidate->update([
            'rejected_at'      => now(),
            'rejection_reason' => $request->rejection_reason ?: 'No reason provided.',
            'current_status_id' => 18,
        ]);

        $trackerInfo = TrackerInfo::find($trackerId);
        if ($trackerInfo) {
            $trackerInfo->updateStatusFromCandidates();
        }

        return redirect()->route('tracker.info', $trackerId)
            ->with('success', 'Candidate has been rejected and moved to the rejected section.');
    }

    public function revertCandidate(string $trackerId, string $trackerCandidateId)
    {
        $trackerCandidate = TrackerCandidate::with('pipelineStatus')->findOrFail($trackerCandidateId);

        if (!$trackerCandidate->rejected_at) {
            return redirect()->route('tracker.info', $trackerId)
                ->with('error', 'Only rejected candidates can be reverted.');
        }

        $this->pipelineService->resetCandidateToFreshStart($trackerCandidate);

        $trackerInfo = TrackerInfo::find($trackerId);
        if ($trackerInfo) {
            $trackerInfo->updateStatusFromCandidates();
        }

        return redirect()->route('tracker.info', $trackerId)
            ->with('success', 'Candidate reverted to the pipeline. Checklist and pipeline have been reset.');
    }

    public function markApproved(string $trackerId, string $trackerCandidateId)
    {
        $trackerCandidate = TrackerCandidate::with(['pipelineStatus', 'candidate'])->findOrFail($trackerCandidateId);

        if ($trackerCandidate->rejected_at) {
            return redirect()->route('tracker.info', $trackerId)
                ->with('error', 'Rejected candidates cannot be marked as approved.');
        }

        if ($trackerCandidate->approved_at) {
            return redirect()->route('tracker.info', $trackerId)
                ->with('error', 'This candidate is already marked as approved.');
        }

        if ($trackerCandidate->isPipelinePlaced()) {
            return redirect()->route('tracker.info', $trackerId)
                ->with('error', 'This candidate is already placed via the pipeline.');
        }

        $hasResume = (bool) $trackerCandidate->candidate?->resume_file_url;
        if (!$this->pipelineService->isChecklistComplete($trackerCandidate->pipelineStatus, $hasResume)) {
            return redirect()->route('tracker.info', $trackerId)
                ->with('error', 'Complete every checklist step before marking this candidate as approved.');
        }

        $trackerCandidate->update([
            'approved_at' => now(),
            'approved_stage' => TrackerCandidate::APPROVED_STAGE_IN_PROGRESS,
        ]);

        $trackerInfo = TrackerInfo::find($trackerId);
        if ($trackerInfo) {
            $trackerInfo->updateStatusFromCandidates();
        }

        return redirect()->route('tracker.info', $trackerId)
            ->with('success', 'Candidate marked as approved successfully.');
    }

    public function updateApprovedStage(Request $request, string $trackerId, string $trackerCandidateId)
    {
        $request->validate([
            'approved_stage' => 'required|in:' . implode(',', TrackerCandidate::APPROVED_STAGES),
        ]);

        $trackerCandidate = TrackerCandidate::with('pipelineStatus')->findOrFail($trackerCandidateId);

        if (!$trackerCandidate->approved_at) {
            return redirect()->route('tracker.info', $trackerId)
                ->with('error', 'Only approved candidates can have their post-approval status updated.');
        }

        if ($trackerCandidate->isPipelinePlaced()) {
            return redirect()->route('tracker.info', $trackerId)
                ->with('error', 'Placed candidates cannot have their status changed here.');
        }

        $trackerCandidate->update(['approved_stage' => $request->input('approved_stage')]);

        return redirect()->route('tracker.info', $trackerId)
            ->with('success', 'Candidate status updated to ' . $trackerCandidate->fresh()->approvedStageLabel() . '.');
    }

    /**
     * Resolve tracker candidate job status, forcing placement-confirmed when applicable.
     */
    private function resolveTrackerStatusId(
        TrackerCandidate $trackerCandidate,
        CandidatePipelineStatus $pipeline,
        bool $hasResume
    ): int {
        if ($pipeline->final_status_placement_completion === 'Confirmed') {
            $placementId = JobStatus::where('status', 'Candidate Placement Confirmed')->value('id');
            if ($placementId) {
                return (int) $placementId;
            }
        }

        return $this->pipelineService->resolveStatusId(
            $pipeline,
            $hasResume,
            (int) $trackerCandidate->current_status_id,
            false
        );
    }

    public function mailDraft(Request $request, string $trackerId, string $trackerCandidateId)
    {
        $validated = $request->validate([
            'from' => ['required', 'email', 'regex:/@rinfinite\.com$/i'],
            'to' => 'required|email',
            'cc' => 'nullable|array',
            'cc.*' => 'email',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'candidate_name' => 'nullable|string|max:255',
        ]);

        TrackerCandidate::where('tracker_info_id', $trackerId)->findOrFail($trackerCandidateId);

        $recruiterName = Auth::user()?->staffUser?->username ?? Auth::user()?->username ?? 'Recruiter';

        $email = (new Email())
            ->from(new Address($validated['from'], $recruiterName))
            ->to($validated['to'])
            ->subject($validated['subject']);

        if (! empty($validated['cc'])) {
            $email->cc(...array_map(fn (string $addr) => new Address($addr), $validated['cc']));
        }

        $body = $validated['body'];
        $logoPath = public_path('logo.png');
        if (! file_exists($logoPath)) {
            $logoPath = public_path('email_logo.png');
        }
        if (! file_exists($logoPath)) {
            $logoPath = public_path('report_banner.png');
        }

        if (file_exists($logoPath)) {
            $email->embedFromPath($logoPath, 'company_logo');
            $cidImg = '<img src="cid:company_logo" alt="RADiiX INFINITEii" style="max-width:140px;height:auto;display:block;">';
            $body = preg_replace(
                '/<img[^>]+src=["\'][^"\']*(?:logo|email_logo|report_banner)\.png[^"\']*["\'][^>]*>/i',
                $cidImg,
                $body
            );
            if (! str_contains($body, 'cid:company_logo')) {
                $body .= '<p style="margin-top:20px;">' . $cidImg . '</p>';
            }
        }

        $email->html($body);

        $mime = $email->toString();
        if (! str_contains($mime, 'X-Unsent:')) {
            $mime = preg_replace('/^(MIME-Version:)/m', "X-Unsent: 1\r\n$1", $mime, 1);
        }

        $slug = Str::slug($validated['candidate_name'] ?? 'candidate') ?: 'candidate';
        $filename = 'initialization-' . $slug . '.eml';
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($mime, 200, [
            'Content-Type' => 'message/rfc822',
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
        ]);
    }

    public function reportForm(string $trackerId, string $trackerCandidateId)
    {
        $trackerCandidate = TrackerCandidate::with([
            'candidate.location',
            'pipelineStatus',
            'status',
        ])->findOrFail($trackerCandidateId);

        $trackerInfo = TrackerInfo::with([
            'month', 'client', 'region', 'leadRecruiter', 'jobStatus',
        ])->findOrFail($trackerId);

        return view('tracker.report_form', compact('trackerCandidate', 'trackerInfo'));
    }

    public function generateReport(Request $request, string $trackerId, string $trackerCandidateId)
    {
        $request->validate([
            'recruiter_name'                 => 'nullable|string|max:255',
            'candidate_summary'              => 'nullable|string',
            'candidate_strong_points'        => 'nullable|string',
            'candidate_jd_skills'            => 'nullable|string',
            'additional_notes'               => 'nullable|string',
            'skill_communication_score'      => 'nullable|integer|min:1|max:10',
            'skill_communication_notes'      => 'nullable|string',
            'skill_technical_score'          => 'nullable|integer|min:1|max:10',
            'skill_technical_notes'          => 'nullable|string',
            'skill_problem_solving_score'    => 'nullable|integer|min:1|max:10',
            'skill_problem_solving_notes'    => 'nullable|string',
            'overall_recommendation'         => 'nullable|string',
        ]);

        $trackerCandidate = TrackerCandidate::with([
            'candidate.location',
            'pipelineStatus',
            'status',
        ])->findOrFail($trackerCandidateId);

        $trackerInfo = TrackerInfo::with([
            'month', 'client', 'region', 'leadRecruiter', 'jobStatus',
        ])->findOrFail($trackerId);

        // Build structured skills assessment from manual input
        $skillDefs = [
            'Communication Skills'    => ['skill_communication_score',   'skill_communication_notes'],
            'Technical Proficiency'   => ['skill_technical_score',        'skill_technical_notes'],
            'Professional Approach' => ['skill_problem_solving_score',  'skill_problem_solving_notes'],
        ];

        $skills = [];
        foreach ($skillDefs as $label => [$scoreKey, $notesKey]) {
            $score = $request->input($scoreKey);
            $notes = trim((string) $request->input($notesKey, ''));
            if ($score || $notes) {
                $skills[] = [
                    'label' => $label,
                    'score' => $score ?: null,
                    'notes' => $notes,
                ];
            }
        }

        // Brand assets sourced from the application (null-safe helper handles missing files)
        $bannerPath    = public_path('report_banner.png');
        $logoPath      = public_path('logo.png');
        $watermarkPath = public_path('watermark.png');

        $reportService = new \App\Services\Report\CandidateReportService();

        return $reportService->generate($trackerInfo, $trackerCandidate, [
            'company_name'           => 'RADiiX INFINITEii',
            'recruiter_name'         => $request->recruiter_name ?: ($trackerInfo->leadRecruiter->username ?? 'RADiiX INFINITEii'),
            'banner_path'            => $bannerPath,
            'logo_path'              => $logoPath,
            'watermark_path'         => $watermarkPath,
            'candidate_summary'      => $request->candidate_summary,
            'candidate_strong_points'=> $request->candidate_strong_points,
            'candidate_jd_skills'    => $request->candidate_jd_skills,
            'skills'                 => $skills,
            'overall_recommendation' => $request->overall_recommendation,
            'additional_notes'       => $request->additional_notes,
        ]);
    }
}
