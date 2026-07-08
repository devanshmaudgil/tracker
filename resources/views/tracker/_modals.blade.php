{{-- Create Candidate Modal --}}
<style>
    .cc-modal {
        max-width: 680px;
        max-height: min(92vh, 860px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(10, 45, 41, 0.1);
    }

    .cc-modal__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px 18px;
        background: linear-gradient(135deg, var(--c-primary) 0%, #0d3d38 55%, #0a2d29 100%);
        border-bottom: 3px solid var(--c-accent);
        flex-shrink: 0;
    }

    .cc-modal__head-left {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        min-width: 0;
    }

    .cc-modal__icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--c-accent) 0%, #e8c078 100%);
        color: var(--c-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(241, 205, 134, 0.35);
    }

    .cc-modal__icon svg { width: 22px; height: 22px; }

    .cc-modal__title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #fff;
        letter-spacing: -0.01em;
        line-height: 1.25;
    }

    .cc-modal__sub {
        margin: 4px 0 0;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.72);
        line-height: 1.45;
    }

    .cc-modal__sub strong {
        color: var(--c-accent);
        font-weight: 700;
    }

    .cc-modal__close {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #fff;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 20px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease, transform 0.15s ease;
        flex-shrink: 0;
    }

    .cc-modal__close:hover {
        background: rgba(255, 255, 255, 0.22);
        transform: scale(1.04);
    }

    .cc-modal__form {
        display: flex;
        flex-direction: column;
        min-height: 0;
        flex: 1;
    }

    .cc-modal__body {
        padding: 20px 22px 8px;
        overflow-y: auto;
        flex: 1;
        background: linear-gradient(180deg, #fafbfc 0%, #fff 120px);
    }

    .cc-section {
        margin-bottom: 20px;
        padding: 16px 16px 4px;
        background: #fff;
        border: 1px solid var(--c-border);
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(10, 45, 41, 0.04);
    }

    .cc-section:last-child { margin-bottom: 12px; }

    .cc-section__head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eef1f3;
    }

    .cc-section__badge {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(10, 45, 41, 0.07);
        color: var(--c-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .cc-section__badge svg { width: 14px; height: 14px; }

    .cc-section__title {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--c-primary);
        margin: 0;
    }

    .cc-section__hint {
        margin-left: auto;
        font-size: 10.5px;
        color: var(--c-muted);
        font-weight: 500;
    }

    .cc-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 14px;
    }

    .cc-grid .span-2 { grid-column: span 2; }

    .cc-field { margin: 0; }

    .cc-field label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 6px;
        letter-spacing: 0.02em;
    }

    .cc-field label .req { color: #dc2626; margin-left: 2px; }

    .cc-field input,
    .cc-field select,
    .cc-field textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #dde3e8;
        border-radius: 9px;
        font-size: 13px;
        color: var(--c-text);
        background: #fafbfc;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        font-family: inherit;
    }

    .cc-field input::placeholder,
    .cc-field textarea::placeholder { color: #9ca3af; }

    .cc-field input:hover,
    .cc-field select:hover,
    .cc-field textarea:hover {
        border-color: #c5cdd6;
        background: #fff;
    }

    .cc-field input:focus,
    .cc-field select:focus,
    .cc-field textarea:focus {
        border-color: var(--c-primary);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(10, 45, 41, 0.08);
    }

    .cc-field textarea {
        resize: vertical;
        min-height: 72px;
        line-height: 1.5;
    }

    .cc-field select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 34px;
    }

    .cc-loc-wrap { position: relative; }

    .cc-loc-wrap input { padding-left: 36px; }

    .cc-loc-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--c-muted);
        pointer-events: none;
        display: flex;
    }

    .cc-loc-icon svg { width: 15px; height: 15px; }

    .cc-loc-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid var(--c-border);
        border-radius: 10px;
        max-height: 160px;
        overflow-y: auto;
        z-index: 20;
        box-shadow: var(--shadow-md);
    }

    .cc-loc-dropdown .location-option {
        padding: 10px 12px;
        cursor: pointer;
        font-size: 12.5px;
        color: var(--c-text);
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.12s ease;
    }

    .cc-loc-dropdown .location-option:last-child { border-bottom: none; }

    .cc-loc-dropdown .location-option:hover {
        background: rgba(241, 205, 134, 0.18);
        color: var(--c-primary);
    }

    .cc-file {
        position: relative;
        border: 1.5px dashed #cfd6dc;
        border-radius: 12px;
        padding: 18px 16px;
        text-align: center;
        background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
        transition: border-color 0.2s ease, background 0.2s ease;
        cursor: pointer;
    }

    .cc-file:hover,
    .cc-file.is-dragover {
        border-color: var(--c-accent);
        background: rgba(241, 205, 134, 0.08);
    }

    .cc-file input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .cc-file__icon {
        width: 40px;
        height: 40px;
        margin: 0 auto 8px;
        border-radius: 10px;
        background: rgba(10, 45, 41, 0.06);
        color: var(--c-primary);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cc-file__icon svg { width: 20px; height: 20px; }

    .cc-file__label {
        font-size: 13px;
        font-weight: 700;
        color: var(--c-primary);
        margin: 0 0 4px;
    }

    .cc-file__hint {
        font-size: 11px;
        color: var(--c-muted);
        margin: 0;
    }

    .cc-file__name {
        display: none;
        margin-top: 10px;
        font-size: 12px;
        font-weight: 600;
        color: var(--c-success);
        word-break: break-all;
    }

    .cc-file.has-file .cc-file__name { display: block; }

    .cc-modal__foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 22px;
        border-top: 1px solid var(--c-border);
        background: #fff;
        flex-shrink: 0;
    }

    .cc-modal__foot-note {
        font-size: 11px;
        color: var(--c-muted);
        line-height: 1.4;
        max-width: 280px;
    }

    .cc-modal__foot-actions {
        display: flex;
        gap: 10px;
        margin-left: auto;
    }

    .cc-btn-cancel {
        padding: 10px 18px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid var(--c-border);
        background: #fff;
        color: var(--c-muted);
        cursor: pointer;
        transition: all 0.15s ease;
        font-family: inherit;
    }

    .cc-btn-cancel:hover {
        color: var(--c-text);
        border-color: #c5cdd6;
        background: #f9fafb;
    }

    .cc-btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 700;
        border: none;
        background: linear-gradient(135deg, var(--c-primary) 0%, #0f3d38 100%);
        color: #fff;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(10, 45, 41, 0.22);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        font-family: inherit;
    }

    .cc-btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(10, 45, 41, 0.28);
    }

    .cc-btn-submit svg { width: 15px; height: 15px; }

    @media (max-width: 600px) {
        .cc-grid { grid-template-columns: 1fr; }
        .cc-grid .span-2 { grid-column: span 1; }
        .cc-modal__foot { flex-direction: column; align-items: stretch; }
        .cc-modal__foot-note { max-width: none; text-align: center; }
        .cc-modal__foot-actions { width: 100%; }
        .cc-btn-cancel, .cc-btn-submit { flex: 1; justify-content: center; }
    }
