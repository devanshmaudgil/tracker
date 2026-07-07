{{-- Candidate management slide-over drawer (Checklist | Pipeline | Journey) --}}
@php
    $jobStatusMap = ($jobStatuses ?? collect())->keyBy('id');
    $statusLabel = fn (int $id, string $fallback = '') => $jobStatusMap->get($id)?->status ?? $fallback;
@endphp
<style>
    /* ── Overlay & panel: always mounted, animated via visibility/opacity/transform ── */
    .drawer-overlay {
        position: fixed; inset: 0; z-index: 1100;
        background: rgba(10, 45, 41, 0);
        backdrop-filter: blur(0);
        -webkit-backdrop-filter: blur(0);
        visibility: hidden;
        opacity: 0;
        pointer-events: none;
        transition:
            opacity 0.38s cubic-bezier(0.4, 0, 0.2, 1),
            background 0.38s cubic-bezier(0.4, 0, 0.2, 1),
            backdrop-filter 0.38s cubic-bezier(0.4, 0, 0.2, 1),
            visibility 0s linear 0.38s;
    }
    .drawer-overlay.is-open {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
        background: rgba(10, 45, 41, 0.42);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        transition:
            opacity 0.38s cubic-bezier(0.4, 0, 0.2, 1),
            background 0.38s cubic-bezier(0.4, 0, 0.2, 1),
            backdrop-filter 0.38s cubic-bezier(0.4, 0, 0.2, 1),
            visibility 0s;
    }

    .drawer-panel {
        position: fixed; top: 0; right: 0; bottom: 0; z-index: 1101;
        width: min(520px, 100vw); background: #fff;
        box-shadow: -12px 0 40px rgba(0, 0, 0, 0.14);
        transform: translateX(105%);
        transition: transform 0.42s cubic-bezier(0.32, 0.72, 0, 1);
        display: flex; flex-direction: column;
        will-change: transform;
        contain: layout style;
    }
    .drawer-overlay.is-open .drawer-panel {
        transform: translateX(0);
    }

    body.drawer-open {
        overflow: hidden;
        touch-action: none;
    }

    .drawer-head {
        padding: 18px 20px; background: linear-gradient(135deg, var(--c-primary) 0%, #0d3d38 100%);
        color: #fff; flex-shrink: 0;
    }
    .drawer-head-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
    .drawer-head h3 {
        margin: 0; font-size: 16px; font-weight: 800; line-height: 1.3;
        transition: opacity 0.2s ease;
    }
    .drawer-head .drawer-sub { font-size: 12px; opacity: .75; margin-top: 4px; }
    .drawer-candidate-meta {
        margin-top: 10px; padding: 10px 12px; border-radius: 8px;
        background: rgba(255,255,255,.1); font-size: 11px; line-height: 1.45;
        display: none;
    }
    .drawer-candidate-meta.is-visible { display: block; }
    .drawer-candidate-meta strong { display: block; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; opacity: .7; margin-bottom: 2px; }
    .drawer-notes-block {
        margin-top: 14px; padding: 12px; border: 1px solid var(--c-border);
        border-radius: 8px; background: #f9fafb;
    }
    .drawer-notes-block label { display: block; font-size: 11px; font-weight: 700; color: var(--c-primary); margin-bottom: 6px; }
    .drawer-notes-block textarea {
        width: 100%; min-height: 72px; padding: 8px 10px; border: 1px solid var(--c-border);
        border-radius: 6px; font-size: 12px; resize: vertical; font-family: inherit;
    }
    .drawer-close {
        background: rgba(255,255,255,.15); border: none; color: #fff;
        width: 32px; height: 32px; border-radius: 8px; cursor: pointer; font-size: 20px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        transition: background 0.2s ease, transform 0.15s ease;
    }
    .drawer-close:hover { background: rgba(255,255,255,.28); }
    .drawer-close:active { transform: scale(0.94); }

    .drawer-progress { margin-top: 14px; display: flex; align-items: center; gap: 12px; }
    .drawer-ring {
        width: 44px; height: 44px; border-radius: 50%;
        background: conic-gradient(var(--c-accent) var(--pct, 0%), rgba(255,255,255,.2) 0);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        transition: background 0.55s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .drawer-ring-inner {
        width: 34px; height: 34px; border-radius: 50%;
        background: #0d3d38; display: flex; align-items: center; justify-content: center;
        font-size: 10px; font-weight: 800;
    }
    .drawer-progress-meta { flex: 1; min-width: 0; }
    .drawer-progress-meta strong {
        display: block; font-size: 12px; font-weight: 700;
        transition: opacity 0.25s ease;
    }
    .drawer-progress-meta span { font-size: 11px; opacity: .7; }

    .drawer-tabs {
        display: flex; border-bottom: 1px solid var(--c-border);
        background: #f9fafb; flex-shrink: 0; position: relative;
    }
    .drawer-tab {
        flex: 1; padding: 11px 8px; border: none; background: transparent;
        font-size: 12px; font-weight: 700; color: var(--c-muted); cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: color 0.22s ease, background 0.22s ease;
        position: relative; z-index: 1;
    }
    .drawer-tab:hover { color: var(--c-primary); }
    .drawer-tab.active { color: var(--c-primary); border-bottom-color: var(--c-accent); background: #fff; }

    .drawer-body {
        flex: 1; overflow-y: auto; overflow-x: hidden;
        padding: 16px 18px 24px;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }

    /* Stacked panes — crossfade instead of display toggle */
    .drawer-pane-stack {
        display: grid;
        min-height: 120px;
    }
    .drawer-pane {
        grid-area: 1 / 1;
        z-index: 0;
        opacity: 0;
        visibility: hidden;
        transform: translateY(8px);
        pointer-events: none;
        transition:
            opacity 0.28s cubic-bezier(0.4, 0, 0.2, 1),
            transform 0.28s cubic-bezier(0.4, 0, 0.2, 1),
            visibility 0.28s;
    }
    .drawer-pane.active {
        z-index: 2;
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: auto;
    }
    .drawer-pane:not(.active) * {
        pointer-events: none;
    }

    /* Checklist */
    .chk-list { display: flex; flex-direction: column; gap: 8px; }
    .chk-rejection-banner {
        margin-bottom: 14px; padding: 12px 14px;
        background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px;
    }
    .chk-rejection-banner strong {
        display: block; font-size: 12px; font-weight: 800; color: #991B1B; margin-bottom: 6px;
    }
    .chk-rejection-banner p {
        margin: 0; font-size: 12px; line-height: 1.5; color: #7F1D1D; font-style: italic;
    }
    .chk-rejection-banner span {
        display: block; margin-top: 6px; font-size: 10px; font-weight: 600; color: #B91C1C;
    }
    .chk-card {
        border: 1px solid var(--c-border); border-radius: 10px;
        background: #fff; overflow: hidden;
        transition: border-color 0.25s ease, background 0.25s ease, opacity 0.2s ease;
        animation: chkSlideIn 0.38s cubic-bezier(0.32, 0.72, 0, 1) backwards;
    }
    .chk-card.done { background: #f0fdf4; border-color: #bbf7d0; }
    .chk-card.is-saving { opacity: 0.55; pointer-events: none; }
    .chk-card-head {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 10px 12px;
    }
    .chk-card-head input[type="checkbox"] {
        width: 17px; height: 17px; margin-top: 1px; flex-shrink: 0; cursor: pointer;
        accent-color: var(--c-primary);
    }
    .chk-card-head input:disabled { cursor: default; }
    .chk-card-label { font-size: 12.5px; font-weight: 600; color: var(--c-text); line-height: 1.45; flex: 1; }
    .chk-card.done .chk-card-label { color: #065f46; }
    .chk-card-body {
        padding: 10px 12px 12px;
        border-top: 1px solid var(--c-border);
        background: #f9fafb;
    }
    .chk-card.done .chk-card-body { border-top-color: #d1fae5; background: #ecfdf5; }
    .chk-pipe-title {
        font-size: 10px; font-weight: 800; text-transform: uppercase;
        letter-spacing: .05em; color: var(--c-muted); margin: 0 0 8px;
    }
    .chk-pipe-fields { display: flex; flex-direction: column; gap: 8px; }
    .chk-pipe-fields .form-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .chk-pipe-fields .form-field { flex: 1; min-width: 120px; margin: 0; }
    .chk-pipe-fields label { font-size: 10px; font-weight: 700; color: var(--c-muted); display: block; margin-bottom: 4px; }
    .chk-pipe-fields select,
    .chk-pipe-fields input[type="date"] {
        width: 100%; padding: 6px 8px; font-size: 12px;
        border: 1px solid var(--c-border); border-radius: 6px; background: #fff;
    }
    .chk-item {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 10px 12px; border: 1px solid var(--c-border); border-radius: 8px;
        background: #fff;
        transition: border-color 0.25s ease, background 0.25s ease, transform 0.2s ease, opacity 0.2s ease;
        animation: chkSlideIn 0.38s cubic-bezier(0.32, 0.72, 0, 1) backwards;
    }
    @keyframes chkSlideIn {
        from { opacity: 0; transform: translateX(12px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    .chk-item.done { background: #f0fdf4; border-color: #bbf7d0; }
    .chk-item.readonly { opacity: .85; cursor: default; }
    .chk-item.readonly input[type="checkbox"] { cursor: default; }
    .chk-item.is-saving { opacity: 0.55; pointer-events: none; }
    .chk-item input[type="checkbox"] {
        width: 17px; height: 17px; margin-top: 1px; flex-shrink: 0; cursor: pointer;
        accent-color: var(--c-primary);
        transition: transform 0.15s ease;
    }
    .chk-item input:active:not(:disabled) { transform: scale(0.9); }
    .chk-item input:disabled { cursor: default; }
    .chk-item-label { font-size: 12.5px; font-weight: 600; color: var(--c-text); line-height: 1.45; }
    .chk-item.done .chk-item-label { color: #065f46; }
    .chk-section-title {
        font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
        color: var(--c-muted); margin: 14px 0 8px;
        animation: chkSlideIn 0.32s cubic-bezier(0.32, 0.72, 0, 1) backwards;
    }
    .chk-section-title:first-child { margin-top: 0; }

    /* Pipeline stepper */
    .pipe-stepper { display: flex; flex-direction: column; gap: 0; }
    .pipe-step {
        position: relative; padding-left: 28px; padding-bottom: 18px;
        transition: opacity 0.2s ease;
    }
    .pipe-step:last-child { padding-bottom: 0; }
    .pipe-step::before {
        content: ''; position: absolute; left: 8px; top: 22px; bottom: 0;
        width: 2px; background: var(--c-border);
        transition: background 0.3s ease;
    }
    .pipe-step:last-child::before { display: none; }
    .pipe-step-dot {
        position: absolute; left: 0; top: 4px; width: 18px; height: 18px;
        border-radius: 50%; background: #fff; border: 2px solid var(--c-border);
        z-index: 1;
        transition: background 0.3s ease, border-color 0.3s ease, transform 0.3s ease;
    }
    .pipe-step.done .pipe-step-dot { background: var(--c-accent); border-color: var(--c-primary); }
    .pipe-step-head {
        font-size: 12px; font-weight: 800; color: var(--c-primary); margin-bottom: 8px;
    }
    .pipe-step-body {
        background: #f9fafb; border: 1px solid var(--c-border); border-radius: 8px; padding: 10px 12px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .pipe-step-body:focus-within {
        border-color: rgba(10, 45, 41, 0.25);
        box-shadow: 0 0 0 3px rgba(10, 45, 41, 0.06);
    }
    .pipe-step-body .form-field { margin-bottom: 10px; }
    .pipe-step-body .form-field:last-child { margin-bottom: 0; }
    .pipe-step-body label { font-size: 11px; }
    .pipe-step-body select, .pipe-step-body input[type="date"] {
        padding: 6px 8px; font-size: 12px;
        transition: border-color 0.15s ease;
    }

    .drawer-foot {
        flex-shrink: 0;
        display: flex; justify-content: flex-end; gap: 8px;
        max-height: 0; opacity: 0; overflow: hidden;
        padding: 0 18px;
        border-top: 1px solid transparent;
        background: #f9fafb;
        transition:
            max-height 0.32s cubic-bezier(0.4, 0, 0.2, 1),
            opacity 0.28s ease,
            padding 0.32s cubic-bezier(0.4, 0, 0.2, 1),
            border-color 0.28s ease;
    }
    .drawer-foot.visible {
        max-height: 72px;
        opacity: 1;
        padding: 12px 18px;
        border-top-color: var(--c-border);
    }

    .drawer-timeline .tl-item {
        margin-bottom: 12px;
        animation: chkSlideIn 0.35s cubic-bezier(0.32, 0.72, 0, 1) backwards;
    }

    .drawer-loading {
        display: flex; align-items: center; justify-content: center;
        padding: 48px 20px; color: var(--c-muted); font-size: 12px; gap: 10px;
    }
    .drawer-loading-spinner {
        width: 18px; height: 18px; border: 2px solid var(--c-border);
        border-top-color: var(--c-primary); border-radius: 50%;
        animation: drawerSpin 0.65s linear infinite;
    }
    @keyframes drawerSpin { to { transform: rotate(360deg); } }

    /* Cascade confirm dialog */
    .chk-confirm-overlay {
        position: fixed; inset: 0; z-index: 1200;
        background: rgba(10, 45, 41, 0.55);
        display: flex; align-items: center; justify-content: center;
        padding: 20px;
        opacity: 0; visibility: hidden; pointer-events: none;
        transition: opacity 0.22s ease, visibility 0.22s;
    }
    .chk-confirm-overlay.is-open {
        opacity: 1; visibility: visible; pointer-events: auto;
    }
    .chk-confirm-box {
        background: #fff; border-radius: 12px; width: min(400px, 100%);
        box-shadow: 0 20px 50px rgba(0,0,0,.18); overflow: hidden;
        transform: scale(0.96); transition: transform 0.22s cubic-bezier(0.32, 0.72, 0, 1);
    }
    .chk-confirm-overlay.is-open .chk-confirm-box { transform: scale(1); }
    .chk-confirm-head {
        padding: 16px 18px; background: #fef3c7; border-bottom: 1px solid #fde68a;
        font-size: 13px; font-weight: 800; color: #92400e;
    }
    .chk-confirm-body { padding: 16px 18px; font-size: 12.5px; line-height: 1.55; color: var(--c-text); }
    .chk-confirm-body ul { margin: 10px 0 0 18px; }
    .chk-confirm-body li { margin-bottom: 4px; }
    .chk-confirm-foot {
        display: flex; justify-content: flex-end; gap: 8px;
        padding: 12px 18px; border-top: 1px solid var(--c-border); background: #f9fafb;
    }

    @media (prefers-reduced-motion: reduce) {
        .drawer-overlay, .drawer-panel, .drawer-pane, .chk-item, .drawer-foot, .drawer-ring {
            transition: none !important;
            animation: none !important;
        }
    }
</style>

<div id="candidateDrawerOverlay" class="drawer-overlay" onclick="closeDrawerOnBackdrop(event)">
    <div class="drawer-panel" onclick="event.stopPropagation()">
        <div class="drawer-head">
            <div class="drawer-head-top">
                <div>
                    <h3 id="drawerCandidateName">Candidate</h3>
                    <div class="drawer-sub" id="drawerCandidateEmail"></div>
                    <div id="drawerCandidateMeta" class="drawer-candidate-meta"></div>
                </div>
                <button type="button" class="drawer-close" onclick="closeCandidateDrawer()" aria-label="Close">&times;</button>
            </div>
            <div class="drawer-progress">
                <div class="drawer-ring" id="drawerProgressRing" style="--pct: 0%;">
                    <div class="drawer-ring-inner"><span id="drawerProgressPct">0%</span></div>
                </div>
                <div class="drawer-progress-meta">
                    <strong id="drawerStageLabel">Candidate Identified</strong>
                    <span id="drawerProgressHint">Pre-submission checklist progress</span>
                </div>
            </div>
        </div>

        <div class="drawer-tabs">
            <button type="button" class="drawer-tab active" data-tab="checklist" onclick="switchDrawerTab('checklist')">Checklist</button>
            <button type="button" class="drawer-tab" data-tab="pipeline" onclick="switchDrawerTab('pipeline')">Pipeline</button>
            <button type="button" class="drawer-tab" data-tab="journey" onclick="switchDrawerTab('journey')">Journey</button>
        </div>

        <div class="drawer-body">
            <div class="drawer-pane-stack">
                {{-- Checklist tab --}}
                <div id="drawerPaneChecklist" class="drawer-pane active">
                    <p style="font-size:12px;color:var(--c-muted);margin:0 0 14px;line-height:1.5;">
                        Each checklist step syncs directly with pipeline data. Complete a step to reveal its pipeline card for dates and status.
                    </p>
                    <div id="drawerRejectionBanner" class="chk-rejection-banner" style="display:none;"></div>
                    <div id="drawerChecklist" class="chk-list"></div>
                </div>

                {{-- Pipeline tab --}}
                <div id="drawerPanePipeline" class="drawer-pane">
                    <form id="drawerPipelineForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="pipe-stepper">
                        <div class="pipe-step" data-stage="3">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(3, 'Resume Reviewed') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-row">
                                    <div class="form-field">
                                        <label>Resume Reviewed</label>
                                        <select id="d_resume_reviewed_by_recruiter" name="resume_reviewed_by_recruiter" onchange="toggleDrawerDate('resume_reviewed_date', this.value)">
                                            <option value="Pending">Pending</option>
                                            <option value="Completed">Completed</option>
                                        </select>
                                    </div>
                                    <div class="form-field" id="resume_reviewed_date_container" style="display:none;">
                                        <label>Review Date</label>
                                        <input type="date" id="d_resume_reviewed_date" name="resume_reviewed_date">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pipe-step" data-stage="4">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(4, 'Screening Call') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-row">
                                    <div class="form-field">
                                        <label>Screening Status</label>
                                        <select id="d_recruiter_screening_call" name="recruiter_screening_call" onchange="toggleDrawerDate('recruiter_screening_call_date', this.value)">
                                            <option value="Pending">Pending</option>
                                            <option value="Completed">Completed</option>
                                            <option value="No Show">No Show</option>
                                        </select>
                                    </div>
                                    <div class="form-field" id="recruiter_screening_call_date_container" style="display:none;">
                                        <label>Call Date</label>
                                        <input type="date" id="d_recruiter_screening_call_date" name="recruiter_screening_call_date">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pipe-step" data-stage="5">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(5, 'Shortlisted') }} &amp; {{ $statusLabel(6, 'Submitted to client') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-field checkbox-field">
                                    <label><input type="checkbox" id="d_candidate_shortlisted" name="candidate_shortlisted" value="1"> Shortlisted</label>
                                </div>
                                <div class="form-field">
                                    <label>Submitted to Client</label>
                                    <select id="d_resume_submitted_to_client" name="resume_submitted_to_client">
                                        <option value="Not Submitted">Not Submitted</option>
                                        <option value="Submitted">Submitted</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="pipe-step" data-stage="7">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(7, 'Internal Prep') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-row">
                                    <div class="form-field">
                                        <label>Prep Status</label>
                                        <select id="d_radix_internal_interview_prep" name="radix_internal_interview_prep" onchange="toggleDrawerDate('radix_internal_interview_prep_date', this.value)">
                                            <option value="Not Required">Not Required</option>
                                            <option value="Planned">Planned</option>
                                            <option value="Completed">Completed</option>
                                        </select>
                                    </div>
                                    <div class="form-field" id="radix_internal_interview_prep_date_container" style="display:none;">
                                        <label>Prep Date</label>
                                        <input type="date" id="d_radix_internal_interview_prep_date" name="radix_internal_interview_prep_date">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pipe-step" data-stage="8">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(8, 'Client Review') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-field">
                                    <label>Outcome</label>
                                    <select id="d_client_resume_review" name="client_resume_review">
                                        <option value="">Select</option>
                                        <option value="Approved">Approved</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="pipe-step" data-stage="9">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(9, 'Round 1') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-field">
                                    <label>Date</label>
                                    <input type="date" id="d_client_interview_round_1_date" name="client_interview_round_1_date">
                                </div>
                            </div>
                        </div>
                        <div class="pipe-step" data-stage="10">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(10, 'Round 2') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-field">
                                    <label>Date</label>
                                    <input type="date" id="d_client_interview_round_2_date" name="client_interview_round_2_date">
                                </div>
                            </div>
                        </div>
                        <div class="pipe-step" data-stage="11">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(11, 'Additional Round') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-field">
                                    <label>Additional rounds?</label>
                                    <select id="d_additional_rounds_select" name="additional_rounds_select">
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="pipe-step" data-stage="12">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(12, 'Client Decision Awaited') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-row">
                                    <div class="form-field">
                                        <label>Decision</label>
                                        <select id="d_client_decision" name="client_decision" onchange="toggleDrawerDate('client_decision_date', this.value)">
                                            <option value="">Select</option>
                                            <option value="Selected">Selected</option>
                                            <option value="Rejected">Rejected</option>
                                            <option value="On Hold">On Hold</option>
                                            <option value="Selected but declined the offer">Selected but declined the offer</option>
                                        </select>
                                    </div>
                                    <div class="form-field" id="client_decision_date_container" style="display:none;">
                                        <label>Decision Date</label>
                                        <input type="date" id="d_client_decision_date" name="client_decision_date">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pipe-step" data-stage="13">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(13, 'Client Confirmation Recieved') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-row">
                                    <div class="form-field checkbox-field">
                                        <label><input type="checkbox" id="d_client_confirmation_received" name="client_confirmation_received" value="1"> Confirmation received</label>
                                    </div>
                                    <div class="form-field">
                                        <label>Date</label>
                                        <input type="date" id="d_client_confirmation_date" name="client_confirmation_date">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pipe-step" data-stage="14">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(14, 'Offer Extended to Candidate') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-row">
                                    <div class="form-field checkbox-field">
                                        <label><input type="checkbox" id="d_offer_extended_to_candidate" name="offer_extended_to_candidate" value="1"> Offer extended</label>
                                    </div>
                                    <div class="form-field">
                                        <label>Offer Date</label>
                                        <input type="date" id="d_offer_extended_date" name="offer_extended_date">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pipe-step" data-stage="15">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(15, 'Background Check') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-field">
                                    <label>Status</label>
                                    <select id="d_background_check" name="background_check">
                                        <option value="Pending">Pending</option>
                                        <option value="Initiated">Initiated</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="pipe-step" data-stage="16">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(16, 'Candidate Project Start') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-field">
                                    <label>Start Date</label>
                                    <input type="date" id="d_candidate_project_start_date" name="candidate_project_start_date">
                                </div>
                            </div>
                        </div>
                        <div class="pipe-step" data-stage="17">
                            <span class="pipe-step-dot"></span>
                            <div class="pipe-step-head">{{ $statusLabel(17, 'Candidate Placement Completed') }}</div>
                            <div class="pipe-step-body">
                                <div class="form-row">
                                    <div class="form-field">
                                        <label>Final Status</label>
                                        <select id="d_final_status_placement_completion" name="final_status_placement_completion" onchange="toggleDrawerDate('placement_completion_date', this.value)">
                                            <option value="Confirmed">Confirmed</option>
                                            <option value="Not Confirmed">Not Confirmed</option>
                                        </select>
                                    </div>
                                    <div class="form-field" id="placement_completion_date_container" style="display:none;">
                                        <label>Completion Date</label>
                                        <input type="date" id="d_placement_completion_date" name="placement_completion_date">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="drawer-notes-block">
                            <label for="d_recruiter_notes">Recruiter Notes</label>
                            <textarea id="d_recruiter_notes" name="recruiter_notes" placeholder="Availability, other offers, call logs…"></textarea>
                        </div>
                    </div>
                </form>
                </div>

                {{-- Journey tab --}}
                <div id="drawerPaneJourney" class="drawer-pane">
                    <div id="drawerTimeline" class="timeline drawer-timeline"></div>
                </div>
            </div>
        </div>

        <div id="drawerFoot" class="drawer-foot">
            <button type="button" class="btn-i btn-ghost-i" onclick="closeCandidateDrawer()">Close</button>
            <button type="button" class="btn-i btn-primary-i" onclick="submitDrawerPipeline()">Save Pipeline</button>
        </div>
    </div>
</div>

{{-- Cascade uncheck confirmation --}}
<div id="chkConfirmOverlay" class="chk-confirm-overlay" onclick="closeChkConfirmOnBackdrop(event)">
    <div class="chk-confirm-box" onclick="event.stopPropagation()">
        <div class="chk-confirm-head">Reset downstream steps?</div>
        <div class="chk-confirm-body">
            <p id="chkConfirmMessage" style="margin:0;"></p>
            <ul id="chkConfirmList"></ul>
            <p style="margin:12px 0 0;font-size:11px;color:var(--c-muted);">
                Pipeline stage, checklist, and journey will sync. Post-submission data (interviews, offers) is not affected.
            </p>
        </div>
        <div class="chk-confirm-foot">
            <button type="button" class="btn-i btn-ghost-i" onclick="cancelChkConfirm()">Cancel</button>
            <button type="button" class="btn-i btn-primary-i" id="chkConfirmProceedBtn">Continue</button>
        </div>
    </div>
</div>

@php
    use App\Services\Tracker\CandidatePipelineService;
    $checklistLabels = (new CandidatePipelineService())->checklistFieldLabels();
@endphp

<script>
const TRACKER_ID = {{ $trackerInfo->id }};
const STAGE_COLORS = @json($stageColors);
const JOB_STATUS_LABELS = @json($jobStatuses->pluck('status', 'id'));
const jobStatusLabel = (id, fallback = '') => JOB_STATUS_LABELS[id] || fallback;
const CHECKLIST_ORDER = @json(CandidatePipelineService::CHECKLIST_FIELD_ORDER);
const CHECKLIST_LABELS = @json($checklistLabels);
const CHECKLIST_PIPELINE_CARDS = ['resume_reviewed', 'screening_call', 'submitted_to_client'];
const CHECKLIST_SECTIONS = [
    { title: 'Requirement & Sourcing', keys: ['requirement_reviewed', 'candidate_identified'] },
    { title: 'Screening', keys: ['resume_reviewed', 'screening_call'] },
    { title: 'Mandatory Documents', keys: ['doc_resume', 'doc_govt_id_collected', 'doc_work_auth_collected', 'doc_linkedin_collected'] },
    { title: 'Authorization & Submission', keys: ['rtr_signed', 'candidate_shortlisted', 'submitted_to_client'] },
];

let drawerTcId = null;
let drawerRow = null;
let drawerCloseTimer = null;
let drawerTimelineTcId = null;
let chkConfirmCallback = null;
let chkDetailTimer = null;
const DRAWER_ANIM_MS = 420;

function openCandidateDrawer(tcId) {
    drawerTcId = tcId;
    drawerRow = document.querySelector('tr[data-tracker-candidate-id="' + tcId + '"]');
    if (!drawerRow) return;

    const overlay = document.getElementById('candidateDrawerOverlay');
    const alreadyOpen = overlay.classList.contains('is-open');

    if (drawerCloseTimer) {
        clearTimeout(drawerCloseTimer);
        drawerCloseTimer = null;
    }

    document.getElementById('drawerCandidateName').textContent = drawerRow.dataset.candidateName || 'Candidate';
    document.getElementById('drawerCandidateEmail').textContent =
        drawerRow.querySelector('.candidate-email')?.textContent?.trim() || '';

    const metaEl = document.getElementById('drawerCandidateMeta');
    const metaParts = [];
    if (drawerRow.dataset.payRate) metaParts.push('<div><strong>Pay Rate</strong>' + escapeHtml(drawerRow.dataset.payRate) + '</div>');
    if (drawerRow.dataset.placementPayRate) metaParts.push('<div><strong>Placement Pay</strong>' + escapeHtml(drawerRow.dataset.placementPayRate) + '</div>');
    if (drawerRow.dataset.candidateSummary) metaParts.push('<div><strong>Summary</strong>' + escapeHtml(drawerRow.dataset.candidateSummary) + '</div>');
    if (metaParts.length) {
        metaEl.innerHTML = metaParts.join('');
        metaEl.classList.add('is-visible');
    } else {
        metaEl.innerHTML = '';
        metaEl.classList.remove('is-visible');
    }

    document.getElementById('drawerPipelineForm').action =
        '/tracker/info/' + TRACKER_ID + '/candidates/' + tcId + '/pipeline';

    drawerTimelineTcId = null;
    document.getElementById('drawerTimeline').innerHTML = '';

    const checklist = document.getElementById('drawerChecklist');
    checklist.innerHTML =
        '<div class="drawer-loading"><span class="drawer-loading-spinner"></span> Loading…</div>';

    switchDrawerTab('checklist', true);
    document.body.classList.add('drawer-open');
    if (typeof syncBodyScrollLock === 'function') syncBodyScrollLock();

    if (!alreadyOpen) {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => overlay.classList.add('is-open'));
        });
    }

    loadDrawerCandidateData(tcId);
}

function loadDrawerCandidateData(tcId) {
    fetch('/tracker/info/' + TRACKER_ID + '/candidates/' + tcId + '/pipeline', {
        headers: { 'Accept': 'application/json' },
    })
    .then(r => {
        if (!r.ok) throw new Error('Failed to load pipeline');
        return r.json();
    })
    .then(data => {
        if (!drawerRow || drawerTcId !== tcId) return;

        hydrateRowFromPipelineApi(drawerRow, data);
        populateDrawerPipelineForm(drawerRow);
        renderDrawerRejectionBanner(drawerRow);
        renderDrawerChecklist(drawerRow);
        updateDrawerProgressFromRow(drawerRow);
        highlightPipelineSteps(parseInt(drawerRow.dataset.currentStatusId || '2', 10));

        if (document.querySelector('.drawer-tab.active')?.dataset.tab === 'journey') {
            drawerTimelineTcId = drawerTcId;
            renderDrawerTimeline(drawerRow);
        }
    })
    .catch(() => {
        if (!drawerRow || drawerTcId !== tcId) return;

        document.getElementById('drawerChecklist').innerHTML =
            '<p style="font-size:12px;color:var(--c-danger);">Could not load pipeline data. Please refresh and try again.</p>';

        populateDrawerPipelineForm(drawerRow);
        renderDrawerRejectionBanner(drawerRow);
        updateDrawerProgressFromRow(drawerRow);
    });
}

function isDrawerReadOnly(row) {
    return row && row.dataset.rejected === '1';
}

function renderDrawerRejectionBanner(row) {
    const banner = document.getElementById('drawerRejectionBanner');
    if (!banner) return;

    if (row.dataset.rejected !== '1') {
        banner.style.display = 'none';
        banner.innerHTML = '';
        return;
    }

    const reason = row.dataset.rejectionReason || 'No reason provided';
    const rejectedAt = row.dataset.rejectedAt
        ? new Date(row.dataset.rejectedAt + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
        : '';

    banner.innerHTML =
        '<strong>Rejection reason</strong>' +
        '<p>' + escapeHtml(reason) + '</p>' +
        (rejectedAt ? '<span>Rejected ' + rejectedAt + '</span>' : '');
    banner.style.display = 'block';
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function closeCandidateDrawer() {
    const overlay = document.getElementById('candidateDrawerOverlay');
    if (!overlay.classList.contains('is-open')) return;

    overlay.classList.remove('is-open');
    document.body.classList.remove('drawer-open');
    if (typeof syncBodyScrollLock === 'function') syncBodyScrollLock();

    drawerCloseTimer = setTimeout(() => {
        drawerTcId = null;
        drawerRow = null;
        drawerCloseTimer = null;
    }, DRAWER_ANIM_MS);
}

function closeDrawerOnBackdrop(e) {
    if (e.target.id === 'candidateDrawerOverlay') closeCandidateDrawer();
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const overlay = document.getElementById('candidateDrawerOverlay');
        if (overlay && overlay.classList.contains('is-open')) {
            closeCandidateDrawer();
        }
    }
});

function switchDrawerTab(tab, instant) {
    document.querySelectorAll('.drawer-tab').forEach(t => {
        t.classList.toggle('active', t.dataset.tab === tab);
    });

    const panes = ['checklist', 'pipeline', 'journey'];
    panes.forEach(name => {
        const pane = document.getElementById('drawerPane' + name.charAt(0).toUpperCase() + name.slice(1));
        if (!pane) return;
        const isActive = name === tab;
        if (instant && isActive) {
            pane.style.transition = 'none';
            pane.classList.add('active');
            requestAnimationFrame(() => { pane.style.transition = ''; });
        } else {
            pane.classList.toggle('active', isActive);
        }
    });

    document.getElementById('drawerFoot').classList.toggle('visible', tab === 'pipeline');

    if (tab === 'journey' && drawerRow && drawerTimelineTcId !== drawerTcId) {
        drawerTimelineTcId = drawerTcId;
        renderDrawerTimeline(drawerRow);
    }

    const body = document.querySelector('.drawer-body');
    if (body && !instant) body.scrollTop = 0;
}

function updateDrawerProgressFromRow(row) {
    const pct = parseInt(row.dataset.checklistProgress || '0', 10);
    const ring = document.getElementById('drawerProgressRing');
    ring.style.setProperty('--pct', pct + '%');
    document.getElementById('drawerProgressPct').textContent = pct + '%';
    document.getElementById('drawerStageLabel').textContent = row.dataset.stageLabel || 'Unknown';
}

function renderDrawerChecklist(row) {
    const container = document.getElementById('drawerChecklist');
    container.innerHTML = '';

    const state = getChecklistStateFromRow(row);
    const readOnly = isDrawerReadOnly(row);
    let animIndex = 0;

    CHECKLIST_SECTIONS.forEach(section => {
        const title = document.createElement('div');
        title.className = 'chk-section-title';
        title.textContent = section.title;
        title.style.animationDelay = (animIndex * 0.04) + 's';
        animIndex++;
        container.appendChild(title);

        section.keys.forEach(key => {
            const item = state[key];
            if (!item) return;

            if (CHECKLIST_PIPELINE_CARDS.includes(key)) {
                container.appendChild(buildChecklistPipelineCard(key, item, row, readOnly, animIndex));
            } else {
                container.appendChild(buildSimpleChecklistItem(key, item, readOnly, animIndex));
            }
            animIndex++;
        });
    });
}

function buildSimpleChecklistItem(key, item, readOnly, animIndex) {
    const el = document.createElement('label');
    el.className = 'chk-item' + (item.done ? ' done' : '') + (item.readonly ? ' readonly' : '');
    el.style.animationDelay = (animIndex * 0.04) + 's';
    el.innerHTML =
        '<input type="checkbox"' + (item.done ? ' checked' : '') +
        (item.readonly || readOnly ? ' disabled' : '') +
        ' data-field="' + key + '" onchange="toggleChecklistItem(this)">' +
        '<span class="chk-item-label">' + item.label + '</span>';
    return el;
}

function buildChecklistPipelineCard(key, item, row, readOnly, animIndex) {
    const card = document.createElement('div');
    card.className = 'chk-card' + (item.done ? ' done' : '');
    card.dataset.field = key;
    card.style.animationDelay = (animIndex * 0.04) + 's';

    const head = document.createElement('div');
    head.className = 'chk-card-head';
    head.innerHTML =
        '<input type="checkbox"' + (item.done ? ' checked' : '') +
        (item.readonly || readOnly ? ' disabled' : '') +
        ' data-field="' + key + '" onchange="toggleChecklistItem(this)">' +
        '<span class="chk-card-label">' + item.label + '</span>';
    card.appendChild(head);

    const body = document.createElement('div');
    body.className = 'chk-card-body';
    body.style.display = item.done ? 'block' : 'none';
    body.innerHTML = '<div class="chk-pipe-title">Pipeline details</div>' + buildPipelineCardFields(key, row, readOnly);
    card.appendChild(body);

    return card;
}

function buildPipelineCardFields(key, row, readOnly) {
    const disabled = readOnly ? ' disabled' : '';

    if (key === 'resume_reviewed') {
        const status = row.dataset.resumeReviewed || 'Pending';
        const date = row.dataset.resumeReviewedDate || '';
        const showDate = status === 'Completed';
        return '<div class="chk-pipe-fields" data-pipe-field="resume_reviewed">' +
            '<div class="form-row">' +
            '<div class="form-field">' +
            '<label>Review status</label>' +
            '<select data-detail="resume_reviewed_by_recruiter"' + disabled + ' onchange="onChecklistPipelineChange(\'resume_reviewed\', this)">' +
            '<option value="Pending"' + (status === 'Pending' ? ' selected' : '') + '>Pending</option>' +
            '<option value="Completed"' + (status === 'Completed' ? ' selected' : '') + '>Completed</option>' +
            '</select></div>' +
            '<div class="form-field" data-date-wrap="resume_reviewed_date"' + (showDate ? '' : ' style="display:none;"') + '>' +
            '<label>Review date</label>' +
            '<input type="date" data-detail="resume_reviewed_date" value="' + date + '"' + disabled +
            ' onchange="onChecklistPipelineChange(\'resume_reviewed\', this)"></div>' +
            '</div></div>';
    }

    if (key === 'screening_call') {
        const status = row.dataset.recruiterScreening || 'Pending';
        const date = row.dataset.recruiterScreeningDate || '';
        const showDate = status === 'Completed';
        return '<div class="chk-pipe-fields" data-pipe-field="screening_call">' +
            '<div class="form-row">' +
            '<div class="form-field">' +
            '<label>Call status</label>' +
            '<select data-detail="recruiter_screening_call"' + disabled + ' onchange="onChecklistPipelineChange(\'screening_call\', this)">' +
            '<option value="Pending"' + (status === 'Pending' ? ' selected' : '') + '>Pending</option>' +
            '<option value="Completed"' + (status === 'Completed' ? ' selected' : '') + '>Completed</option>' +
            '<option value="No Show"' + (status === 'No Show' ? ' selected' : '') + '>No Show</option>' +
            '</select></div>' +
            '<div class="form-field" data-date-wrap="recruiter_screening_call_date"' + (showDate ? '' : ' style="display:none;"') + '>' +
            '<label>Call date</label>' +
            '<input type="date" data-detail="recruiter_screening_call_date" value="' + date + '"' + disabled +
            ' onchange="onChecklistPipelineChange(\'screening_call\', this)"></div>' +
            '</div></div>';
    }

    if (key === 'submitted_to_client') {
        const status = row.dataset.resumeSubmitted || 'Not Submitted';
        return '<div class="chk-pipe-fields" data-pipe-field="submitted_to_client">' +
            '<div class="form-field">' +
            '<label>Client submission</label>' +
            '<select data-detail="resume_submitted_to_client"' + disabled + ' onchange="onChecklistPipelineChange(\'submitted_to_client\', this)">' +
            '<option value="Not Submitted"' + (status === 'Not Submitted' ? ' selected' : '') + '>Not Submitted</option>' +
            '<option value="Submitted"' + (status === 'Submitted' ? ' selected' : '') + '>Submitted</option>' +
            '</select></div></div>';
    }

    return '';
}

function onChecklistPipelineChange(field, el) {
    if (isDrawerReadOnly(drawerRow)) return;

    const card = el.closest('.chk-card');
    if (!card) return;

    const fieldsWrap = card.querySelector('.chk-pipe-fields');
    if (!fieldsWrap) return;

    const details = {};
    fieldsWrap.querySelectorAll('[data-detail]').forEach(input => {
        details[input.dataset.detail] = input.value;
    });

    if (field === 'resume_reviewed') {
        const showDate = details.resume_reviewed_by_recruiter === 'Completed';
        const dateWrap = fieldsWrap.querySelector('[data-date-wrap="resume_reviewed_date"]');
        if (dateWrap) dateWrap.style.display = showDate ? 'block' : 'none';
        if (!showDate) details.resume_reviewed_date = null;

        if (details.resume_reviewed_by_recruiter !== 'Completed') {
            const prevStatus = drawerRow.dataset.resumeReviewed || 'Pending';
            const prevDate = drawerRow.dataset.resumeReviewedDate || '';
            window._chkConfirmRevert = () => {
                const sel = fieldsWrap.querySelector('[data-detail="resume_reviewed_by_recruiter"]');
                const dateInput = fieldsWrap.querySelector('[data-detail="resume_reviewed_date"]');
                if (sel) sel.value = prevStatus;
                if (dateInput) dateInput.value = prevDate;
                if (dateWrap) dateWrap.style.display = prevStatus === 'Completed' ? 'block' : 'none';
            };
            showChkConfirm(field, () => {
                window._chkConfirmRevert = null;
                saveChecklistDetails(field, details, card);
            });
            return;
        }
    }

    if (field === 'screening_call') {
        const showDate = details.recruiter_screening_call === 'Completed';
        const dateWrap = fieldsWrap.querySelector('[data-date-wrap="recruiter_screening_call_date"]');
        if (dateWrap) dateWrap.style.display = showDate ? 'block' : 'none';
        if (!showDate) details.recruiter_screening_call_date = null;

        if (details.recruiter_screening_call !== 'Completed') {
            const prevStatus = drawerRow.dataset.recruiterScreening || 'Pending';
            const prevDate = drawerRow.dataset.recruiterScreeningDate || '';
            window._chkConfirmRevert = () => {
                const sel = fieldsWrap.querySelector('[data-detail="recruiter_screening_call"]');
                const dateInput = fieldsWrap.querySelector('[data-detail="recruiter_screening_call_date"]');
                if (sel) sel.value = prevStatus;
                if (dateInput) dateInput.value = prevDate;
                if (dateWrap) dateWrap.style.display = prevStatus === 'Completed' ? 'block' : 'none';
            };
            showChkConfirm(field, () => {
                window._chkConfirmRevert = null;
                saveChecklistDetails(field, details, card);
            });
            return;
        }
    }

    if (field === 'submitted_to_client' && details.resume_submitted_to_client !== 'Submitted') {
        const prevStatus = drawerRow.dataset.resumeSubmitted || 'Not Submitted';
        window._chkConfirmRevert = () => {
            const sel = fieldsWrap.querySelector('[data-detail="resume_submitted_to_client"]');
            if (sel) sel.value = prevStatus;
        };
        showChkConfirm(field, () => {
            window._chkConfirmRevert = null;
            saveChecklistDetails(field, details, card);
        });
        return;
    }

    clearTimeout(chkDetailTimer);
    chkDetailTimer = setTimeout(() => saveChecklistDetails(field, details, card), 300);
}

function saveChecklistDetails(field, details, cardEl) {
    if (!drawerTcId || isDrawerReadOnly(drawerRow)) return;

    if (cardEl) cardEl.classList.add('is-saving');

    fetch('/tracker/info/' + TRACKER_ID + '/candidates/' + drawerTcId + '/checklist', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                document.querySelector('#drawerPipelineForm input[name="_token"]')?.value,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ field: field, details: details }),
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (!ok) throw new Error(data.message || 'Update failed');
        applyChecklistResponse(data);
    })
    .catch(err => {
        alert(err.message || 'Could not update pipeline details.');
        if (drawerRow) renderDrawerChecklist(drawerRow);
    })
    .finally(() => {
        if (cardEl) cardEl.classList.remove('is-saving');
    });
}

function getChecklistStateFromRow(row) {
    const hasResume = row.dataset.hasResume === '1';
    const docResumeCollected = row.dataset.docResumeCollected === '1';
    return {
        requirement_reviewed: { key: 'requirement_reviewed', label: 'Requirement understood (JD reviewed)', done: row.dataset.requirementReviewed === '1' },
        candidate_identified: {
            key: 'candidate_identified',
            label: 'Candidate sourced & identified',
            done: row.dataset.candidateIdentified === '1',
        },
        resume_reviewed: { key: 'resume_reviewed', label: 'Resume reviewed by recruiter', done: row.dataset.resumeReviewed === 'Completed' },
        screening_call: { key: 'screening_call', label: 'Initial screening call completed', done: row.dataset.recruiterScreening === 'Completed' },
        doc_resume: {
            key: 'doc_resume',
            label: 'Updated resume on file',
            done: hasResume || docResumeCollected,
            readonly: hasResume,
        },
        doc_govt_id_collected: { key: 'doc_govt_id_collected', label: 'Government photo ID collected', done: row.dataset.docGovtId === '1' },
        doc_work_auth_collected: { key: 'doc_work_auth_collected', label: 'Work authorization copy collected', done: row.dataset.docWorkAuth === '1' },
        doc_linkedin_collected: { key: 'doc_linkedin_collected', label: 'LinkedIn profile link collected', done: row.dataset.docLinkedin === '1' },
        rtr_signed: { key: 'rtr_signed', label: 'Signed RTR obtained', done: row.dataset.rtrSigned === '1' },
        candidate_shortlisted: { key: 'candidate_shortlisted', label: 'Candidate shortlisted for submission', done: row.dataset.shortlisted === '1' },
        submitted_to_client: { key: 'submitted_to_client', label: 'Submitted to client', done: row.dataset.resumeSubmitted === 'Submitted' },
    };
}

function getDownstreamChecklistFields(field) {
    const idx = CHECKLIST_ORDER.indexOf(field);
    if (idx === -1) return [];
    const hasResume = drawerRow && drawerRow.dataset.hasResume === '1';
    return CHECKLIST_ORDER.slice(idx + 1).filter(f => !(f === 'doc_resume' && hasResume));
}

function showChkConfirm(field, onProceed) {
    const downstream = getDownstreamChecklistFields(field);
    if (downstream.length === 0) {
        onProceed();
        return;
    }

    const fieldLabel = CHECKLIST_LABELS[field] || field;
    document.getElementById('chkConfirmMessage').textContent =
        'Unchecking "' + fieldLabel + '" will also reset these steps:';

    const list = document.getElementById('chkConfirmList');
    list.innerHTML = '';
    downstream.forEach(key => {
        const li = document.createElement('li');
        li.textContent = CHECKLIST_LABELS[key] || key;
        list.appendChild(li);
    });

    chkConfirmCallback = onProceed;
    document.getElementById('chkConfirmProceedBtn').onclick = () => {
        const cb = chkConfirmCallback;
        window._chkConfirmRevert = null;
        closeChkConfirm();
        if (cb) cb();
    };
    document.getElementById('chkConfirmOverlay').classList.add('is-open');
}

function closeChkConfirm() {
    document.getElementById('chkConfirmOverlay').classList.remove('is-open');
    chkConfirmCallback = null;
}

function cancelChkConfirm() {
    closeChkConfirm();
    if (window._chkConfirmRevert) {
        window._chkConfirmRevert();
        window._chkConfirmRevert = null;
    }
}

function closeChkConfirmOnBackdrop(e) {
    if (e.target.id === 'chkConfirmOverlay') cancelChkConfirm();
}

function toggleChecklistItem(checkbox) {
    if (isDrawerReadOnly(drawerRow)) {
        checkbox.checked = !checkbox.checked;
        return;
    }

    const field = checkbox.dataset.field;
    const checked = checkbox.checked;

    const doSave = () => saveChecklistToggle(checkbox, field, checked);

    if (!checked) {
        window._chkConfirmRevert = () => { checkbox.checked = true; };
        showChkConfirm(field, doSave);
        return;
    }

    doSave();
}

function saveChecklistToggle(checkbox, field, checked) {
    const row = checkbox.closest('.chk-item, .chk-card');
    if (row) row.classList.add('is-saving');
    checkbox.disabled = true;

    fetch('/tracker/info/' + TRACKER_ID + '/candidates/' + drawerTcId + '/checklist', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                document.querySelector('#drawerPipelineForm input[name="_token"]')?.value,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ field: field, checked: checked }),
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (!ok) throw new Error(data.message || 'Update failed');
        applyChecklistResponse(data);
    })
    .catch(err => {
        checkbox.checked = !checked;
        alert(err.message || 'Could not update checklist.');
    })
    .finally(() => {
        if (row) row.classList.remove('is-saving');
        const parent = checkbox.closest('.chk-item, .chk-card');
        const itemState = parent && drawerRow ? getChecklistStateFromRow(drawerRow)[checkbox.dataset.field] : null;
        if (!isDrawerReadOnly(drawerRow) && !(itemState && itemState.readonly)) {
            checkbox.disabled = false;
        }
        window._chkConfirmRevert = null;
    });
}

function syncRowFromPipeline(row, pipeline) {
    hydrateRowFromPipelineApi(row, pipeline);
}

function hydrateRowFromPipelineApi(row, data) {
    if (!row || !data) return;

    row.dataset.candidateIdentified = data.candidate_identified ? '1' : '0';
    row.dataset.requirementReviewed = data.requirement_reviewed ? '1' : '0';
    row.dataset.docResumeCollected = data.doc_resume_collected ? '1' : '0';
    row.dataset.docGovtId = data.doc_govt_id_collected ? '1' : '0';
    row.dataset.docWorkAuth = data.doc_work_auth_collected ? '1' : '0';
    row.dataset.docLinkedin = data.doc_linkedin_collected ? '1' : '0';
    row.dataset.rtrSigned = data.rtr_signed ? '1' : '0';
    if (data.has_resume !== undefined) {
        row.dataset.hasResume = data.has_resume ? '1' : '0';
    }
    row.dataset.resumeReviewed = data.resume_reviewed_by_recruiter || 'Pending';
    row.dataset.resumeReviewedDate = data.resume_reviewed_date || '';
    row.dataset.recruiterScreening = data.recruiter_screening_call || 'Pending';
    row.dataset.recruiterScreeningDate = data.recruiter_screening_call_date || '';
    row.dataset.shortlisted = data.candidate_shortlisted ? '1' : '0';
    row.dataset.resumeSubmitted = data.resume_submitted_to_client || 'Not Submitted';
    row.dataset.radixPrep = data.radix_internal_interview_prep || 'Not Required';
    row.dataset.radixPrepDate = data.radix_internal_interview_prep_date || '';
    row.dataset.clientReview = data.client_resume_review || '';
    row.dataset.interviewRound1 = data.client_interview_round_1_date || '';
    row.dataset.interviewRound2 = data.client_interview_round_2_date || '';
    row.dataset.additionalRounds = data.additional_rounds ? '1' : '0';
    row.dataset.clientDecision = data.client_decision || '';
    row.dataset.clientDecisionDate = data.client_decision_date || '';
    row.dataset.confirmationReceived = data.client_confirmation_received ? '1' : '0';
    row.dataset.confirmationDate = data.client_confirmation_date || '';
    row.dataset.offerExtended = data.offer_extended_to_candidate ? '1' : '0';
    row.dataset.offerExtendedDate = data.offer_extended_date || '';
    row.dataset.backgroundCheck = data.background_check || 'Pending';
    row.dataset.projectStart = data.candidate_project_start_date || '';
    row.dataset.finalStatus = data.final_status_placement_completion || '';
    row.dataset.placementDate = data.placement_completion_date || '';
    row.dataset.recruiterNotes = data.recruiter_notes || '';

    if (data.checklist_progress !== undefined) {
        row.dataset.checklistProgress = String(data.checklist_progress);
    }
    if (data.current_status_id !== undefined) {
        row.dataset.currentStatusId = String(data.current_status_id);
    }
    if (data.current_status_label) {
        row.dataset.stageLabel = data.current_status_label;
    }
    if (data.final_status_placement_completion === 'Confirmed') {
        row.dataset.placed = '1';
    }
}

function applyChecklistResponse(data) {
    if (!drawerRow) return;

    if (data.pipeline) {
        syncRowFromPipeline(drawerRow, data.pipeline);
    } else if (data.checklist_items) {
        const items = data.checklist_items || [];
        items.forEach(item => {
            const key = item.key;
            if (key === 'requirement_reviewed') drawerRow.dataset.requirementReviewed = item.done ? '1' : '0';
            if (key === 'candidate_identified') drawerRow.dataset.candidateIdentified = item.done ? '1' : '0';
            if (key === 'resume_reviewed') drawerRow.dataset.resumeReviewed = item.done ? 'Completed' : 'Pending';
            if (key === 'screening_call') drawerRow.dataset.recruiterScreening = item.done ? 'Completed' : 'Pending';
            if (key === 'doc_resume') drawerRow.dataset.docResumeCollected = item.done ? '1' : '0';
            if (key === 'doc_govt_id_collected') drawerRow.dataset.docGovtId = item.done ? '1' : '0';
            if (key === 'doc_work_auth_collected') drawerRow.dataset.docWorkAuth = item.done ? '1' : '0';
            if (key === 'doc_linkedin_collected') drawerRow.dataset.docLinkedin = item.done ? '1' : '0';
            if (key === 'rtr_signed') drawerRow.dataset.rtrSigned = item.done ? '1' : '0';
            if (key === 'candidate_shortlisted') drawerRow.dataset.shortlisted = item.done ? '1' : '0';
            if (key === 'submitted_to_client') drawerRow.dataset.resumeSubmitted = item.done ? 'Submitted' : 'Not Submitted';
        });
    }

    drawerRow.dataset.checklistProgress = String(data.checklist_progress || 0);
    drawerRow.dataset.currentStatusId = String(data.current_status_id);
    drawerRow.dataset.stageLabel = data.current_status_label || drawerRow.dataset.stageLabel;

    updateTableStageBadge(drawerRow, data.current_status_id, data.current_status_label);
    updateDrawerProgressFromRow(drawerRow);
    highlightPipelineSteps(parseInt(drawerRow.dataset.currentStatusId || '2', 10));

    renderDrawerRejectionBanner(drawerRow);
    renderDrawerChecklist(drawerRow);
    populateDrawerPipelineForm(drawerRow);
    drawerTimelineTcId = drawerTcId;
    renderDrawerTimeline(drawerRow);
}

function updateTableStageBadge(row, statusId, label) {
    const badge = row.querySelector('.stage-badge');
    if (!badge) return;
    const color = STAGE_COLORS[statusId] || '#6B7280';
    badge.innerHTML = '<span class="dot" style="background:' + color + ';"></span>' + (label || 'Unknown');
}

function populateDrawerPipelineForm(row) {
    setDrawerVal('d_resume_reviewed_by_recruiter', row.dataset.resumeReviewed || 'Pending');
    setDrawerVal('d_resume_reviewed_date', row.dataset.resumeReviewedDate || '');
    setDrawerVal('d_recruiter_screening_call', row.dataset.recruiterScreening || 'Pending');
    setDrawerVal('d_recruiter_screening_call_date', row.dataset.recruiterScreeningDate || '');
    setDrawerCheck('d_candidate_shortlisted', row.dataset.shortlisted === '1');
    setDrawerVal('d_resume_submitted_to_client', row.dataset.resumeSubmitted || 'Not Submitted');
    setDrawerVal('d_radix_internal_interview_prep', row.dataset.radixPrep || 'Not Required');
    setDrawerVal('d_radix_internal_interview_prep_date', row.dataset.radixPrepDate || '');
    setDrawerVal('d_client_resume_review', row.dataset.clientReview || '');
    setDrawerVal('d_client_interview_round_1_date', row.dataset.interviewRound1 || '');
    setDrawerVal('d_client_interview_round_2_date', row.dataset.interviewRound2 || '');
    setDrawerVal('d_additional_rounds_select', row.dataset.additionalRounds === '1' ? 'Yes' : 'No');
    setDrawerVal('d_client_decision', row.dataset.clientDecision || '');
    setDrawerVal('d_client_decision_date', row.dataset.clientDecisionDate || '');
    setDrawerCheck('d_client_confirmation_received', row.dataset.confirmationReceived === '1');
    setDrawerVal('d_client_confirmation_date', row.dataset.confirmationDate || '');
    setDrawerCheck('d_offer_extended_to_candidate', row.dataset.offerExtended === '1');
    setDrawerVal('d_offer_extended_date', row.dataset.offerExtendedDate || '');
    setDrawerVal('d_background_check', row.dataset.backgroundCheck || 'Pending');
    setDrawerVal('d_candidate_project_start_date', row.dataset.projectStart || '');
    setDrawerVal('d_final_status_placement_completion', row.dataset.finalStatus || 'Confirmed');
    setDrawerVal('d_placement_completion_date', row.dataset.placementDate || '');
    setDrawerVal('d_recruiter_notes', row.dataset.recruiterNotes || '');

    toggleDrawerDate('resume_reviewed_date', document.getElementById('d_resume_reviewed_by_recruiter').value);
    toggleDrawerDate('recruiter_screening_call_date', document.getElementById('d_recruiter_screening_call').value);
    toggleDrawerDate('radix_internal_interview_prep_date', document.getElementById('d_radix_internal_interview_prep').value);
    toggleDrawerDate('client_decision_date', document.getElementById('d_client_decision').value);
    toggleDrawerDate('placement_completion_date', document.getElementById('d_final_status_placement_completion').value);
}

function setDrawerVal(id, val) { const el = document.getElementById(id); if (el) el.value = val; }
function setDrawerCheck(id, val) { const el = document.getElementById(id); if (el) el.checked = val; }

function toggleDrawerDate(dateId, value) {
    const container = document.getElementById(dateId + '_container');
    if (!container) return;
    const show = value && !['', 'Pending', 'Not Required', 'No Show', 'Not Submitted', 'On Hold'].includes(value);
    container.style.display = show ? 'block' : 'none';
}

function highlightPipelineSteps(currentId) {
    document.querySelectorAll('.pipe-step').forEach(step => {
        const stage = parseInt(step.dataset.stage, 10);
        step.classList.toggle('done', stage < currentId);
    });
}

function submitDrawerPipeline() {
    document.getElementById('drawerPipelineForm').submit();
}

function renderDrawerTimeline(row) {
    const tl = document.getElementById('drawerTimeline');
    tl.innerHTML = '';
    const stages = [
        { id: 2, title: jobStatusLabel(2, 'Candidate Identified'), date: null, desc: 'Added to job pipeline.' },
        { id: 3, title: jobStatusLabel(3), date: row.dataset.resumeReviewedDate, desc: row.dataset.resumeReviewed === 'Completed' ? 'Review completed.' : 'Pending.' },
        { id: 4, title: jobStatusLabel(4), date: row.dataset.recruiterScreeningDate, desc: row.dataset.recruiterScreening === 'Completed' ? 'Call completed.' : 'Pending.' },
        { id: 5, title: jobStatusLabel(5), date: null, desc: row.dataset.shortlisted === '1' ? 'Shortlisted.' : 'Not shortlisted.' },
        { id: 6, title: jobStatusLabel(6), date: null, desc: row.dataset.resumeSubmitted === 'Submitted' ? 'Submitted.' : 'Not submitted.' },
        { id: 7, title: jobStatusLabel(7), date: row.dataset.radixPrepDate, desc: row.dataset.radixPrep || 'N/A' },
        { id: 8, title: jobStatusLabel(8), date: null, desc: row.dataset.clientReview || 'Pending.' },
        { id: 9, title: jobStatusLabel(9), date: row.dataset.interviewRound1, desc: row.dataset.interviewRound1 ? 'Scheduled.' : 'Not scheduled.' },
        { id: 10, title: jobStatusLabel(10), date: row.dataset.interviewRound2, desc: row.dataset.interviewRound2 ? 'Scheduled.' : 'Not scheduled.' },
        { id: 11, title: jobStatusLabel(11), date: null, desc: row.dataset.additionalRounds === '1' ? 'Additional rounds.' : 'No extra rounds.' },
        { id: 12, title: jobStatusLabel(12), date: row.dataset.clientDecisionDate, desc: row.dataset.clientDecision || 'Awaiting.' },
        { id: 13, title: jobStatusLabel(13), date: row.dataset.confirmationDate, desc: row.dataset.confirmationReceived === '1' ? 'Received.' : 'Pending.' },
        { id: 14, title: jobStatusLabel(14), date: row.dataset.offerExtendedDate, desc: row.dataset.offerExtended === '1' ? 'Extended.' : 'Pending.' },
        { id: 15, title: jobStatusLabel(15), date: null, desc: row.dataset.backgroundCheck || 'Not started.' },
        { id: 16, title: jobStatusLabel(16), date: row.dataset.projectStart, desc: row.dataset.projectStart ? 'Date set.' : 'Pending.' },
        { id: 17, title: jobStatusLabel(17), date: row.dataset.placementDate, desc: 'Placement workflow.' },
    ];
    const curId = parseInt(row.dataset.currentStatusId || '2', 10);
    stages.forEach((stage, i) => {
        const div = document.createElement('div');
        div.className = 'tl-item' + (stage.id < curId ? ' done' : '') + (stage.id === curId ? ' active' : '');
        div.style.animationDelay = (i * 0.035) + 's';
        const dateStr = stage.date ? new Date(stage.date + 'T00:00:00').toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' }) : '';
        div.innerHTML = '<div class="tl-content"><div class="tl-title">' + stage.title +
            '<span class="tl-date">' + dateStr + '</span></div><div class="tl-desc">' + stage.desc + '</div></div>';
        tl.appendChild(div);
    });
}
</script>
