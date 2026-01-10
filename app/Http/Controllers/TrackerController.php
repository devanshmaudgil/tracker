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
    public function index()
    {
        $trackerInfos = TrackerInfo::with(['month', 'client', 'region', 'leadRecruiter'])
            ->orderBy('id', 'desc')
            ->get();
        $months = Month::orderBy('id', 'desc')->get();
        $clients = Client::orderBy('client', 'asc')->get();
        $regions = Region::orderBy('region', 'asc')->get();
        $leadRecruiters = StaffUser::orderBy('id', 'desc')->get();
        
        return view('tracker.index', compact('trackerInfos', 'months', 'clients', 'regions', 'leadRecruiters'));
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

        TrackerInfo::create($request->all());

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
        
        return view('tracker.info', compact('trackerInfo', 'availableCandidates'));
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
        ]);

        // Create initial pipeline status record
        CandidatePipelineStatus::create([
            'tracker_candidate_id' => $trackerCandidate->id,
        ]);

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
        ]);

        $trackerCandidate = TrackerCandidate::findOrFail($trackerCandidateId);
        
        $data = $request->all();
        // Convert checkboxes
        $data['candidate_identified'] = $request->has('candidate_identified');
        $data['candidate_shortlisted'] = $request->has('candidate_shortlisted');
        $data['additional_rounds'] = $request->has('additional_rounds');
        $data['client_confirmation_received'] = $request->has('client_confirmation_received');
        $data['offer_extended_to_candidate'] = $request->has('offer_extended_to_candidate');

        if ($trackerCandidate->pipelineStatus) {
            $trackerCandidate->pipelineStatus->update($data);
        } else {
            $data['tracker_candidate_id'] = $trackerCandidate->id;
            CandidatePipelineStatus::create($data);
        }

        return redirect()->route('tracker.info', $trackerId)->with('success', 'Pipeline status updated successfully.');
    }
}
