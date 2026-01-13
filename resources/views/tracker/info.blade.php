@extends('layouts.app')

@section('title', 'Tracker Details')

@section('content')
<div class="content-header">
    <h1>Tracker Details</h1>
    <a href="{{ route('tracker.index') }}" class="btn btn-secondary">Back</a>
</div>

<!-- Job Information Section -->
<div style="background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <h2 style="color: #0a2d29; margin-bottom: 20px; border-bottom: 2px solid #f1cd86; padding-bottom: 10px;">Job Information</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
        <div>
            <strong>Month:</strong> {{ $trackerInfo->month->month ?? 'N/A' }}
        </div>
        <div>
            <strong>Client:</strong> {{ $trackerInfo->client->client ?? 'N/A' }}
        </div>
        <div>
            <strong>Job Location:</strong> 
            @if($trackerInfo->region)
                @if($trackerInfo->region->city)
                    {{ $trackerInfo->region->city }}, {{ $trackerInfo->region->region }}
                @else
                    {{ $trackerInfo->region->region }}
                @endif
            @else
                N/A
            @endif
        </div>
        <div>
            <strong>Type of Job:</strong> {{ $trackerInfo->type_of_job ? ucfirst($trackerInfo->type_of_job) : 'N/A' }}
        </div>
        <div>
            <strong>Position:</strong> {{ $trackerInfo->position ?? 'N/A' }}
        </div>
        <div>
            <strong>Bill Rate / Salary Range:</strong> {{ $trackerInfo->bill_rate_salary_range ?? 'N/A' }}
        </div>
        <div>
            <strong>Priority:</strong> {{ $trackerInfo->priority ?? 'N/A' }}
        </div>
        <div>
            <strong>Submission Deadline:</strong> {{ $trackerInfo->submission_deadline ? $trackerInfo->submission_deadline->format('M d, Y') : 'N/A' }}
        </div>
        <div>
            <strong>PRD:</strong> {{ $trackerInfo->prd ? $trackerInfo->prd->format('M d, Y') : 'N/A' }}
        </div>
        <div>
            <strong>CF:</strong> {{ $trackerInfo->cf ?? 'N/A' }}
        </div>
        <div>
            <strong>Lead Recruiter:</strong> {{ $trackerInfo->leadRecruiter ? $trackerInfo->leadRecruiter->username : 'N/A' }}
        </div>
        <div>
            <strong>CSI:</strong> {{ $trackerInfo->csi ?? 'N/A' }}
        </div>
    </div>
</div>