</style>

<div id="createCandidateModal" class="modal-overlay" onclick="closeOnBackdrop(event, 'createCandidateModal')">
    <div class="modal-box cc-modal" onclick="event.stopPropagation()">
        <div class="cc-modal__head">
            <div class="cc-modal__head-left">
                <div class="cc-modal__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/>
                        <line x1="23" y1="11" x2="17" y2="11"/>
                    </svg>
                </div>
                <div>
                    <h3 class="cc-modal__title">Create New Candidate</h3>
                    <p class="cc-modal__sub">Adds to the database and assigns to <strong>Position #{{ $trackerInfo->id }}</strong>{{ $trackerInfo->position ? ' — ' . $trackerInfo->position : '' }}</p>
                </div>
            </div>
            <button type="button" class="cc-modal__close" onclick="closeCreateCandidateModal()" aria-label="Close">×</button>
        </div>

        <form id="createCandidateForm" class="cc-modal__form" method="POST" action="{{ route('candidates.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tracker_id" value="{{ $trackerInfo->id }}">

            <div class="cc-modal__body">
                {{-- Identity --}}
                <section class="cc-section">
                    <div class="cc-section__head">
                        <span class="cc-section__badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <h4 class="cc-section__title">Basic Information</h4>
                    </div>
                    <div class="cc-grid">
                        <div class="cc-field">
                            <label for="create_full_name">Full Name<span class="req">*</span></label>
                            <input type="text" id="create_full_name" name="full_name" value="{{ old('full_name') }}" required placeholder="e.g. Jane Smith">
                        </div>
                        <div class="cc-field">
                            <label for="create_email">Email<span class="req">*</span></label>
                            <input type="email" id="create_email" name="email" value="{{ old('email') }}" required placeholder="name@email.com">
                        </div>
                        <div class="cc-field">
                            <label for="create_phone">Phone</label>
                            <input type="text" id="create_phone" name="phone" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000">
                        </div>
                        <div class="cc-field">
                            <label for="create_work_status">Work Authorization</label>
                            <select id="create_work_status" name="work_status">
                                <option value="">Select status</option>
                                <option value="GC" @selected(old('work_status') === 'GC')>GC</option>
                                <option value="PR" @selected(old('work_status') === 'PR')>PR</option>
                                <option value="Citizen" @selected(old('work_status') === 'Citizen')>Citizen</option>
                                <option value="H1B" @selected(old('work_status') === 'H1B')>H1B</option>
                                <option value="OPT" @selected(old('work_status') === 'OPT')>OPT</option>
                            </select>
                        </div>
                        <div class="cc-field span-2">
                            <label for="create_current_company">Current Company</label>
                            <input type="text" id="create_current_company" name="current_company" value="{{ old('current_company') }}" placeholder="Employer name">
                        </div>
                    </div>
                </section>

                {{-- Location --}}
                <section class="cc-section">
                    <div class="cc-section__head">
                        <span class="cc-section__badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </span>
                        <h4 class="cc-section__title">Location</h4>
                        <span class="cc-section__hint">Pick from list or enter free text</span>
                    </div>
                    <div class="cc-grid">
                        <div class="cc-field span-2">
                            <label for="location_search">City &amp; State</label>
                            <div class="cc-loc-wrap">
                                <span class="cc-loc-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                </span>
                                <input type="text" id="location_search" placeholder="Search location…" autocomplete="off">
                                <input type="hidden" id="create_location_id" name="location_id" value="{{ old('location_id') }}">
                                <div id="location_dropdown" class="cc-loc-dropdown">
                                    @foreach(\App\Models\Region::orderBy('region', 'asc')->get() as $region)
                                        <div class="location-option" data-value="{{ $region->id }}">
                                            {{ $region->city ? $region->city . ', ' : '' }}{{ $region->region }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="cc-field span-2">
                            <label for="create_location_text">Location (free text)</label>
                            <input type="text" id="create_location_text" name="location_text" value="{{ old('location_text') }}" placeholder="e.g. Hyderabad, India">
                        </div>
                    </div>
                </section>

                {{-- Compensation --}}
                <section class="cc-section">
                    <div class="cc-section__head">
                        <span class="cc-section__badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </span>
                        <h4 class="cc-section__title">Compensation</h4>
                    </div>
                    <div class="cc-grid">
                        <div class="cc-field">
                            <label for="create_pay_rate">Pay Rate</label>
                            <input type="text" id="create_pay_rate" name="pay_rate" value="{{ old('pay_rate') }}" placeholder="e.g. $50/hr">
                        </div>
                        <div class="cc-field">
                            <label for="create_placement_pay_rate">Placement Pay Rate</label>
                            <input type="text" id="create_placement_pay_rate" name="placement_pay_rate" value="{{ old('placement_pay_rate') }}" placeholder="e.g. 30 LPA">
                        </div>
                    </div>
                </section>

                {{-- Notes --}}
                <section class="cc-section">
                    <div class="cc-section__head">
                        <span class="cc-section__badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        </span>
                        <h4 class="cc-section__title">Profile Notes</h4>
                    </div>
                    <div class="cc-grid">
                        <div class="cc-field span-2">
                            <label for="create_summary">Candidate Summary</label>
                            <textarea id="create_summary" name="summary" rows="2" placeholder="Skills, experience, notice period…">{{ old('summary') }}</textarea>
                        </div>
                        <div class="cc-field span-2">
                            <label for="create_remarks">Internal Remarks</label>
                            <textarea id="create_remarks" name="remarks" rows="2" placeholder="Recruiter-only notes…">{{ old('remarks') }}</textarea>
                        </div>
                    </div>
                </section>

                {{-- Agency & Resume --}}
                <section class="cc-section">
                    <div class="cc-section__head">
                        <span class="cc-section__badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        </span>
                        <h4 class="cc-section__title">Agency &amp; Documents</h4>
                    </div>
                    <div class="cc-grid">
                        <div class="cc-field">
                            <label for="create_agency_name">Agency Name</label>
                            <input type="text" id="create_agency_name" name="agency_name" value="{{ old('agency_name') }}" placeholder="Optional">
                        </div>
                        <div class="cc-field">
                            <label for="create_agency_poc">Agency POC</label>
                            <input type="text" id="create_agency_poc" name="agency_poc" value="{{ old('agency_poc') }}" placeholder="Point of contact">
                        </div>
                        <div class="cc-field span-2">
                            <label>Resume</label>
                            <div class="cc-file" id="createResumeDropzone">
                                <input type="file" id="create_resume" name="resume_file" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="cc-file__icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                </div>
                                <p class="cc-file__label">Drop resume here or click to browse</p>
                                <p class="cc-file__hint">PDF, JPG, or PNG — max 5 MB</p>
                                <p class="cc-file__name" id="createResumeFileName"></p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="cc-modal__foot">
                <p class="cc-modal__foot-note">Candidate will be created and immediately assigned to this position.</p>
                <div class="cc-modal__foot-actions">
                    <button type="button" class="cc-btn-cancel" onclick="closeCreateCandidateModal()">Cancel</button>
                    <button type="submit" class="cc-btn-submit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Create &amp; Assign
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const resumeInput = document.getElementById('create_resume');
    const dropzone = document.getElementById('createResumeDropzone');
    const fileNameEl = document.getElementById('createResumeFileName');

    function setResumeLabel(file) {
        if (!dropzone || !fileNameEl) return;
        if (file) {
            dropzone.classList.add('has-file');
            fileNameEl.textContent = file.name;
        } else {
            dropzone.classList.remove('has-file');
            fileNameEl.textContent = '';
        }
    }

    if (resumeInput) {
        resumeInput.addEventListener('change', function () {
            setResumeLabel(this.files && this.files[0] ? this.files[0] : null);
        });
    }

    if (dropzone) {
        ['dragenter', 'dragover'].forEach(evt => {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropzone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropzone.addEventListener(evt, function () {
                dropzone.classList.remove('is-dragover');
            });
        });
        dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            if (e.dataTransfer.files.length && resumeInput) {
                resumeInput.files = e.dataTransfer.files;
                setResumeLabel(e.dataTransfer.files[0]);
            }
        });
    }
})();
</script>
