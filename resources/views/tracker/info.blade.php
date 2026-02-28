@extends('layouts.app')

@section('title', 'Tracker Details')

@section('content')
<div class="content-header">
    <h1>Tracker Details</h1>
    <div class="header-actions" style="display: flex; gap: 10px;">
        <a href="{{ route('tracker.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>

<style>
    .details-card {
        background: white;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .details-card h2 {
        color: #0a2d29;
        margin-bottom: 15px;
        border-bottom: 2px solid #f1cd86;
        padding-bottom: 8px;
        font-size: 18px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        font-size: 13px;
    }
    
    .info-item strong {
        color: #0a2d29;
    }
    
    .assign-form {
        display: flex;
        gap: 10px;
        align-items: flex-end;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }
    
    .assign-form .form-group {
        flex: 0 0 300px; /* Fixed width of 300px */
        min-width: 200px;
        margin-bottom: 0;
    }
    
    .assign-form .button-container {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 0;
    }
    
    .assign-form label {
        display: block;
        margin-bottom: 4px;
        font-weight: 600;
        color: #0a2d29;
        font-size: 12px;
    }
    
    .assign-form select {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
    }
    
    .compact-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }
    
    .compact-table th {
        background-color: #0a2d29;
        color: white;
        padding: 6px 4px;
        text-align: center;
        white-space: nowrap;
    }
    
    .compact-table td {
        padding: 6px 4px;
        text-align: center;
        border-bottom: 1px solid #eee;
        color: #444;
    }
    
    .status-badge {
        font-size: 10px;
        padding: 2px 6px;
        background-color: #f1cd86;
        color: #0a2d29;
        border-radius: 3px;
        display: inline-block;
        font-weight: 600;
    }
    
    .btn-compact {
        padding: 4px 8px;
        font-size: 11px;
    }
</style>

<!-- Job Information Section -->
<div class="details-card">
    <h2>Job Information</h2>
    <div class="info-grid">
        <div class="info-item"><strong>Month:</strong> {{ $trackerInfo->month->month ?? 'N/A' }}</div>
        <div class="info-item"><strong>Client:</strong> {{ $trackerInfo->client->client ?? 'N/A' }}</div>
        <div class="info-item">
            <strong>Location:</strong> 
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
        <div class="info-item"><strong>Status:</strong> {{ $trackerInfo->jobStatus->status ?? 'Demand Raised' }} ({{ $trackerInfo->jobStatus->status_initial ?? 'DR' }})</div>
        <div class="info-item"><strong>Type:</strong> {{ $trackerInfo->type_of_job ? ucfirst($trackerInfo->type_of_job) : 'N/A' }}</div>
        <div class="info-item"><strong>Position:</strong> {{ $trackerInfo->position ?? 'N/A' }}</div>
        <div class="info-item"><strong>Rate:</strong> {{ $trackerInfo->bill_rate_salary_range ?? 'N/A' }}</div>
        <div class="info-item"><strong>Priority:</strong> {{ $trackerInfo->priority ?? 'N/A' }}</div>
        <div class="info-item"><strong>Deadline:</strong> {{ $trackerInfo->submission_deadline ? $trackerInfo->submission_deadline->format('d-M-Y') : 'N/A' }}</div>
        <div class="info-item"><strong>PRD:</strong> {{ $trackerInfo->prd ? $trackerInfo->prd->format('d-M-Y') : 'N/A' }}</div>
        <div class="info-item"><strong>CF:</strong> {{ $trackerInfo->cf ?? 'N/A' }}</div>
        <div class="info-item"><strong>LR:</strong> {{ $trackerInfo->leadRecruiter ? $trackerInfo->leadRecruiter->username : 'N/A' }}</div>
        <div class="info-item"><strong>CSI:</strong> {{ $trackerInfo->csi ?? 'N/A' }}</div>
    </div>
</div>

