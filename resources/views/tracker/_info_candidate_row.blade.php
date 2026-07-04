@php
    $status = $status ?? $tc->pipelineStatus;
    $hasResume = $hasResume ?? (bool) $tc->candidate->resume_file_url;
    $checklistProgress = $checklistProgress ?? $pipelineService->checklistProgress(
        $pipelineService->checklistItems($status, $hasResume)
    );
    $isApproved = $isApproved ?? false;
    $isSubmitted = $isSubmitted ?? false;
    $isRejected = $isRejected ?? false;
    $canMarkApproved = !$isRejected && !$isApproved && !$isSubmitted && $checklistProgress === 100;
    $showApprovedWorkflow = $isApproved || $isSubmitted;
    $approvedStage = $tc->approved_stage ?? \App\Models\TrackerCandidate::APPROVED_STAGE_IN_PROGRESS;
    $stageId = $tc->current_status_id ?? 2;
    $stageColor = $stageColors[$stageId] ?? '#6B7280';
    $rowClass = $isRejected ? 'row-rejected' : ($showApprovedWorkflow ? 'row-approved' : '');
    $popoverId = 'ap-' . $tc->id;
@endphp
<tr class="{{ $rowClass }}"
    data-tracker-candidate-id="{{ $tc->id }}"
    data-rejected="{{ $isRejected ? '1' : '0' }}"
    data-approved="{{ $showApprovedWorkflow ? '1' : '0' }}"
    data-approved-stage="{{ $showApprovedWorkflow ? $approvedStage : '' }}"
    data-rejection-reason="{{ $isRejected ? ($tc->rejection_reason ?? '') : '' }}"
    data-rejected-at="{{ $isRejected && $tc->rejected_at ? $tc->rejected_at->format('Y-m-d') : '' }}"
    data-candidate-identified="{{ $status && $status->candidate_identified ? '1' : '0' }}"
    data-requirement-reviewed="{{ $status && $status->requirement_reviewed ? '1' : '0' }}"
    data-doc-govt-id="{{ $status && $status->doc_govt_id_collected ? '1' : '0' }}"
    data-doc-resume-collected="{{ $status && $status->doc_resume_collected ? '1' : '0' }}"
    data-doc-work-auth="{{ $status && $status->doc_work_auth_collected ? '1' : '0' }}"
    data-doc-linkedin="{{ $status && $status->doc_linkedin_collected ? '1' : '0' }}"
    data-rtr-signed="{{ $status && $status->rtr_signed ? '1' : '0' }}"
    data-has-resume="{{ $hasResume ? '1' : '0' }}"
    data-checklist-progress="{{ $checklistProgress }}"
    data-stage-label="{{ $showApprovedWorkflow ? $tc->approvedStageLabel() : ($tc->status->status ?? 'Identified') }}"
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
    data-current-status-id="{{ $tc->current_status_id }}"
    data-candidate-name="{{ $tc->candidate->full_name }}"
    >
    <td style="color:var(--c-muted);font-size:11px;">{{ $index }}</td>
    <td>
        <div class="candidate-name">{{ $tc->candidate->full_name }}</div>
        <div class="candidate-email">{{ $tc->candidate->email }}</div>
        @if($showApprovedWorkflow)
            <div class="candidate-approved-meta">Approved {{ $tc->approved_at?->format('d M Y') ?? '—' }}</div>
        @endif
    </td>
    <td class="hide-mobile" style="color:var(--c-muted);font-size:12px;">{{ $tc->candidate->phone ?? '—' }}</td>
    <td class="hide-mobile" style="font-size:12px;">
        @if($tc->candidate->location)
            {{ $tc->candidate->location->city ? $tc->candidate->location->city . ', ' : '' }}{{ $tc->candidate->location->region }}
        @else
            <span style="color:var(--c-muted);">—</span>
        @endif
    </td>
    <td class="hide-mobile">
        @if($tc->candidate->work_status)
            <span class="pill pill-primary">{{ $tc->candidate->work_status }}</span>
        @else
            <span style="color:var(--c-muted);">—</span>
        @endif
    </td>
    <td>
        @if($showApprovedWorkflow)
            <span class="stage-badge stage-badge-approved">
                <span class="dot" style="background:#EAB308;"></span>
                {{ $tc->approvedStageLabel() }}
            </span>
        @else
            <span class="stage-badge" @if($isRejected) style="background:#FEE2E2;color:#991B1B;border-color:#FECACA;" @endif>
                <span class="dot" style="background:{{ $isRejected ? '#EF4444' : $stageColor }};"></span>
                {{ $tc->status->status ?? ($isRejected ? 'Rejected' : 'Identified') }}
            </span>
        @endif
    </td>
    <td>
        @if($tc->candidate->resume_file_url)
            <a href="{{ $tc->candidate->resume_file_url }}" target="_blank" class="btn-i btn-sm-i btn-accent-i" style="text-decoration:none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                CV
            </a>
        @else
            <span style="color:var(--c-muted);font-size:11px;">N/A</span>
        @endif
    </td>
    <td class="actions-cell">
        <div class="action-group actions-wrap">
            @if($isRejected)
                <button type="button" class="btn-i btn-sm-i btn-primary-i btn-revert" onclick="openRevertModal({{ $tc->id }}, '{{ addslashes($tc->candidate->full_name) }}')">Revert</button>
                <button type="button" class="btn-i btn-sm-i btn-secondary-i" onclick="openCandidateDrawer({{ $tc->id }})">View</button>
                <a href="{{ route('candidates.edit', $tc->candidate_id) }}" class="btn-i btn-sm-i btn-ghost-i" title="View Profile">Profile</a>
            @elseif($showApprovedWorkflow)
                <button type="button" class="btn-i btn-sm-i btn-primary-i" onclick="openApprovedStageModal({{ $tc->id }}, '{{ addslashes($tc->candidate->full_name) }}', '{{ $approvedStage }}')">Edit</button>
                <button type="button" class="btn-i btn-sm-i btn-secondary-i" onclick="openCandidateDrawer({{ $tc->id }})">View</button>
                <a href="{{ route('tracker.candidates.report.form', ['tracker_id' => $trackerInfo->id, 'tracker_candidate_id' => $tc->id]) }}" class="btn-i btn-sm-i btn-accent-i" style="text-decoration:none;">Report</a>
            @else
                <div id="{{ $popoverId }}" class="actions-popover" data-tc-id="{{ $tc->id }}" data-name="{{ $tc->candidate->full_name }}">
                    <button type="button" class="ap-item" onclick="closeAllPopovers(); openCandidateDrawer({{ $tc->id }})">Manage</button>
                    <a href="{{ route('tracker.candidates.report.form', ['tracker_id' => $trackerInfo->id, 'tracker_candidate_id' => $tc->id]) }}" class="ap-item">Report</a>
                    @if($status && $status->candidate_identified)
                        @if($recruiterEmail ?? null)
                            <button type="button" class="ap-item ap-item-mail"
                                data-tc-id="{{ $tc->id }}"
                                data-candidate-name="{{ $tc->candidate->full_name }}"
                                data-candidate-email="{{ $tc->candidate->email }}"
                                onclick="closeAllPopovers(); openMailComposer(Number(this.dataset.tcId), this.dataset.candidateName, this.dataset.candidateEmail)">Mail</button>
                        @else
                            <span class="ap-item ap-item-disabled" title="Set your official @rinfinite.com email in your user profile to send mail.">Mail</span>
                        @endif
                    @endif
                    @if($canMarkApproved)
                        <form method="POST" action="{{ route('tracker.candidates.approve', ['tracker_id' => $trackerInfo->id, 'tracker_candidate_id' => $tc->id]) }}" class="ap-form" onsubmit="return confirm('Mark {{ addslashes($tc->candidate->full_name) }} as approved?')">
                            @csrf
                            <button type="submit" class="ap-item ap-item-gold">Mark approved</button>
                        </form>
                    @else
                        <span class="ap-item ap-item-disabled" title="Complete checklist ({{ $checklistProgress }}%)">Mark approved</span>
                    @endif
                    <button type="button" class="ap-item ap-item-danger" onclick="closeAllPopovers(); openRejectModal({{ $tc->id }}, '{{ addslashes($tc->candidate->full_name) }}')">Reject</button>
                </div>
                <button type="button" class="btn-i btn-sm-i btn-ghost-i actions-trigger" onclick="toggleActionsPopover('{{ $popoverId }}', event)" title="Actions">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    Actions
                </button>
            @endif
        </div>
    </td>
</tr>
