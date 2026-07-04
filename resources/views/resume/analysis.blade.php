@extends('layouts.app')

@section('title', 'Resume Screening Report')

@section('content')
@php
    $jobDescriptionValue = old('job_description', $jobDescription ?? '');
    $aiStatus = $aiStatus ?? ['available' => false];
    $engineReady = (bool) ($aiStatus['available'] ?? false);
    $hasResults = isset($resumeAnalysis);
@endphp

<style>
    .fit-page {
        max-width: 1280px;
    }

    .fit-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }

    .fit-hero__copy h1 {
        font-size: 28px;
        font-weight: 700;
        color: var(--teal-deep, #0a2d29);
        margin-bottom: 6px;
        letter-spacing: -0.02em;
    }

    .fit-hero__copy p {
        font-size: 14px;
        color: #5f6f6d;
        max-width: 560px;
        line-height: 1.55;
    }

    .engine-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        color: #0a2d29;
        background: #fff;
        border: 1px solid rgba(10, 45, 41, 0.1);
        box-shadow: 0 2px 8px rgba(10, 45, 41, 0.06);
        white-space: nowrap;
    }

    .engine-pill__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #c0392b;
        box-shadow: 0 0 0 4px rgba(192, 57, 43, 0.12);
    }

    .engine-pill--ready .engine-pill__dot {
        background: #1f8f5f;
        box-shadow: 0 0 0 4px rgba(31, 143, 95, 0.12);
    }

    .fit-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
        gap: 22px;
        align-items: start;
    }

    @media (max-width: 992px) {
        .fit-layout {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    .fit-card {
        background: #fff;
        border-radius: 14px;
        padding: 24px;
        border: 1px solid rgba(10, 45, 41, 0.08);
        box-shadow: 0 8px 24px rgba(10, 45, 41, 0.06);
    }

    .fit-card__title {
        font-size: 17px;
        font-weight: 700;
        color: #0a2d29;
        margin-bottom: 6px;
    }

    .fit-card__subtitle {
        font-size: 13px;
        color: #6b7c7a;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .fit-steps {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 22px;
    }

    .fit-step {
        padding: 12px;
        border-radius: 10px;
        background: linear-gradient(135deg, rgba(241, 205, 134, 0.12), rgba(10, 45, 41, 0.04));
        border: 1px solid rgba(241, 205, 134, 0.35);
    }

    .fit-step__num {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #9a7b3f;
        margin-bottom: 4px;
    }

    .fit-step__text {
        font-size: 12px;
        color: #0a2d29;
        line-height: 1.4;
        font-weight: 500;
    }

    .fit-form .form-group {
        margin-bottom: 18px;
    }

    .fit-form label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #0a2d29;
        margin-bottom: 8px;
    }

    .fit-form textarea,
    .fit-form input[type="file"] {
        width: 100%;
        border: 1px solid rgba(10, 45, 41, 0.14);
        border-radius: 10px;
        padding: 12px 14px;
        font-family: inherit;
        font-size: 14px;
        background: #fafcfb;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .fit-form textarea:focus,
    .fit-form input[type="file"]:focus {
        outline: none;
        border-color: rgba(241, 205, 134, 0.9);
        box-shadow: 0 0 0 3px rgba(241, 205, 134, 0.25);
    }

    .fit-form textarea {
        min-height: 220px;
        resize: vertical;
        line-height: 1.55;
    }

    .fit-hint {
        font-size: 12px;
        color: #7a8a88;
        margin-top: 6px;
        line-height: 1.45;
    }

    .fit-submit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 4px;
    }

    .fit-alert {
        padding: 12px 14px;
        border-radius: 10px;
        font-size: 13px;
        margin-bottom: 16px;
        line-height: 1.5;
    }

    .fit-alert--error {
        background: #fdf0f0;
        color: #8a2f2f;
        border: 1px solid #f1c9c9;
    }

    .fit-alert--errors ul {
        margin: 6px 0 0 18px;
    }

    /* Results panel */
    .fit-results__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .fit-score-block {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 18px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(10, 45, 41, 0.04), rgba(241, 205, 134, 0.1));
        border: 1px solid rgba(241, 205, 134, 0.35);
        margin-bottom: 18px;
    }

    .fit-score-ring {
        position: relative;
        width: 88px;
        height: 88px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .fit-score-ring__svg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        transform: rotate(0deg);
    }

    .fit-score-ring__track {
        fill: none;
        stroke: rgba(10, 45, 41, 0.1);
        stroke-width: 6;
    }

    .fit-score-ring__progress {
        fill: none;
        stroke-width: 6;
        stroke-linecap: round;
        transition: stroke-dashoffset 1s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .fit-score-ring--strong .fit-score-ring__progress { stroke: #1f8f5f; }
    .fit-score-ring--good .fit-score-ring__progress { stroke: #0a2d29; }
    .fit-score-ring--borderline .fit-score-ring__progress { stroke: #c9a85c; }
    .fit-score-ring--low .fit-score-ring__progress { stroke: #c0392b; }

    .fit-score-ring__value {
        position: relative;
        z-index: 1;
        font-size: 20px;
        font-weight: 700;
        color: #0a2d29;
        line-height: 1;
    }

    .fit-score-meta h3 {
        font-size: 15px;
        font-weight: 700;
        color: #0a2d29;
        margin-bottom: 4px;
    }

    .fit-score-meta p {
        font-size: 13px;
        color: #6b7c7a;
        margin: 0;
    }

    .fit-section {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid rgba(10, 45, 41, 0.08);
    }

    .fit-section h4 {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #9a7b3f;
        margin-bottom: 10px;
    }

    .fit-section p {
        font-size: 14px;
        line-height: 1.6;
        color: #33413f;
        margin: 0;
    }

    .fit-section ol {
        margin: 0 0 0 20px;
        padding: 0;
    }

    .fit-section li {
        font-size: 14px;
        line-height: 1.55;
        color: #33413f;
        margin-bottom: 8px;
    }

    .fit-recommendation {
        display: inline-block;
        margin-top: 6px;
        padding: 7px 14px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .rec-strong {
        background: rgba(31, 143, 95, 0.12);
        color: #166644;
        border: 1px solid rgba(31, 143, 95, 0.35);
    }

    .rec-good {
        background: rgba(10, 45, 41, 0.08);
        color: #0a2d29;
        border: 1px solid rgba(10, 45, 41, 0.18);
    }

    .rec-borderline {
        background: rgba(241, 205, 134, 0.22);
        color: #7a5e1e;
        border: 1px solid rgba(241, 205, 134, 0.75);
    }

    .rec-low {
        background: rgba(192, 57, 43, 0.1);
        color: #8f2d22;
        border: 1px solid rgba(192, 57, 43, 0.28);
    }

    /* Empty state with logo watermark */
    .fit-placeholder {
        position: relative;
        min-height: 520px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 14px;
        background: linear-gradient(160deg, #ffffff 0%, #f4f8f7 55%, #eef4f2 100%);
        border: 1px dashed rgba(10, 45, 41, 0.14);
    }

    .fit-placeholder__watermark {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        user-select: none;
    }

    .fit-placeholder__watermark img {
        width: min(72%, 340px);
        height: auto;
        opacity: 0.22;
        filter: grayscale(10%);
    }

    .fit-placeholder__content {
        position: relative;
        z-index: 1;
        text-align: center;
        max-width: 360px;
        padding: 28px 24px;
    }

    .fit-placeholder__content h2 {
        font-size: 20px;
        font-weight: 700;
        color: #0a2d29;
        margin-bottom: 10px;
    }

    .fit-placeholder__content p {
        font-size: 14px;
        color: #6b7c7a;
        line-height: 1.6;
        margin-bottom: 16px;
    }

    .fit-placeholder__list {
        list-style: none;
        margin: 0;
        padding: 0;
        text-align: left;
        display: inline-block;
    }

    .fit-placeholder__list li {
        font-size: 13px;
        color: #4f5f5d;
        padding: 6px 0 6px 22px;
        position: relative;
        line-height: 1.45;
    }

    .fit-placeholder__list li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 12px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--gold, #f1cd86);
        box-shadow: 0 0 0 3px rgba(241, 205, 134, 0.25);
    }

    .fit-results__stamp {
        font-size: 11px;
        color: #8a9694;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 600;
    }

    #fitResultsColumn {
        position: relative;
        min-height: 520px;
    }

    .fit-loader {
        position: relative;
        min-height: 520px;
        border-radius: 14px;
        overflow: hidden;
        background: linear-gradient(160deg, #0a2d29 0%, #0f3d37 48%, #134840 100%);
        border: 1px solid rgba(241, 205, 134, 0.2);
        box-shadow: 0 12px 32px rgba(10, 45, 41, 0.18);
        display: none;
    }

    .fit-loader.is-active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fit-loader__watermark {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    .fit-loader__watermark img {
        width: min(65%, 280px);
        opacity: 0.14;
        filter: brightness(1.4);
    }

    .fit-loader__inner {
        position: relative;
        z-index: 1;
        width: min(100%, 420px);
        padding: 32px 28px;
    }

    .fit-loader__title {
        font-size: 20px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 6px;
        letter-spacing: -0.02em;
    }

    .fit-loader__status {
        font-size: 13px;
        color: rgba(255, 255, 255, 0.72);
        margin-bottom: 18px;
        min-height: 20px;
        transition: opacity 0.25s ease;
    }

    .fit-loader__bar {
        height: 6px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        overflow: hidden;
        margin-bottom: 22px;
    }

    .fit-loader__bar-fill {
        height: 100%;
        width: 0%;
        border-radius: inherit;
        background: linear-gradient(90deg, #c9a85c, #f1cd86, #ffe4a8);
        transition: width 0.45s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .fit-loader__bar-fill::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.45), transparent);
        animation: fitLoaderShimmer 1.6s ease-in-out infinite;
    }

    @keyframes fitLoaderShimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .fit-loader__steps {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .fit-loader__step {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.06);
        transition: background 0.25s, border-color 0.25s, opacity 0.25s;
    }

    .fit-loader__step.is-pending {
        opacity: 0.45;
    }

    .fit-loader__step.is-active {
        background: rgba(241, 205, 134, 0.14);
        border-color: rgba(241, 205, 134, 0.55);
        opacity: 1;
        box-shadow: 0 0 18px rgba(241, 205, 134, 0.18);
    }

    .fit-loader__step.is-done {
        opacity: 1;
        background: rgba(31, 143, 95, 0.12);
        border-color: rgba(31, 143, 95, 0.35);
        box-shadow: 0 0 0 1px rgba(31, 143, 95, 0.08);
    }

    .fit-loader__step-icon {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        border: 2px solid rgba(255, 255, 255, 0.25);
        color: rgba(255, 255, 255, 0.5);
        margin-top: 1px;
    }

    .fit-loader__step.is-active .fit-loader__step-icon {
        border-color: transparent;
        background: transparent;
    }

    .fit-loader__step.is-done .fit-loader__step-icon {
        background: rgba(31, 143, 95, 0.25);
        border-color: #1f8f5f;
        color: #8fe0b8;
    }

    .fit-loader__step-spinner {
        width: 18px;
        height: 18px;
        border: 2px solid rgba(241, 205, 134, 0.25);
        border-top-color: #f1cd86;
        border-radius: 50%;
        animation: fitLoaderSpin 0.75s linear infinite;
    }

    @keyframes fitLoaderSpin {
        to { transform: rotate(360deg); }
    }

    .fit-loader__step-body {
        min-width: 0;
    }

    .fit-loader__step-label {
        font-size: 13px;
        font-weight: 600;
        color: #fff;
        line-height: 1.35;
    }

    .fit-loader__step.is-pending .fit-loader__step-label {
        color: rgba(255, 255, 255, 0.55);
    }

    .fit-loader__step-hint {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.5);
        margin-top: 2px;
        line-height: 1.35;
    }

    .fit-loader__step.is-active .fit-loader__step-hint {
        color: rgba(241, 205, 134, 0.85);
    }

    #fitResultsPanel.is-hidden {
        display: none;
    }

    .fit-report-card {
        animation: fitReportIn 0.45s ease;
    }

    @keyframes fitReportIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="fit-page">
    <div class="fit-hero">
        <div class="fit-hero__copy">
            <h1>Resume Screening Report</h1>
            <p>
                Compare a candidate resume against your job description and receive a structured match assessment
                with match score, strengths, gaps, and a clear hiring recommendation.
            </p>
        </div>
        <div class="engine-pill {{ $engineReady ? 'engine-pill--ready' : '' }}">
            <span class="engine-pill__dot"></span>
            <span>RADiiX Intelligence · {{ $engineReady ? 'Ready' : 'Unavailable' }}</span>
        </div>
    </div>

    @if (session('error'))
        <div class="fit-alert fit-alert--error" id="fitPageError">
            {{ session('error') }}
        </div>
    @else
        <div class="fit-alert fit-alert--error" id="fitPageError" style="display: none;"></div>
    @endif

    <div class="fit-layout">
        <div>
            <div class="fit-card">
                <div class="fit-card__title">New analysis</div>
                <p class="fit-card__subtitle">
                    Paste the full job description and upload the candidate resume to generate a recruiter-ready assessment.
                </p>

                <div class="fit-steps">
                    <div class="fit-step">
                        <div class="fit-step__num">Step 1</div>
                        <div class="fit-step__text">Paste the job description</div>
                    </div>
                    <div class="fit-step">
                        <div class="fit-step__num">Step 2</div>
                        <div class="fit-step__text">Upload the resume (PDF)</div>
                    </div>
                    <div class="fit-step">
                        <div class="fit-step__num">Step 3</div>
                        <div class="fit-step__text">Review the match assessment</div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="fit-alert fit-alert--error fit-alert--errors">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="fit-form" method="POST" action="{{ route('resume.analysis.analyze') }}" enctype="multipart/form-data" id="fitAnalysisForm">
                    @csrf

                    <div class="form-group">
                        <label for="job_description">Job description</label>
                        <textarea id="job_description" name="job_description" rows="10" required placeholder="Include role title, required skills, experience, and must-have qualifications…">{{ $jobDescriptionValue }}</textarea>
                        <div class="fit-hint">
                            Tip: Include a clear Skills section and Must Haves for the most accurate match scoring.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="resume">Candidate resume</label>
                        <input type="file" id="resume" name="resume" accept="application/pdf" required>
                        <div class="fit-hint">
                            PDF format only. Maximum file size 5 MB.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary fit-submit" id="fitSubmitBtn">
                        Generate match assessment
                    </button>
                </form>
            </div>
        </div>

        <div id="fitResultsColumn">
            <div class="fit-loader" id="fitLoader" aria-live="polite" aria-busy="false">
                <div class="fit-loader__watermark" aria-hidden="true">
                    <img src="{{ asset('logo.png') }}" alt="">
                </div>
                <div class="fit-loader__inner">
                    <div class="fit-loader__title">Generating match assessment</div>
                    <p class="fit-loader__status" id="fitLoaderStatus">Starting analysis…</p>
                    <div class="fit-loader__bar">
                        <div class="fit-loader__bar-fill" id="fitLoaderBar"></div>
                    </div>
                    <ul class="fit-loader__steps" id="fitLoaderSteps"></ul>
                </div>
            </div>

            <div id="fitResultsPanel" class="{{ $hasResults ? '' : 'is-hidden' }}">
                @if ($hasResults && !empty($analysisSections))
                    @include('resume._fit_report', ['analysisSections' => $analysisSections])
                @elseif ($hasResults)
                    <div class="fit-card">
                        <div class="fit-section" style="margin-top: 0; padding-top: 0; border-top: none;">
                            <p style="white-space: pre-wrap;">{{ $resumeAnalysis }}</p>
                        </div>
                    </div>
                @endif
            </div>

            @if (! $hasResults)
                <div class="fit-placeholder" id="fitPlaceholder">
                    <div class="fit-placeholder__watermark" aria-hidden="true">
                        <img src="{{ asset('logo.png') }}" alt="">
                    </div>
                    <div class="fit-placeholder__content">
                        <h2>Your match assessment will appear here</h2>
                        <p>
                            Submit a job description and resume on the left to generate a structured candidate assessment.
                        </p>
                        <ul class="fit-placeholder__list">
                            <li>Match percentage against role requirements</li>
                            <li>Evidence-based strengths and gaps</li>
                            <li>Clear proceed / hold / pass recommendation</li>
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
        const form = document.getElementById('fitAnalysisForm');
        const btn = document.getElementById('fitSubmitBtn');
        const loader = document.getElementById('fitLoader');
        const loaderStatus = document.getElementById('fitLoaderStatus');
        const loaderBar = document.getElementById('fitLoaderBar');
        const loaderSteps = document.getElementById('fitLoaderSteps');
        const resultsPanel = document.getElementById('fitResultsPanel');
        const placeholder = document.getElementById('fitPlaceholder');
        const pageError = document.getElementById('fitPageError');

        const PIPELINE = [
            { id: 'upload', label: 'Receiving documents', hint: 'Uploading job description and resume' },
            { id: 'extract', label: 'Reading resume document', hint: 'Extracting text from PDF' },
            { id: 'score', label: 'Evaluating requirements match', hint: 'Scoring skills and must-haves' },
            { id: 'narrative', label: 'Generating match assessment', hint: 'Building recruiter narrative' },
            { id: 'finalize', label: 'Preparing your report', hint: 'Formatting final output' },
        ];

        if (!form || !btn || !loader) return;

        function initFitScoreRings(root) {
            const scope = root || document;
            scope.querySelectorAll('.fit-score-ring').forEach(function (ring) {
                const progress = ring.querySelector('.fit-score-ring__progress');
                if (!progress || progress.dataset.animated === '1') {
                    return;
                }

                const target = progress.getAttribute('data-target-offset');
                requestAnimationFrame(function () {
                    progress.style.strokeDashoffset = target;
                    progress.dataset.animated = '1';
                });
            });
        }

        initFitScoreRings(document.getElementById('fitResultsPanel'));

        let narrativeCreepTimer = null;
        let narrativeCreepPercent = 52;

        function buildStepList() {
            loaderSteps.innerHTML = PIPELINE.map(function (step, index) {
                return '<li class="fit-loader__step is-pending" data-step="' + step.id + '">' +
                    '<div class="fit-loader__step-icon"><span>' + (index + 1) + '</span></div>' +
                    '<div class="fit-loader__step-body">' +
                    '<div class="fit-loader__step-label">' + step.label + '</div>' +
                    '<div class="fit-loader__step-hint">' + step.hint + '</div>' +
                    '</div></li>';
            }).join('');
        }

        function updateStepUI(stepId, percent) {
            const order = PIPELINE.map(function (s) { return s.id; });
            let activeIndex = order.indexOf(stepId);
            if (stepId === 'done') {
                activeIndex = order.length;
            }
            if (stepId === 'waiting') {
                activeIndex = -1;
            }

            loaderBar.style.width = Math.max(3, Math.min(100, percent)) + '%';

            loaderSteps.querySelectorAll('.fit-loader__step').forEach(function (el) {
                const id = el.getAttribute('data-step');
                const idx = order.indexOf(id);
                const icon = el.querySelector('.fit-loader__step-icon');
                const stepMeta = PIPELINE[idx];

                el.classList.remove('is-pending', 'is-active', 'is-done');

                if (stepId === 'done' || idx < activeIndex) {
                    el.classList.add('is-done');
                    icon.innerHTML = '&#10003;';
                } else if (idx === activeIndex) {
                    el.classList.add('is-active');
                    icon.innerHTML = '<span class="fit-loader__step-spinner"></span>';
                    if (stepMeta) {
                        loaderStatus.textContent = stepMeta.label + '…';
                    }
                } else {
                    el.classList.add('is-pending');
                    icon.innerHTML = '<span>' + (idx + 1) + '</span>';
                }
            });

            if (stepId === 'done') {
                loaderStatus.textContent = 'Report ready';
                loaderBar.style.width = '100%';
            }
        }

        function stopNarrativeCreep() {
            if (narrativeCreepTimer) {
                clearInterval(narrativeCreepTimer);
                narrativeCreepTimer = null;
            }
        }

        function startNarrativeCreep(fromPercent) {
            stopNarrativeCreep();
            narrativeCreepPercent = fromPercent || 52;

            narrativeCreepTimer = setInterval(function () {
                if (narrativeCreepPercent < 88) {
                    narrativeCreepPercent += 0.55;
                    loaderBar.style.width = Math.round(narrativeCreepPercent) + '%';
                }
            }, 700);
        }

        function handleProgressEvent(event) {
            if (!event || event.type !== 'progress') return;

            updateStepUI(event.step, event.percent || 5);

            if (event.label) {
                loaderStatus.textContent = event.label;
            }

            if (event.step === 'narrative') {
                startNarrativeCreep(event.percent || 52);
            } else {
                stopNarrativeCreep();
            }
        }

        function readStreamedResponse(response) {
            if (!response.body) {
                return Promise.reject(new Error('Streaming is not supported in this browser.'));
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';
            let completePayload = null;
            let streamError = null;

            function processLine(line) {
                const trimmed = line.trim();
                if (!trimmed) return;

                let event;
                try {
                    event = JSON.parse(trimmed);
                } catch (e) {
                    return;
                }

                if (event.type === 'progress') {
                    handleProgressEvent(event);
                } else if (event.type === 'complete') {
                    stopNarrativeCreep();
                    completePayload = event;
                } else if (event.type === 'error') {
                    stopNarrativeCreep();
                    streamError = new Error(event.message || 'Analysis failed. Please try again.');
                }
            }

            function pump() {
                return reader.read().then(function (chunk) {
                    if (chunk.done) {
                        if (buffer.trim()) {
                            processLine(buffer);
                        }

                        if (streamError) {
                            throw streamError;
                        }

                        if (!completePayload) {
                            throw new Error('Analysis ended before the report was ready. Please try again.');
                        }

                        return completePayload;
                    }

                    buffer += decoder.decode(chunk.value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop() || '';

                    for (let i = 0; i < lines.length; i++) {
                        processLine(lines[i]);
                        if (streamError) {
                            throw streamError;
                        }
                    }

                    return pump();
                });
            }

            return pump();
        }

        function showLoader() {
            buildStepList();
            updateStepUI('upload', 8);
            loaderStatus.textContent = 'Receiving documents…';
            if (placeholder) placeholder.style.display = 'none';
            if (resultsPanel) {
                resultsPanel.classList.add('is-hidden');
                resultsPanel.innerHTML = '';
            }
            loader.classList.add('is-active');
            loader.setAttribute('aria-busy', 'true');
        }

        function hideLoader() {
            stopNarrativeCreep();
            loader.classList.remove('is-active');
            loader.setAttribute('aria-busy', 'false');
        }

        function showError(message) {
            if (!pageError) return;
            pageError.textContent = message;
            pageError.style.display = 'block';
            pageError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function clearError() {
            if (!pageError) return;
            pageError.textContent = '';
            pageError.style.display = 'none';
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearError();
            stopNarrativeCreep();

            const formData = new FormData(form);

            btn.disabled = true;
            btn.textContent = 'Generating report…';
            showLoader();

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/x-ndjson',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Progress-Stream': '1',
                },
            })
                .then(function (response) {
                    if (!response.ok) {
                        return response.json()
                            .catch(function () { return {}; })
                            .then(function (data) {
                                const validationMsg = data.errors
                                    ? Object.values(data.errors).flat().join(' ')
                                    : null;
                                throw new Error(validationMsg || data.message || 'Analysis failed. Please try again.');
                            });
                    }

                    return readStreamedResponse(response);
                })
                .then(function (result) {
                    updateStepUI('done', 100);

                    return new Promise(function (resolve) {
                        setTimeout(resolve, 450);
                    }).then(function () {
                        hideLoader();
                        if (resultsPanel) {
                            resultsPanel.innerHTML = result.html;
                            resultsPanel.classList.remove('is-hidden');
                            initFitScoreRings(resultsPanel);
                        }
                        if (placeholder) placeholder.style.display = 'none';
                        resultsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                })
                .catch(function (err) {
                    hideLoader();
                    if (placeholder && resultsPanel && !resultsPanel.innerHTML.trim()) {
                        placeholder.style.display = 'flex';
                    }
                    showError(err.message || 'Something went wrong. Please try again.');
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.textContent = 'Generate match assessment';
                });
        });
    })();
</script>
@endsection