<!-- Assign Candidates Section -->
<div style="background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <h2 style="color: #0a2d29; margin-bottom: 20px; border-bottom: 2px solid #f1cd86; padding-bottom: 10px;">Assign Candidates</h2>
    <form method="POST" action="{{ route('tracker.candidates.assign', $trackerInfo->id) }}" style="margin-bottom: 20px;">
        @csrf
        <div style="display: flex; gap: 10px; align-items: flex-end;">
            <div style="flex: 1;">
                <label for="candidate_id" style="display: block; margin-bottom: 5px; font-weight: 600; color: #0a2d29;">Select Candidate</label>
                <select id="candidate_id" name="candidate_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Select Candidate</option>
                    @foreach($availableCandidates as $candidate)
                        @php
                            $alreadyAssigned = $trackerInfo->trackerCandidates->pluck('candidate_id')->contains($candidate->id);
                        @endphp
                        @if(!$alreadyAssigned)
                            <option value="{{ $candidate->id }}">
                                {{ $candidate->full_name }} 
                                @if($candidate->email) - {{ $candidate->email }} @endif
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Assign Candidate</button>
            <button type="button" class="btn btn-success" onclick="openCreateCandidateModal()">Create New Candidate</button>
        </div>
    </form>

    <!-- Assigned Candidates List -->
    @if($trackerInfo->trackerCandidates->count() > 0)
        <div style="margin-top: 20px;">
            <h3 style="color: #0a2d29; margin-bottom: 15px;">Assigned Candidates</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                    <thead>
                        <tr style="background-color: #0a2d29; color: white;">
                            <th style="padding: 8px; text-align: center; white-space: nowrap;">S.No</th>
                            <th style="padding: 8px; text-align: center; white-space: nowrap;">Candidate Name</th>
                            <th style="padding: 8px; text-align: center; white-space: nowrap;">Email</th>
                            <th style="padding: 8px; text-align: center; white-space: nowrap;">Phone</th>
                            <th style="padding: 8px; text-align: center; white-space: nowrap;">Location</th>
                            <th style="padding: 8px; text-align: center; white-space: nowrap;">Work Status</th>
                            <th style="padding: 8px; text-align: center; white-space: nowrap;">Resume</th>
                            <th style="padding: 8px; text-align: center; white-space: nowrap;">Pipeline Status</th>
                            <th style="padding: 8px; text-align: center; white-space: nowrap;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trackerInfo->trackerCandidates as $index => $trackerCandidate)
                            @php
                                $status = $trackerCandidate->pipelineStatus;
                            @endphp
                            <tr data-tracker-candidate-id="{{ $trackerCandidate->id }}"
                                data-candidate-identified="{{ $status && $status->candidate_identified ? '1' : '0' }}"
                                data-resume-reviewed="{{ $status ? ($status->resume_reviewed_by_recruiter ?? '') : '' }}"
                                data-resume-reviewed-date="{{ $status && $status->resume_reviewed_date ? $status->resume_reviewed_date->format('Y-m-d') : '' }}"
                                data-recruiter-screening="{{ $status ? ($status->recruiter_screening_call ?? '') : '' }}"
                                data-recruiter-screening-date="{{ $status && $status->recruiter_screening_call_date ? $status->recruiter_screening_call_date->format('Y-m-d') : '' }}"
                                data-shortlisted="{{ $status && $status->candidate_shortlisted ? '1' : '0' }}"
                                data-resume-submitted="{{ $status ? ($status->resume_submitted_to_client ?? '') : '' }}"
                                data-radix-prep="{{ $status ? ($status->radix_internal_interview_prep ?? '') : '' }}"
                                data-radix-prep-date="{{ $status && $status->radix_internal_interview_prep_date ? $status->radix_internal_interview_prep_date->format('Y-m-d') : '' }}"
                                data-client-review="{{ $status ? ($status->client_resume_review ?? '') : '' }}"
                                data-interview-round-1="{{ $status && $status->client_interview_round_1_date ? $status->client_interview_round_1_date->format('Y-m-d') : '' }}"
                                data-interview-round-2="{{ $status && $status->client_interview_round_2_date ? $status->client_interview_round_2_date->format('Y-m-d') : '' }}"
                                data-additional-rounds="{{ $status && $status->additional_rounds ? '1' : '0' }}"
                                data-client-decision="{{ $status ? ($status->client_decision ?? '') : '' }}"
                                data-client-decision-date="{{ $status && $status->client_decision_date ? $status->client_decision_date->format('Y-m-d') : '' }}"
                                data-confirmation-received="{{ $status && $status->client_confirmation_received ? '1' : '0' }}"
                                data-confirmation-date="{{ $status && $status->client_confirmation_date ? $status->client_confirmation_date->format('Y-m-d') : '' }}"
                                data-offer-extended="{{ $status && $status->offer_extended_to_candidate ? '1' : '0' }}"
                                data-offer-extended-date="{{ $status && $status->offer_extended_date ? $status->offer_extended_date->format('Y-m-d') : '' }}"
                                data-background-check="{{ $status ? ($status->background_check ?? '') : '' }}"
                                data-project-start="{{ $status && $status->candidate_project_start_date ? $status->candidate_project_start_date->format('Y-m-d') : '' }}"
                                data-final-status="{{ $status ? ($status->final_status_placement_completion ?? '') : '' }}"
                                data-placement-date="{{ $status && $status->placement_completion_date ? $status->placement_completion_date->format('Y-m-d') : '' }}">
                                <td style="padding: 8px; text-align: center; border-bottom: 1px solid #ddd;">{{ $index + 1 }}</td>
                                <td style="padding: 8px; text-align: center; border-bottom: 1px solid #ddd;">{{ $trackerCandidate->candidate->full_name }}</td>
                                <td style="padding: 8px; text-align: center; border-bottom: 1px solid #ddd;">{{ $trackerCandidate->candidate->email }}</td>
                                <td style="padding: 8px; text-align: center; border-bottom: 1px solid #ddd;">{{ $trackerCandidate->candidate->phone ?? 'N/A' }}</td>
                                <td style="padding: 8px; text-align: center; border-bottom: 1px solid #ddd;">
                                    @if($trackerCandidate->candidate->location)
                                        @if($trackerCandidate->candidate->location->city)
                                            {{ $trackerCandidate->candidate->location->city }}, {{ $trackerCandidate->candidate->location->region }}
                                        @else
                                            {{ $trackerCandidate->candidate->location->region }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td style="padding: 8px; text-align: center; border-bottom: 1px solid #ddd;">{{ $trackerCandidate->candidate->work_status ?? 'N/A' }}</td>
                                <td style="padding: 8px; text-align: center; border-bottom: 1px solid #ddd;">
                                    @if($trackerCandidate->candidate->resume_file_url)
                                        <a href="{{ $trackerCandidate->candidate->resume_file_url }}" target="_blank" style="color: #f1cd86;">View</a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td style="padding: 8px; text-align: center; border-bottom: 1px solid #ddd;">
                                    @php
                                        $status = $trackerCandidate->pipelineStatus;
                                        $currentStage = 'Not Started';
                                        if ($status) {
                                            if ($status->final_status_placement_completion) {
                                                $currentStage = 'Completed';
                                            } elseif ($status->candidate_project_start_date) {
                                                $currentStage = 'Started';
                                            } elseif ($status->offer_extended_to_candidate) {
                                                $currentStage = 'Offer Extended';
                                            } elseif ($status->client_decision) {
                                                $currentStage = $status->client_decision;
                                            } elseif ($status->client_interview_round_2_date || $status->client_interview_round_1_date) {
                                                $currentStage = 'Interviewing';
                                            } elseif ($status->resume_submitted_to_client == 'Submitted') {
                                                $currentStage = 'Submitted';
                                            } elseif ($status->candidate_shortlisted) {
                                                $currentStage = 'Shortlisted';
                                            } elseif ($status->candidate_identified) {
                                                $currentStage = 'Identified';
                                            }
                                        }
                                    @endphp
                                    <span style="font-size: 11px; padding: 4px 8px; background-color: #f1cd86; color: #0a2d29; border-radius: 4px; display: inline-block;">{{ $currentStage }}</span>
                                </td>
                                <td style="padding: 8px; text-align: center; border-bottom: 1px solid #ddd;">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="openPipelineModal({{ $trackerCandidate->id }})">Pipeline</button>
                                    <form method="POST" action="{{ route('tracker.candidates.unassign', ['tracker_id' => $trackerInfo->id, 'tracker_candidate_id' => $trackerCandidate->id]) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this candidate from this job?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <p style="color: #666; text-align: center; padding: 20px;">No candidates assigned yet. Select a candidate above to assign.</p>
    @endif
</div>

<!-- Pipeline Status Modal -->
<div id="pipelineModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 900px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h2 id="pipelineModalTitle">Candidate Pipeline Status</h2>
            <span class="close" onclick="closePipelineModal()">&times;</span>
        </div>
        <form id="pipelineForm" method="POST">
            @csrf
            @method('PUT')
            <div style="padding: 20px;">
                <!-- Stage 1 -->
                <div class="pipeline-stage">
                    <h3 style="color: #0a2d29; margin-bottom: 10px;">1. Candidate Identified</h3>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="candidate_identified" name="candidate_identified" value="1">
                            Candidate Identified (Yes/No)
                        </label>
                    </div>
                </div>

                <!-- Stage 2 -->
                <div class="pipeline-stage">
                    <h3 style="color: #0a2d29; margin-bottom: 10px;">2. Resume Reviewed by Recruiter</h3>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="resume_reviewed_by_recruiter" name="resume_reviewed_by_recruiter" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">Select Status</option>
                            <option value="Completed">Completed</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="resume_reviewed_date" name="resume_reviewed_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <!-- Stage 3 -->
                <div class="pipeline-stage">
                    <h3 style="color: #0a2d29; margin-bottom: 10px;">3. Recruiter Screening Call</h3>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="recruiter_screening_call" name="recruiter_screening_call" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">Select Status</option>
                            <option value="Completed">Completed</option>
                            <option value="Pending">Pending</option>
                            <option value="No Show">No Show</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="recruiter_screening_call_date" name="recruiter_screening_call_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <!-- Stage 4 -->
                <div class="pipeline-stage">
                    <h3 style="color: #0a2d29; margin-bottom: 10px;">4. Candidate Shortlisted</h3>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="candidate_shortlisted" name="candidate_shortlisted" value="1">
                            Candidate Shortlisted (Yes/No)
                        </label>
                    </div>
                </div>

                <!-- Stage 5 -->
                <div class="pipeline-stage">
                    <h3 style="color: #0a2d29; margin-bottom: 10px;">5. Resume Submitted to Client</h3>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="resume_submitted_to_client" name="resume_submitted_to_client" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">Select Status</option>
                            <option value="Submitted">Submitted</option>
                            <option value="Not Submitted">Not Submitted</option>
                        </select>
                    </div>
                </div>

                <!-- Stage 6 -->
                <div class="pipeline-stage">
                    <h3 style="color: #0a2d29; margin-bottom: 10px;">6. RADIX Internal Interview Prep</h3>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="radix_internal_interview_prep" name="radix_internal_interview_prep" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">Select Status</option>
                            <option value="Completed">Completed</option>
                            <option value="Planned">Planned</option>
                            <option value="Not Required">Not Required</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="radix_internal_interview_prep_date" name="radix_internal_interview_prep_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <!-- Stage 7 -->
                <div class="pipeline-stage">
                    <h3 style="color: #0a2d29; margin-bottom: 10px;">7. Client Resume Review</h3>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="client_resume_review" name="client_resume_review" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">Select Status</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <!-- Stage 8 -->
                <div class="pipeline-stage">
                    <h3 style="color: #0a2d29; margin-bottom: 10px;">8. Client Interview - Round 1</h3>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="client_interview_round_1_date" name="client_interview_round_1_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <!-- Stage 9 -->
                <div class="pipeline-stage">
                    <h3 style="color: #0a2d29; margin-bottom: 10px;">9. Client Interview - Round 2</h3>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="client_interview_round_2_date" name="client_interview_round_2_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <!-- Stage 10 -->
                <div class="pipeline-stage">
                    <h3 style="color: #0a2d29; margin-bottom: 10px;">10. Additional Rounds (Tech/Manager/Panel)</h3>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="additional_rounds" name="additional_rounds" value="1">
                            Additional Rounds (Yes/No)
                        </label>
                    </div>
                </div>

                <!-- Stage 11 -->
                <div class="pipeline-stage" style="background-color: #fff5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #dc3545;">
                    <h3 style="color: #dc3545; margin-bottom: 10px;">11. Client Decision</h3>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="client_decision" name="client_decision" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">Select Decision</option>
                            <option value="Selected">Selected</option>
                            <option value="Rejected">Rejected</option>
                            <option value="On Hold">On Hold</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="client_decision_date" name="client_decision_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <!-- Stage 12 -->
                <div class="pipeline-stage" style="background-color: #fff5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #dc3545;">
                    <h3 style="color: #dc3545; margin-bottom: 10px;">12. Client Confirmation Received</h3>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="client_confirmation_received" name="client_confirmation_received" value="1">
                            Client Confirmation Received (Yes/No)
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="client_confirmation_date" name="client_confirmation_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <!-- Stage 13 -->
                <div class="pipeline-stage" style="background-color: #fff5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #dc3545;">
                    <h3 style="color: #dc3545; margin-bottom: 10px;">13. Offer Extended to Candidate</h3>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="offer_extended_to_candidate" name="offer_extended_to_candidate" value="1">
                            Offer Extended to Candidate (Yes/No)
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="offer_extended_date" name="offer_extended_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <!-- Stage 14 -->
                <div class="pipeline-stage" style="background-color: #fff5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #dc3545;">
                    <h3 style="color: #dc3545; margin-bottom: 10px;">14. Background Check</h3>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="background_check" name="background_check" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">Select Status</option>
                            <option value="Completed">Completed</option>
                            <option value="Initiated">Initiated</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                </div>

                <!-- Stage 15 -->
                <div class="pipeline-stage" style="background-color: #fff5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #dc3545;">
                    <h3 style="color: #dc3545; margin-bottom: 10px;">15. Candidate Project Start Date</h3>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="candidate_project_start_date" name="candidate_project_start_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <!-- Stage 16 -->
                <div class="pipeline-stage" style="background-color: #fff5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #dc3545;">
                    <h3 style="color: #dc3545; margin-bottom: 10px;">16. Final Status - Placement Completion</h3>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="final_status_placement_completion" name="final_status_placement_completion" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">Select Status</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Not Confirmed">Not Confirmed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="placement_completion_date" name="placement_completion_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Update Pipeline Status</button>
                    <button type="button" class="btn btn-secondary" onclick="closePipelineModal()">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .pipeline-stage {
        margin-bottom: 25px;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 4px;
        border-left: 4px solid #0a2d29;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #0a2d29;
        font-size: 14px;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: white;
        margin: 3% auto;
        padding: 0;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #0a2d29;
        color: white;
        border-radius: 8px 8px 0 0;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .modal-header h2 {
        margin: 0;
        color: white;
        font-size: 24px;
    }

    .close {
        color: white;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 20px;
    }

    .close:hover {
        opacity: 0.7;
    }

    #location_dropdown .location-option:hover {
        background-color: #f5f5f5 !important;
    }

    #location_dropdown .location-option:last-child {
        border-bottom: none;
    }

    #location_search:focus {
        outline: none;
        border-color: #f1cd86;
    }