<!-- Assign Candidates Section -->
<div class="details-card">
    <h2>Assign Candidates</h2>
    <form method="POST" action="{{ route('tracker.candidates.assign', $trackerInfo->id) }}" class="assign-form">
        @csrf
        <div class="form-group">
            <label for="candidate_id">Select Candidate</label>
            <select id="candidate_id" name="candidate_id" required>
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
        <div class="button-container">
            <button type="submit" class="btn btn-primary" style="height: 30px; display: flex; align-items: center; padding: 0 12px; font-size: 12px;">Assign</button>
            <button type="button" class="btn btn-success" onclick="openCreateCandidateModal()" style="height: 30px; display: flex; align-items: center; padding: 0 12px; font-size: 12px;">New Candidate</button>
        </div>
    </form>

    <!-- Assigned Candidates List -->
    @if($trackerInfo->trackerCandidates->count() > 0)
        <div style="margin-top: 15px;">
            <h3 style="color: #0a2d29; margin-bottom: 10px; font-size: 16px;">Assigned Candidates</h3>
            <div style="overflow-x: auto;">
                <table class="compact-table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Resume</th>
                            <th>Pipeline</th>
                            <th>Actions</th>
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
                                data-placement-date="{{ $status && $status->placement_completion_date ? $status->placement_completion_date->format('Y-m-d') : '' }}"
                                data-current-status-id="{{ $trackerCandidate->current_status_id }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $trackerCandidate->candidate->full_name }}</td>
                                <td>{{ $trackerCandidate->candidate->email }}</td>
                                <td>{{ $trackerCandidate->candidate->phone ?? 'N/A' }}</td>
                                <td>
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
                                <td>{{ $trackerCandidate->candidate->work_status ?? 'N/A' }}</td>
                                <td>
                                    @if($trackerCandidate->candidate->resume_file_url)
                                        <a href="{{ $trackerCandidate->candidate->resume_file_url }}" target="_blank" style="color: #f1cd86;">View</a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge" title="{{ $trackerCandidate->status->status ?? 'Candidate Identified' }}">
                                        {{ $trackerCandidate->status->status_initial ?? 'CI' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-warning btn-compact" onclick="openPipelineSummaryModal({{ $trackerCandidate->id }})" title="View Pipeline Summary" style="background-color: #f1cd86; border: none; color: #0a2d29; font-weight: 600;">Summary</button>
                                        <button type="button" class="btn btn-secondary btn-compact" onclick="openPipelineModal({{ $trackerCandidate->id }})">Pipeline</button>
                                        <form method="POST" action="{{ route('tracker.candidates.unassign', ['tracker_id' => $trackerInfo->id, 'tracker_candidate_id' => $trackerCandidate->id]) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this candidate from this job?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-compact">Remove</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <p style="color: #666; text-align: center; padding: 15px; font-size: 13px;">No candidates assigned yet.</p>
    @endif
</div>

<!-- Pipeline Status Modal -->
<div id="pipelineModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header" style="padding: 10px 15px;">
            <h2 id="pipelineModalTitle" style="font-size: 18px; margin: 0;">Candidate Pipeline Status</h2>
            <span class="close" onclick="closePipelineModal()">&times;</span>
        </div>
        <form id="pipelineForm" method="POST">
            @csrf
            @method('PUT')
            <div style="padding: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <!-- Current Status (Hidden but used for logic) -->
                <input type="hidden" id="current_status_id" name="current_status_id">

                <!-- Stage 2: Candidate Identified -> Next Step: Resume Review -->
                <div class="pipeline-stage section-status-2" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 1: Resume Review</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Resume Reviewed by Recruiter</label>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <select id="resume_reviewed_by_recruiter" name="resume_reviewed_by_recruiter" onchange="toggleDateInput('resume_reviewed_date', this.value)" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="Pending">Pending</option>
                                <option value="Completed">Completed</option>
                            </select>
                            <div id="resume_reviewed_date_container" style="flex: 1; display: none;">
                                <input type="date" id="resume_reviewed_date" name="resume_reviewed_date" style="width: 100%; padding: 7px; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stage 3: Resume Reviewed -> Next Step: Screening Call -->
                <div class="pipeline-stage section-status-3" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 2: Recruiter Screening Call</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Screening Call Status</label>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <select id="recruiter_screening_call" name="recruiter_screening_call" onchange="toggleDateInput('recruiter_screening_call_date', this.value)" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="Pending">Pending</option>
                                <option value="Completed">Completed</option>
                                <option value="No Show">No Show</option>
                            </select>
                            <div id="recruiter_screening_call_date_container" style="flex: 1; display: none;">
                                <input type="date" id="recruiter_screening_call_date" name="recruiter_screening_call_date" style="width: 100%; padding: 7px; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stage 4: Screening Call -> Next Step: Shortlisting -->
                <div class="pipeline-stage section-status-4" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 3: Candidate Shortlisting</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" id="candidate_shortlisted" name="candidate_shortlisted" value="1" style="width: 18px; height: 18px;">
                            Shortlist this candidate for client submission?
                        </label>
                    </div>
                </div>

                <!-- Stage 5: Shortlisted -> Next Step: Resume Submission -->
                <div class="pipeline-stage section-status-5" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 4: Resume Submission to Client</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Resume Submitted to Client</label>
                        <select id="resume_submitted_to_client" name="resume_submitted_to_client" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="Not Submitted">Not Submitted</option>
                            <option value="Submitted">Submitted</option>
                        </select>
                    </div>
                </div>

                <!-- Stage 6: Submitted -> Next Step: Internal Prep -->
                <div class="pipeline-stage section-status-6" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 5: RADiiX Internal Prep Interview</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Internal Prep Status</label>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <select id="radix_internal_interview_prep" name="radix_internal_interview_prep" onchange="toggleDateInput('radix_internal_interview_prep_date', this.value)" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="Not Required">Not Required</option>
                                <option value="Planned">Planned</option>
                                <option value="Completed">Completed</option>
                            </select>
                            <div id="radix_internal_interview_prep_date_container" style="flex: 1; display: none;">
                                <input type="date" id="radix_internal_interview_prep_date" name="radix_internal_interview_prep_date" style="width: 100%; padding: 7px; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stage 7: Internal Prep -> Next Step: Client Resume Review -->
                <div class="pipeline-stage section-status-7" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 6: Client Resume Review</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Client Review Outcome</label>
                        <select id="client_resume_review" name="client_resume_review" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="">Select Outcome</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                        <p style="margin-top: 8px; font-size: 11px; color: #d9534f;">* Note: Choosing 'Rejected' will automatically remove the candidate from this job.</p>
                    </div>
                </div>

                <!-- Stage 8: Resume Accepted -> Next Step: Client Interview 1 -->
                <div class="pipeline-stage section-status-8" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 7: Client Interview - Round 1</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Interview Round 1 Date</label>
                        <input type="date" id="client_interview_round_1_date" name="client_interview_round_1_date" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                </div>

                <!-- Stage 9: Interview 1 -> Next Step: Client Interview 2 -->
                <div class="pipeline-stage section-status-9" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 8: Client Interview - Round 2</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Interview Round 2 Date</label>
                        <input type="date" id="client_interview_round_2_date" name="client_interview_round_2_date" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                </div>

                <!-- Stage 10 & 11: Interview 2 / Additional Rounds -> Next Step: Additional Rounds -->
                <div class="pipeline-stage section-status-10 section-status-11" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 9: Additional Rounds</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Are there any additional rounds (Tech/Manager/Panel)?</label>
                        <select id="additional_rounds_select" name="additional_rounds_select" onchange="document.getElementById('additional_rounds').value = (this.value === 'Yes' ? '1' : '0'); togglePipelineSections(document.getElementById('current_status_id').value);" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                        <input type="hidden" id="additional_rounds" name="additional_rounds" value="0">
                    </div>
                </div>

                <!-- Stage 12: Client Decision -> Next Step: Client Confirmation -->
                <div class="pipeline-stage section-status-12" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 10: Client Decision</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Client Decision</label>
                        <select id="client_decision" name="client_decision" onchange="toggleDateInput('client_decision_date', this.value)" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="">Select Decision</option>
                            <option value="Selected">Selected</option>
                            <option value="Rejected">Rejected</option>
                            <option value="On Hold">On Hold</option>
                        </select>
                        <div id="client_decision_date_container" style="display: none; margin-top: 10px;">
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Decision Date</label>
                            <input type="date" id="client_decision_date" name="client_decision_date" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <p style="margin-top: 8px; font-size: 11px; color: #d9534f;">* Note: Choosing 'Rejected' will automatically remove the candidate from this job.</p>
                    </div>
                </div>

                <!-- Stage 13: Client Confirmation -> Next Step: Offer Extended -->
                <div class="pipeline-stage section-status-13" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 11: Client Confirmation</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; cursor: pointer; margin-bottom: 10px;">
                            <input type="checkbox" id="client_confirmation_received" name="client_confirmation_received" value="1" style="width: 18px; height: 18px;">
                            Client Confirmation Received?
                        </label>
                        <div id="client_confirmation_date_container">
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Confirmation Date</label>
                            <input type="date" id="client_confirmation_date" name="client_confirmation_date" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                    </div>
                </div>

                <!-- Stage 14: Offer Extended -> Next Step: Background Check -->
                <div class="pipeline-stage section-status-14" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 12: Offer Extended</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; cursor: pointer; margin-bottom: 10px;">
                            <input type="checkbox" id="offer_extended_to_candidate" name="offer_extended_to_candidate" value="1" style="width: 18px; height: 18px;">
                            Offer Extended to Candidate?
                        </label>
                        <div id="offer_extended_date_container">
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Offer Date</label>
                            <input type="date" id="offer_extended_date" name="offer_extended_date" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                    </div>
                </div>

                <!-- Stage 15: Background Check -> Next Step: Project Start -->
                <div class="pipeline-stage section-status-15" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 13: Background Check</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Background Check Status</label>
                        <select id="background_check" name="background_check" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="Pending">Pending</option>
                            <option value="Initiated">Initiated</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                </div>

                <!-- Stage 16: Project Start -> Next Step: Placement Completion -->
                <div class="pipeline-stage section-status-16" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 14: Project Start</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Candidate Project Start Date</label>
                        <input type="date" id="candidate_project_start_date" name="candidate_project_start_date" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                </div>

                <!-- Stage 17: Placement Completion -> Final -->
                <div class="pipeline-stage section-status-17 section-status-18" style="grid-column: span 2; display: none;">
                    <h3 style="color: #0a2d29; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Step 15: Placement Completion</h3>
                    <div style="background: #fdf6e3; padding: 15px; border-radius: 8px; border: 1px solid #f1cd86;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Final Status</label>
                        <select id="final_status_placement_completion" name="final_status_placement_completion" onchange="toggleDateInput('placement_completion_date', this.value)" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 10px;">
                            <option value="Confirmed">Confirmed</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                        <div id="placement_completion_date_container" style="display: none;">
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">Completion Date</label>
                            <input type="date" id="placement_completion_date" name="placement_completion_date" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer" style="padding: 15px 20px; display: flex; justify-content: flex-end; gap: 10px; background: #f9f9f9; border-radius: 0 0 8px 8px; border-top: 1px solid #f0f0f0;">
                <button type="button" class="btn btn-secondary btn-compact" onclick="closePipelineModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-compact">Save Status</button>
            </div>
        </form>
    </div>
</div>

<!-- Pipeline Summary Modal -->
<div id="pipelineSummaryModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header" style="padding: 10px 15px; background: #0a2d29;">
            <h2 style="font-size: 18px; margin: 0; color: white;">Pipeline Journey: <span id="summaryCandidateName"></span></h2>
            <span class="close" onclick="closePipelineSummaryModal()">&times;</span>
        </div>
        <div style="padding: 25px;">
            <div id="pipelineTimeline" class="timeline">
                <!-- Timeline items will be injected here -->
            </div>
        </div>
        <div class="modal-footer" style="padding: 15px 20px; display: flex; justify-content: flex-end; background: #f9f9f9; border-top: 1px solid #f0f0f0;">
            <button type="button" class="btn btn-secondary btn-compact" onclick="closePipelineSummaryModal()">Close</button>
        </div>
    </div>
</div>

@include('tracker._modals')

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0);
        backdrop-filter: blur(0px);
        transition: all 0.3s ease;
    }

    .modal.active {
        display: block;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }

    .modal-content {
        background-color: white;
        margin: 3% auto;
        padding: 0;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        transform: translateY(-20px);
        transition: all 0.3s ease;
        opacity: 0;
    }

    .modal.active .modal-content {
        transform: translateY(0);
        opacity: 1;
    }

    .modal-header {
        padding: 15px 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #0a2d29;
        color: white;
        border-radius: 8px 8px 0 0;
    }

    .modal-header h2 {
        margin: 0;
        color: white;
        font-size: 20px;
        font-weight: 600;
    }

    .close {
        color: white;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    .close:hover {
        opacity: 1;
    }

    /* Timeline Styles */
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 7px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 25px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -30px;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #0a2d29;
        z-index: 1;
    }
    .timeline-item.completed::before {
        background: #f1cd86;
        border-color: #0a2d29;
    }
    .timeline-item.active::before {
        background: #0a2d29;
        box-shadow: 0 0 0 4px rgba(10, 45, 41, 0.2);
    }
    .timeline-content {
        background: #f9f9f9;
        padding: 12px 15px;
        border-radius: 8px;
        border-left: 4px solid #ddd;
    }
    .timeline-item.completed .timeline-content {
        border-left-color: #f1cd86;
        background: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .timeline-item.active .timeline-content {
        border-left-color: #0a2d29;
        background: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .timeline-title {
        font-weight: 700;
        font-size: 14px;
        color: #0a2d29;
        margin-bottom: 4px;
        display: flex;
        justify-content: space-between;
    }
    .timeline-date {
        font-size: 11px;
        color: #888;
        font-weight: normal;
    }
    .timeline-desc {
        font-size: 13px;
        color: #555;
    }
    .btn-info {
        background-color: #17a2b8;
        color: white;
    }
    .btn-info:hover {
        background-color: #138496;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 for candidate selection
        if (typeof jQuery !== 'undefined' && $('#candidate_id').length > 0) {
            $('#candidate_id').select2({
                placeholder: 'Select or search for a candidate',
                allowClear: true,
                width: '100%'
            });
            
            // Fix for Select2 styling to match the theme
            $('.select2-container .select2-selection--single').css({
                'height': '30px',
                'border': '1px solid #ddd',
                'display': 'flex',
                'align-items': 'center'
            });
            
            $('.select2-container--default .select2-selection--single .select2-selection__arrow').css({
                'height': '28px'
            });
        }
    });

    let currentTrackerCandidateId = null;

    function toggleDateInput(dateInputId, value) {
        const container = document.getElementById(dateInputId + '_container');
        if (container) {
            // Show date input if value is not empty, not 'Pending', and not 'Not Required'
            if (value && value !== '' && value !== 'Pending' && value !== 'Not Required') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }
    }

    function togglePipelineSections(currentStatusId) {
        // Hide all stages first
        document.querySelectorAll('.pipeline-stage').forEach(stage => {
            stage.style.display = 'none';
        });

        // Show the relevant stage based on current_status_id
        // Stage 1 (Demand Raised) -> Not applicable for candidate
        // Stage 2 (Candidate Identified) -> Show Step 1 (Resume Review)
        // Stage 3 (Resume Reviewed) -> Show Step 2 (Screening Call)
        // Stage 4 (Screening Call) -> Show Step 3 (Shortlisting)
        // Stage 5 (Shortlisted) -> Show Step 4 (Resume Submission)
        // Stage 6 (Submitted) -> Show Step 5 (Internal Prep)
        // Stage 7 (Internal Prep) -> Show Step 6 (Client Review)
        // Stage 8 (Resume Accepted) -> Show Step 7 (Interview 1)
        // Stage 9 (Interview 1) -> Show Step 8 (Interview 2)
        // Stage 10 (Interview 2) -> Show Step 9 (Additional Rounds)
        // Stage 11 (Additional Rounds) -> Show Step 9 (Additional Rounds)
        // Stage 12 (Client Decision) -> Show Step 10 (Client Decision)
        
        let statusId = parseInt(currentStatusId);
        
        // Fallback for status 1 (Demand Raised) to show first candidate step
        if (statusId === 1) statusId = 2;
        
        const section = document.querySelector('.section-status-' + statusId);
        if (section) {
            section.style.display = 'block';
        } else if (statusId >= 13) {
            // For later stages, show the generic "Advanced" section
            const advancedSection = document.querySelector('.section-status-13');
            if (advancedSection) advancedSection.style.display = 'block';
        }
    }

    function openCreateCandidateModal() {
        const modal = document.getElementById('createCandidateModal');
        modal.classList.add('active');
        modal.style.display = 'block';
    }

    function closeCreateCandidateModal() {
        const modal = document.getElementById('createCandidateModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.getElementById('createCandidateForm').reset();
        }, 300);
    }

    function openPipelineModal(trackerCandidateId) {
        currentTrackerCandidateId = trackerCandidateId;
        const modal = document.getElementById('pipelineModal');
        const form = document.getElementById('pipelineForm');
        const row = document.querySelector('tr[data-tracker-candidate-id="' + trackerCandidateId + '"]');
        
        form.action = '/tracker/info/{{ $trackerInfo->id }}/candidates/' + trackerCandidateId + '/pipeline';
        
        if (row) {
            const currentStatusId = row.dataset.currentStatusId || 2;
            document.getElementById('current_status_id').value = currentStatusId;
            
            // Populate fields
            document.getElementById('resume_reviewed_by_recruiter').value = row.dataset.resumeReviewed || 'Pending';
            document.getElementById('resume_reviewed_date').value = row.dataset.resumeReviewedDate || '';
            document.getElementById('recruiter_screening_call').value = row.dataset.recruiterScreening || 'Pending';
            document.getElementById('recruiter_screening_call_date').value = row.dataset.recruiterScreeningDate || '';
            document.getElementById('candidate_shortlisted').checked = row.dataset.shortlisted === '1';
            document.getElementById('resume_submitted_to_client').value = row.dataset.resumeSubmitted || 'Not Submitted';
            document.getElementById('radix_internal_interview_prep').value = row.dataset.radixPrep || 'Not Required';
            document.getElementById('radix_internal_interview_prep_date').value = row.dataset.radixPrepDate || '';
            document.getElementById('client_resume_review').value = row.dataset.clientReview || '';
            document.getElementById('client_interview_round_1_date').value = row.dataset['interviewRound-1'] || '';
            document.getElementById('client_interview_round_2_date').value = row.dataset['interviewRound-2'] || '';
            document.getElementById('additional_rounds_select').value = row.dataset.additionalRounds === '1' ? 'Yes' : 'No';
            document.getElementById('client_decision').value = row.dataset.clientDecision || '';
            document.getElementById('client_decision_date').value = row.dataset.clientDecisionDate || '';
            document.getElementById('client_confirmation_received').checked = row.dataset.confirmationReceived === '1';
            document.getElementById('client_confirmation_date').value = row.dataset.confirmationDate || '';
            document.getElementById('offer_extended_to_candidate').checked = row.dataset.offerExtended === '1';
            document.getElementById('offer_extended_date').value = row.dataset.offerExtendedDate || '';
            document.getElementById('background_check').value = row.dataset.backgroundCheck || 'Pending';
            document.getElementById('candidate_project_start_date').value = row.dataset.projectStart || '';
            document.getElementById('final_status_placement_completion').value = row.dataset.finalStatus || 'Confirmed';
            document.getElementById('placement_completion_date').value = row.dataset.placementDate || '';
            
            // Handle date visibility
            toggleDateInput('resume_reviewed_date', row.dataset.resumeReviewed);
            toggleDateInput('recruiter_screening_call_date', row.dataset.recruiterScreening);
            toggleDateInput('radix_internal_interview_prep_date', row.dataset.radixPrep);
            toggleDateInput('client_decision_date', row.dataset.clientDecision);
            toggleDateInput('placement_completion_date', row.dataset.finalStatus);
            
            // Show correct section
            togglePipelineSections(currentStatusId);
            
            modal.classList.add('active');
            modal.style.display = 'block';
        }
    }

    function closePipelineModal() {
        const modal = document.getElementById('pipelineModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.getElementById('pipelineForm').reset();
        }, 300);
        currentTrackerCandidateId = null;
    }

    function openPipelineSummaryModal(trackerCandidateId) {
        const row = document.querySelector('tr[data-tracker-candidate-id="' + trackerCandidateId + '"]');
        if (!row) return;

        document.getElementById('summaryCandidateName').textContent = row.cells[1].textContent;
        const timeline = document.getElementById('pipelineTimeline');
        timeline.innerHTML = '';

        const stages = [
            { id: 2, title: 'Candidate Identified', date: null, desc: 'Candidate added to the tracker.' },
            { id: 3, title: 'Resume Reviewed', date: row.dataset.resumeReviewedDate, desc: row.dataset.resumeReviewed === 'Completed' ? 'Resume reviewed by recruiter.' : 'Pending review.' },
            { id: 4, title: 'Screening Call', date: row.dataset.recruiterScreeningDate, desc: row.dataset.recruiterScreening === 'Completed' ? 'Screening call completed.' : 'Pending screening.' },
            { id: 5, title: 'Candidate Shortlisted', date: null, desc: row.dataset.shortlisted === '1' ? 'Candidate shortlisted for submission.' : 'Not yet shortlisted.' },
            { id: 6, title: 'Resume Submitted', date: null, desc: row.dataset.resumeSubmitted === 'Submitted' ? 'Resume submitted to client.' : 'Not yet submitted.' },
            { id: 7, title: 'Internal Prep', date: row.dataset.radixPrepDate, desc: row.dataset.radixPrep === 'Completed' ? 'Internal interview prep completed.' : 'Prep planned or not required.' },
            { id: 8, title: 'Client Review', date: null, desc: row.dataset.clientReview === 'Approved' ? 'Client approved the resume.' : (row.dataset.clientReview === 'Rejected' ? 'Client rejected the resume.' : 'Pending client review.') },
            { id: 9, title: 'Interview Round 1', date: row.dataset['interviewRound-1'], desc: row.dataset['interviewRound-1'] ? 'First round interview scheduled/completed.' : 'Not yet scheduled.' },
            { id: 10, title: 'Interview Round 2', date: row.dataset['interviewRound-2'], desc: row.dataset['interviewRound-2'] ? 'Second round interview scheduled/completed.' : 'Not yet scheduled.' },
            { id: 12, title: 'Client Decision', date: row.dataset.clientDecisionDate, desc: row.dataset.clientDecision ? `Client Decision: ${row.dataset.clientDecision}` : 'Awaiting client decision.' },
            { id: 13, title: 'Client Confirmation', date: row.dataset.confirmationDate, desc: row.dataset.confirmationReceived === '1' ? 'Client confirmation received.' : 'Awaiting confirmation.' },
            { id: 14, title: 'Offer Extended', date: row.dataset.offerExtendedDate, desc: row.dataset.offerExtended === '1' ? 'Offer extended to candidate.' : 'Awaiting offer extension.' },
            { id: 15, title: 'Background Check', date: null, desc: row.dataset.backgroundCheck ? `Status: ${row.dataset.backgroundCheck}` : 'Not started.' },
            { id: 16, title: 'Project Start', date: row.dataset.projectStart, desc: row.dataset.projectStart ? 'Candidate project start date set.' : 'Awaiting start date.' },
            { id: 17, title: 'Placement Confirmed', date: row.dataset.placementDate, desc: row.dataset.finalStatus === 'Confirmed' ? 'Placement successfully completed!' : 'Awaiting final confirmation.' },
            { id: 18, title: 'Placement Rejected', date: row.dataset.placementDate, desc: 'Placement was not successful.' }
        ];

        const currentStatusId = parseInt(row.dataset.currentStatusId || 2);

        stages.forEach(stage => {
            const item = document.createElement('div');
            item.className = 'timeline-item';
            if (stage.id < currentStatusId) item.classList.add('completed');
            if (stage.id === currentStatusId) item.classList.add('active');

            const dateStr = stage.date ? new Date(stage.date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '';

            item.innerHTML = `
                <div class="timeline-content">
                    <div class="timeline-title">
                        ${stage.title}
                        <span class="timeline-date">${dateStr}</span>
                    </div>
                    <div class="timeline-desc">${stage.desc}</div>
                </div>
            `;
            timeline.appendChild(item);
        });

        const modal = document.getElementById('pipelineSummaryModal');
        modal.classList.add('active');
        modal.style.display = 'block';
    }

    function closePipelineSummaryModal() {
        const modal = document.getElementById('pipelineSummaryModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    window.onclick = function(event) {
        const pipelineModal = document.getElementById('pipelineModal');
        const summaryModal = document.getElementById('pipelineSummaryModal');
        const createModal = document.getElementById('createCandidateModal');
        
        if (event.target == pipelineModal) closePipelineModal();
        if (event.target == summaryModal) closePipelineSummaryModal();
        if (event.target == createModal) closeCreateCandidateModal();
    }
</script>
@endsection

