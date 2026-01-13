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
use Illuminate\Http\Request;

class TrackerController extends Controller
{
    public function index(Request $request)
    {
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
        
        // Filter by month
        if ($request->filled('month_id')) {
            $query->where('month_id', $request->month_id);
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
        if ($request->filled('search')) {
            $search = $request->search;
            $baseCountQuery->where(function($q) use ($search) {
                $q->where('position', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$search%")
                  ->orWhereHas('client', function($q2) use ($search) {
                      $q2->where('client', 'like', "%$search%");
                  });
            });
        }
        if ($request->filled('month_id')) {
            $baseCountQuery->where('month_id', $request->month_id);
        }
        if ($request->filled('client_id')) {
            $baseCountQuery->where('client_id', $request->client_id);
        }
        if ($request->filled('lead_recruiter_id')) {
            $baseCountQuery->where('lr', $request->lead_recruiter_id);
        }

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
        
        if ($request->ajax()) {
            return response()->json([
                'table' => view('tracker._table', compact('trackerInfos'))->render(),
                'pagination' => $trackerInfos->appends(request()->query())->links('vendor.pagination.custom')->render(),
                'count_text' => "Showing {$trackerInfos->firstItem()} to {$trackerInfos->lastItem()} of {$trackerInfos->total()} entries",
                'counts' => $counts
            ]);
        }
        
        $months = Month::orderBy('id', 'desc')->get();
        $clients = Client::orderBy('client', 'asc')->get();
        $regions = Region::orderBy('region', 'asc')->get();
        $leadRecruiters = StaffUser::orderBy('id', 'desc')->get();
        
        return view('tracker.index', compact('trackerInfos', 'months', 'clients', 'regions', 'leadRecruiters', 'counts'));
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
        $trackerInfo = TrackerInfo::with(['month', 'client', 'region', 'leadRecruiter', 'trackerCandidates.candidate.location', 'trackerCandidates.pipelineStatus'])
            ->findOrFail($id);
        
        // Get all available candidates for assignment dropdown
        $availableCandidates = \App\Models\Candidate::with('location')->orderBy('full_name', 'asc')->get();
        
        $jobStatuses = \App\Models\JobStatus::all();
        
        return view('tracker.info', compact('trackerInfo', 'availableCandidates', 'jobStatuses'));
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
        $trackerCandidate = TrackerCandidate::with('pipelineStatus')->findOrFail($trackerCandidateId);
        
        if (!$trackerCandidate->pipelineStatus) {
            return response()->json([
                'candidate_identified' => false,
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
            ]);
        }

        $status = $trackerCandidate->pipelineStatus;
        return response()->json([
            'candidate_identified' => $status->candidate_identified,
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
        ]);
    }

    public function updatePipelineStatus(Request $request, string $trackerId, string $trackerCandidateId)
    {
        $request->validate([
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
            'current_status_id' => 'required|exists:job_status,id',
        ]);

        $trackerCandidate = TrackerCandidate::findOrFail($trackerCandidateId);
        
        // Automatic Status Progression Logic
        $currentStatusId = $trackerCandidate->current_status_id;
        
        if ($currentStatusId == 2 && ($request->resume_reviewed_by_recruiter == 'Completed')) {
            $request->merge(['current_status_id' => 3]); // Move to Resume Reviewed
        } elseif ($currentStatusId == 3 && ($request->recruiter_screening_call == 'Completed')) {
            $request->merge(['current_status_id' => 4]); // Move to Screening Call
        } elseif ($currentStatusId == 4 && $request->has('candidate_shortlisted')) {
            $request->merge(['current_status_id' => 5]); // Move to Shortlisted
        } elseif ($currentStatusId == 5 && $request->resume_submitted_to_client == 'Submitted') {
            $request->merge(['current_status_id' => 6]); // Move to Submitted to Client
        } elseif ($currentStatusId == 6 && in_array($request->radix_internal_interview_prep, ['Planned', 'Completed', 'Not Required'])) {
            $request->merge(['current_status_id' => 7]); // Move to Internal Prep
        } elseif ($currentStatusId == 7 && $request->client_resume_review == 'Approved') {
            $request->merge(['current_status_id' => 8]); // Move to Resume Accepted
        } elseif ($currentStatusId == 8 && $request->filled('client_interview_round_1_date')) {
            $request->merge(['current_status_id' => 9]); // Move to Interview 1
        } elseif ($currentStatusId == 9 && $request->filled('client_interview_round_2_date')) {
            $request->merge(['current_status_id' => 10]); // Move to Interview 2
        } elseif ($currentStatusId == 10 && $request->additional_rounds_select == 'Yes') {
            $request->merge(['current_status_id' => 11]); // Move to Additional Rounds
        } elseif ($currentStatusId == 10 && $request->additional_rounds_select == 'No') {
            $request->merge(['current_status_id' => 12]); // Move to Client Decision
        } elseif ($currentStatusId == 12 && $request->client_decision == 'Selected') {
            $request->merge(['current_status_id' => 13]); // Move to Client Confirmation
        } elseif ($currentStatusId == 12 && $request->client_decision == 'Rejected') {
            // Automatically remove candidate from job
            if ($trackerCandidate->pipelineStatus) {
                $trackerCandidate->pipelineStatus->delete();
            }
            $trackerCandidate->delete();
            
            $trackerInfo = TrackerInfo::find($trackerId);
            if ($trackerInfo) {
                $trackerInfo->updateStatusFromCandidates();
            }
            
            return redirect()->route('tracker.info', $trackerId)->with('success', 'Candidate rejected by client and removed from job.');
        } elseif ($currentStatusId == 7 && $request->client_resume_review == 'Rejected') {
            // Automatically remove candidate from job
            if ($trackerCandidate->pipelineStatus) {
                $trackerCandidate->pipelineStatus->delete();
            }
            $trackerCandidate->delete();
            
            // Update overall job status
            $trackerInfo = TrackerInfo::find($trackerId);
            if ($trackerInfo) {
                $trackerInfo->updateStatusFromCandidates();
            }
            
            return redirect()->route('tracker.info', $trackerId)->with('success', 'Candidate rejected by client and removed from job.');
        }

        // Refresh data from request after merges
        $data = array_filter($request->all(), function($value) {
            return $value !== null && $value !== '';
        });

        // Convert checkboxes/logic
        $data['candidate_identified'] = $request->has('candidate_identified') || $currentStatusId >= 2;
        $data['candidate_shortlisted'] = $request->has('candidate_shortlisted') || $currentStatusId >= 5;
        
        // Handle additional_rounds from select if present
        if ($request->has('additional_rounds_select')) {
            $data['additional_rounds'] = ($request->additional_rounds_select == 'Yes');
        }

        if ($trackerCandidate->pipelineStatus) {
            $trackerCandidate->pipelineStatus->update($data);
        } else {
            $data['tracker_candidate_id'] = $trackerCandidate->id;
            CandidatePipelineStatus::create($data);
        }

        // Update current status in tracker_candidates
        $trackerCandidate->update([
            'current_status_id' => $request->current_status_id
        ]);

        // Update overall job status based on majority
        $trackerInfo = TrackerInfo::find($trackerId);
        if ($trackerInfo) {
            $trackerInfo->updateStatusFromCandidates();
        }

        return redirect()->route('tracker.info', $trackerId)->with('success', 'Pipeline status updated successfully.');
    }
}