</style>

<script>
    let currentTrackerCandidateId = null;

    function openPipelineModal(trackerCandidateId) {
        currentTrackerCandidateId = trackerCandidateId;
        const modal = document.getElementById('pipelineModal');
        const form = document.getElementById('pipelineForm');
        
        form.action = `/tracker/info/{{ $trackerInfo->id }}/candidates/${trackerCandidateId}/pipeline`;
        
        // Always fetch from API to ensure we have the latest data from database
        fetch(`/tracker/info/{{ $trackerInfo->id }}/candidates/${trackerCandidateId}/pipeline`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('candidate_identified').checked = data.candidate_identified || false;
                document.getElementById('resume_reviewed_by_recruiter').value = data.resume_reviewed_by_recruiter || '';
                document.getElementById('resume_reviewed_date').value = data.resume_reviewed_date || '';
                document.getElementById('recruiter_screening_call').value = data.recruiter_screening_call || '';
                document.getElementById('recruiter_screening_call_date').value = data.recruiter_screening_call_date || '';
                document.getElementById('candidate_shortlisted').checked = data.candidate_shortlisted || false;
                document.getElementById('resume_submitted_to_client').value = data.resume_submitted_to_client || '';
                document.getElementById('radix_internal_interview_prep').value = data.radix_internal_interview_prep || '';
                document.getElementById('radix_internal_interview_prep_date').value = data.radix_internal_interview_prep_date || '';
                document.getElementById('client_resume_review').value = data.client_resume_review || '';
                document.getElementById('client_interview_round_1_date').value = data.client_interview_round_1_date || '';
                document.getElementById('client_interview_round_2_date').value = data.client_interview_round_2_date || '';
                document.getElementById('additional_rounds').checked = data.additional_rounds || false;
                document.getElementById('client_decision').value = data.client_decision || '';
                document.getElementById('client_decision_date').value = data.client_decision_date || '';
                document.getElementById('client_confirmation_received').checked = data.client_confirmation_received || false;
                document.getElementById('client_confirmation_date').value = data.client_confirmation_date || '';
                document.getElementById('offer_extended_to_candidate').checked = data.offer_extended_to_candidate || false;
                document.getElementById('offer_extended_date').value = data.offer_extended_date || '';
                document.getElementById('background_check').value = data.background_check || '';
                document.getElementById('candidate_project_start_date').value = data.candidate_project_start_date || '';
                document.getElementById('final_status_placement_completion').value = data.final_status_placement_completion || '';
                document.getElementById('placement_completion_date').value = data.placement_completion_date || '';
                
                modal.style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching pipeline data:', error);
                // Fallback to data attributes if API fails
                const row = document.querySelector(`tr[data-tracker-candidate-id="${trackerCandidateId}"]`);
                if (row) {
                    document.getElementById('candidate_identified').checked = row.dataset.candidateIdentified === '1';
                    document.getElementById('resume_reviewed_by_recruiter').value = row.dataset.resumeReviewed || '';
                    document.getElementById('resume_reviewed_date').value = row.dataset.resumeReviewedDate || '';
                    document.getElementById('recruiter_screening_call').value = row.dataset.recruiterScreening || '';
                    document.getElementById('recruiter_screening_call_date').value = row.dataset.recruiterScreeningDate || '';
                    document.getElementById('candidate_shortlisted').checked = row.dataset.shortlisted === '1';
                    document.getElementById('resume_submitted_to_client').value = row.dataset.resumeSubmitted || '';
                    document.getElementById('radix_internal_interview_prep').value = row.dataset.radixPrep || '';
                    document.getElementById('radix_internal_interview_prep_date').value = row.dataset.radixPrepDate || '';
                    document.getElementById('client_resume_review').value = row.dataset.clientReview || '';
                    document.getElementById('client_interview_round_1_date').value = row.dataset.interviewRound1 || '';
                    document.getElementById('client_interview_round_2_date').value = row.dataset.interviewRound2 || '';
                    document.getElementById('additional_rounds').checked = row.dataset.additionalRounds === '1';
                    document.getElementById('client_decision').value = row.dataset.clientDecision || '';
                    document.getElementById('client_decision_date').value = row.dataset.clientDecisionDate || '';
                    document.getElementById('client_confirmation_received').checked = row.dataset.confirmationReceived === '1';
                    document.getElementById('client_confirmation_date').value = row.dataset.confirmationDate || '';
                    document.getElementById('offer_extended_to_candidate').checked = row.dataset.offerExtended === '1';
                    document.getElementById('offer_extended_date').value = row.dataset.offerExtendedDate || '';
                    document.getElementById('background_check').value = row.dataset.backgroundCheck || '';
                    document.getElementById('candidate_project_start_date').value = row.dataset.projectStart || '';
                    document.getElementById('final_status_placement_completion').value = row.dataset.finalStatus || '';
                    document.getElementById('placement_completion_date').value = row.dataset.placementDate || '';
                }
                modal.style.display = 'block';
            });
    }

    function closePipelineModal() {
        document.getElementById('pipelineModal').style.display = 'none';
        document.getElementById('pipelineForm').reset();
        currentTrackerCandidateId = null;
    }

    window.onclick = function(event) {
        const modal = document.getElementById('pipelineModal');
        if (event.target == modal) {
            closePipelineModal();
        }
        const candidateModal = document.getElementById('createCandidateModal');
        if (event.target == candidateModal) {
            closeCreateCandidateModal();
        }
    }

    function openCreateCandidateModal() {
        document.getElementById('createCandidateModal').style.display = 'block';
        // Reset location search when opening modal
        resetLocationSearch();
        // Initialize location search after a short delay to ensure DOM is ready
        setTimeout(function() {
            initLocationSearch();
        }, 100);
    }

    function closeCreateCandidateModal() {
        document.getElementById('createCandidateModal').style.display = 'none';
        document.getElementById('createCandidateForm').reset();
        resetLocationSearch();
    }

    // Location search functionality
    let allLocationOptions = [];
    let selectedLocationText = '';

    function initLocationSearch() {
        const locationSearch = document.getElementById('location_search');
        const locationDropdown = document.getElementById('location_dropdown');
        const locationHidden = document.getElementById('create_location_id');
        
        if (!locationSearch || !locationDropdown) return;

        // Store all options
        allLocationOptions = Array.from(locationDropdown.querySelectorAll('.location-option'));
        
        // Set initial value if exists
        const initialValue = locationHidden.value;
        if (initialValue) {
            const selectedOption = allLocationOptions.find(opt => opt.dataset.value === initialValue);
            if (selectedOption) {
                selectedLocationText = selectedOption.textContent.trim();
                locationSearch.value = selectedLocationText;
            }
        }

        // Search input event
        locationSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            
            if (searchTerm === '') {
                showAllLocations();
                locationDropdown.style.display = 'block';
                return;
            }

            filterLocations(searchTerm);
            locationDropdown.style.display = 'block';
        });

        // Focus event
        locationSearch.addEventListener('focus', function() {
            if (this.value.trim() === '') {
                showAllLocations();
            }
            locationDropdown.style.display = 'block';
        });

        // Click on option
        allLocationOptions.forEach(option => {
            option.addEventListener('click', function() {
                locationHidden.value = this.dataset.value;
                selectedLocationText = this.textContent.trim();
                locationSearch.value = selectedLocationText;
                locationDropdown.style.display = 'none';
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!locationSearch.contains(e.target) && !locationDropdown.contains(e.target)) {
                locationDropdown.style.display = 'none';
                // Restore selected value if search was cleared
                if (locationSearch.value.trim() === '' && selectedLocationText) {
                    locationSearch.value = selectedLocationText;
                } else if (locationSearch.value.trim() !== selectedLocationText) {
                    locationSearch.value = selectedLocationText || '';
                }
            }
        });
    }

    function filterLocations(searchTerm) {
        allLocationOptions.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    }

    function showAllLocations() {
        allLocationOptions.forEach(option => {
            option.style.display = 'block';
        });
    }

    function resetLocationSearch() {
        const locationSearch = document.getElementById('location_search');
        const locationHidden = document.getElementById('create_location_id');
        const locationDropdown = document.getElementById('location_dropdown');
        
        if (locationSearch) {
            locationSearch.value = '';
        }
        if (locationHidden) {
            locationHidden.value = '';
        }
        if (locationDropdown) {
            locationDropdown.style.display = 'none';
        }
        selectedLocationText = '';
        showAllLocations();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initLocationSearch();
        });
    } else {
        // DOM already loaded, initialize after a short delay
        setTimeout(function() {
            initLocationSearch();
        }, 100);
    }
</script>

<!-- Create Candidate Modal -->
<div id="createCandidateModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h2>Create New Candidate</h2>
            <span class="close" onclick="closeCreateCandidateModal()">&times;</span>
        </div>
        <form id="createCandidateForm" method="POST" action="{{ route('candidates.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tracker_id" value="{{ $trackerInfo->id }}">
            
            <div style="padding: 20px;">
                <div class="form-group">
                    <label for="create_full_name">Candidate Full Name *</label>
                    <input type="text" id="create_full_name" name="full_name" value="{{ old('full_name') }}" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    @error('full_name')
                        <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="create_email">Candidate Email Id *</label>
                    <input type="email" id="create_email" name="email" value="{{ old('email') }}" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    @error('email')
                        <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="create_phone">Candidate Phone</label>
                    <input type="text" id="create_phone" name="phone" value="{{ old('phone') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    @error('phone')
                        <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="create_location_id">Candidate Location (City, State)</label>
                    <div style="position: relative;">
                        <input type="text" id="location_search" placeholder="Search location..." autocomplete="off" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                        <input type="hidden" id="create_location_id" name="location_id" value="{{ old('location_id') }}">
                        <div id="location_dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; border-radius: 0 0 4px 4px; max-height: 200px; overflow-y: auto; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            @foreach(\App\Models\Region::orderBy('region', 'asc')->get() as $region)
                                <div class="location-option" data-value="{{ $region->id }}" style="padding: 10px; cursor: pointer; border-bottom: 1px solid #f0f0f0;" onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor='white'">
                                    @if($region->city)
                                        {{ $region->city }}, {{ $region->region }}
                                    @else
                                        {{ $region->region }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('location_id')
                        <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="create_work_status">Candidate Work Status</label>
                    <select id="create_work_status" name="work_status" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">Select Work Status</option>
                        <option value="GC" {{ old('work_status') == 'GC' ? 'selected' : '' }}>GC</option>
                        <option value="PR" {{ old('work_status') == 'PR' ? 'selected' : '' }}>PR</option>
                        <option value="Citizen" {{ old('work_status') == 'Citizen' ? 'selected' : '' }}>Citizen</option>
                        <option value="H1B" {{ old('work_status') == 'H1B' ? 'selected' : '' }}>H1B</option>
                        <option value="OPT" {{ old('work_status') == 'OPT' ? 'selected' : '' }}>OPT</option>
                    </select>
                    @error('work_status')
                        <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="create_current_company">Current Company</label>
                    <input type="text" id="create_current_company" name="current_company" value="{{ old('current_company') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    @error('current_company')
                        <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="create_pay_rate">Candidate Pay-Rate</label>
                    <input type="text" id="create_pay_rate" name="pay_rate" value="{{ old('pay_rate') }}" placeholder="e.g., $50/hr or $100k/year" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    @error('pay_rate')
                        <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="create_agency_name">Candidate Agency Name</label>
                    <input type="text" id="create_agency_name" name="agency_name" value="{{ old('agency_name') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    @error('agency_name')
                        <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="create_agency_poc">Candidate Agency POC (Point-of-Contact)</label>
                    <input type="text" id="create_agency_poc" name="agency_poc" value="{{ old('agency_poc') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    @error('agency_poc')
                        <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="create_agency_poc_phone">Candidate Agency POC Phone Number</label>
                    <input type="text" id="create_agency_poc_phone" name="agency_poc_phone" value="{{ old('agency_poc_phone') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    @error('agency_poc_phone')
                        <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="create_resume_file">Resume Link / File Name (PDF, JPG, PNG)</label>
                    <input type="file" id="create_resume_file" name="resume_file" accept=".pdf,.jpg,.jpeg,.png" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    @error('resume_file')
                        <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Create & Assign to Job #{{ $trackerInfo->id }}</button>
                    <button type="button" class="btn btn-secondary" onclick="closeCreateCandidateModal()">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

