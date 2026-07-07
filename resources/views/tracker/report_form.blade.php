@extends('layouts.app')

@section('title', 'Generate Submission Report — ' . $trackerCandidate->candidate->full_name)

@section('content')
@php
    $candidate  = $trackerCandidate->candidate;
    $pipeline   = $trackerCandidate->pipelineStatus;
    $stageColors = [
        2=>'#0EA5E9',3=>'#0EA5E9',4=>'#0EA5E9',5=>'#0EA5E9',
        6=>'#F59E0B',7=>'#F59E0B',8=>'#F59E0B',
        9=>'#8B5CF6',10=>'#8B5CF6',11=>'#8B5CF6',
        12=>'#6366F1',13=>'#10B981',14=>'#10B981',
        15=>'#10B981',16=>'#10B981',17=>'#059669',18=>'#EF4444',
    ];
    $stageColor = $stageColors[$trackerCandidate->current_status_id ?? 2] ?? '#6B7280';

    $skillFields = [
        ['key' => 'communication',   'label' => 'Communication Skills',    'icon' => 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z',    'hint' => 'Clarity, articulation, active listening, confidence'],
        ['key' => 'technical',       'label' => 'Technical Proficiency',   'icon' => 'M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18', 'hint' => 'Domain knowledge, tools, relevant experience'],
        ['key' => 'problem_approach', 'label' => 'Professional Approach',         'icon' => 'M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18', 'hint' => ' Attitude, Behavioural Traits, etc.'],
    ];

    $scoreColors = [
        1=>'#EF4444',2=>'#EF4444',3=>'#F97316',4=>'#F97316',5=>'#EAB308',
        6=>'#84CC16',7=>'#22C55E',8=>'#10B981',9=>'#059669',10=>'#047857',
    ];
@endphp

<style>
    :root {
        --c-primary: #0a2d29;
        --c-accent:  #f1cd86;
        --c-accent-d:#c9a84c;
        --c-border:  #e5e7eb;
        --c-text:    #111827;
        --c-muted:   #6b7280;
        --c-surface: #ffffff;
        --radius:    10px;
        --shadow-sm: 0 1px 3px rgba(0,0,0,.08);
        --shadow-md: 0 4px 12px rgba(0,0,0,.10);
    }

    .rp-page { max-width: 860px; margin: 0 auto; padding-bottom: 60px; }

    /* ── Header ── */
    .rp-header { display:flex; align-items:center; justify-content:space-between; padding:18px 0 16px; border-bottom:1px solid var(--c-border); margin-bottom:22px; flex-wrap:wrap; gap:12px; }
    .breadcrumb { display:flex; align-items:center; gap:8px; font-size:13px; color:var(--c-muted); }
    .breadcrumb a { color:var(--c-primary); text-decoration:none; font-weight:600; }
    .breadcrumb a:hover { text-decoration:underline; }
    .breadcrumb-sep { color:var(--c-border); }

    /* ── Candidate hero ── */
    .candidate-hero { background:linear-gradient(135deg,var(--c-primary) 0%,#0d3d38 100%); border-radius:var(--radius); padding:20px 24px; display:flex; align-items:center; gap:18px; margin-bottom:20px; flex-wrap:wrap; }
    .hero-avatar { width:52px; height:52px; border-radius:50%; background:rgba(241,205,134,.15); border:2px solid rgba(241,205,134,.35); display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:800; color:var(--c-accent); flex-shrink:0; }
    .hero-info { flex:1; min-width:0; }
    .hero-name { font-size:18px; font-weight:800; color:#fff; line-height:1.2; }
    .hero-meta { font-size:12px; color:rgba(255,255,255,.55); margin-top:5px; display:flex; gap:14px; flex-wrap:wrap; }
    .hero-meta span { display:flex; align-items:center; gap:4px; }
    .hero-right { text-align:right; flex-shrink:0; }
    .hero-stage { font-size:11px; font-weight:700; padding:5px 12px; border-radius:20px; background:rgba(255,255,255,.1); color:rgba(255,255,255,.9); border:1px solid rgba(255,255,255,.2); }
    .hero-job { font-size:11px; color:rgba(255,255,255,.45); margin-top:4px; }

    /* ── Card ── */
    .card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--radius); box-shadow:var(--shadow-sm); margin-bottom:16px; overflow:hidden; }
    .card-header { padding:13px 20px; border-bottom:1px solid var(--c-border); background:#f9fafb; display:flex; align-items:center; gap:10px; }
    .card-header h2 { font-size:13.5px; font-weight:700; color:var(--c-primary); margin:0; display:flex; align-items:center; gap:7px; }
    .card-header h2 svg { width:15px; height:15px; opacity:.8; }
    .card-header .ch-sub { font-size:11.5px; color:var(--c-muted); margin-left:auto; }
    .card-body { padding:20px; }

    /* ── Pipeline checklist ── */
    .pipeline-checklist { display:grid; grid-template-columns:1fr 1fr; gap:7px; }
    .pcheck-item { display:flex; align-items:center; gap:8px; font-size:12px; color:var(--c-muted); padding:7px 10px; border-radius:7px; background:#f9fafb; border:1px solid var(--c-border); }
    .pcheck-item.done { background:#f0fdf4; border-color:#A7F3D0; color:#065F46; }
    .pcheck-dot { width:7px; height:7px; border-radius:50%; background:var(--c-border); flex-shrink:0; }
    .pcheck-item.done .pcheck-dot { background:#10B981; }
    .pcheck-label { flex:1; font-weight:500; }
    .pcheck-val { font-size:10.5px; color:#6EE7B7; font-style:italic; }

    /* ── Skill assessment cards ── */
    .skill-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .skill-card { border:1px solid var(--c-border); border-radius:8px; overflow:hidden; transition:border-color .2s, box-shadow .2s; }
    .skill-card:focus-within { border-color:var(--c-primary); box-shadow:0 0 0 3px rgba(10,45,41,.07); }
    .skill-card-head { padding:10px 14px; background:#f9fafb; border-bottom:1px solid var(--c-border); display:flex; align-items:center; gap:8px; }
    .skill-card-head svg { width:14px; height:14px; color:var(--c-primary); flex-shrink:0; }
    .skill-card-head .skill-label { font-size:12.5px; font-weight:700; color:var(--c-primary); }
    .skill-card-head .skill-hint { font-size:10.5px; color:var(--c-muted); margin-left:auto; }
    .skill-card-body { padding:12px 14px; display:flex; flex-direction:column; gap:10px; }

    /* Score selector */
    .score-row { display:flex; align-items:center; gap:8px; }
    .score-label { font-size:11px; font-weight:600; color:var(--c-muted); white-space:nowrap; }
    .score-dots { display:flex; gap:4px; flex:1; }
    .score-dot {
        width:28px; height:28px; border-radius:6px; border:1.5px solid var(--c-border);
        background:#f9fafb; cursor:pointer; font-size:11px; font-weight:700;
        color:var(--c-muted); display:flex; align-items:center; justify-content:center;
        transition:all .15s; flex-shrink:0;
    }
    .score-dot:hover { border-color:var(--c-primary); color:var(--c-primary); background:#f0fdf4; }
    .score-dot.active { color:#fff; border-color:transparent; }
    .score-display { font-size:18px; font-weight:800; min-width:36px; text-align:center; transition:color .2s; }

    /* Hidden score input */
    .score-hidden { display:none; }

    .skill-notes {
        width:100%; padding:7px 10px; border:1px solid var(--c-border); border-radius:6px;
        font-size:12px; color:var(--c-text); background:#fff; outline:none;
        resize:vertical; min-height:56px; font-family:inherit; line-height:1.5;
        transition:border-color .15s;
    }
    .skill-notes:focus { border-color:var(--c-primary); box-shadow:0 0 0 2px rgba(10,45,41,.06); }
    .skill-notes::placeholder { color:#9CA3AF; }

    /* Overall recommendation */
    .overall-box { background:#f9fafb; border:1px solid var(--c-border); border-radius:8px; overflow:hidden; }
    .overall-box-head { padding:10px 14px; border-bottom:1px solid var(--c-border); font-size:12.5px; font-weight:700; color:var(--c-primary); display:flex; align-items:center; gap:7px; }
    .overall-box-head svg { width:14px; height:14px; }
    .overall-notes { width:100%; padding:12px 14px; border:none; border-radius:0; font-size:13px; color:var(--c-text); background:transparent; outline:none; resize:vertical; min-height:80px; font-family:inherit; line-height:1.6; }
    .overall-notes::placeholder { color:#9CA3AF; }

    /* Form basics */
    .form-group { display:flex; flex-direction:column; gap:5px; margin-bottom:14px; }
    .form-group:last-child { margin-bottom:0; }
    .form-group label { font-size:12px; font-weight:700; color:var(--c-primary); }
    .form-group label .opt { font-weight:400; color:var(--c-muted); }
    .form-group input, .form-group textarea { padding:9px 12px; border:1px solid var(--c-border); border-radius:7px; font-size:13px; color:var(--c-text); background:#fff; outline:none; transition:border-color .15s; width:100%; box-sizing:border-box; font-family:inherit; }
    .form-group input:focus, .form-group textarea:focus { border-color:var(--c-primary); box-shadow:0 0 0 3px rgba(10,45,41,.08); }
    .form-group textarea { resize:vertical; }
    .field-hint { font-size:11px; color:var(--c-muted); }

    /* Buttons */
    .btn-i { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:7px; font-size:13px; font-weight:700; cursor:pointer; border:none; text-decoration:none; transition:all .15s; white-space:nowrap; font-family:inherit; }
    .btn-i svg { width:15px; height:15px; }
    .btn-secondary-i { background:var(--c-surface); color:var(--c-text); border:1px solid var(--c-border); }
    .btn-secondary-i:hover { background:#f9fafb; }
    .btn-generate { background:var(--c-accent); color:var(--c-primary); font-size:14px; padding:12px 28px; }
    .btn-generate:hover { background:var(--c-accent-d); box-shadow:var(--shadow-md); }

    /* Candidate overview card */
    .overview-field { margin-bottom:18px; }
    .overview-field:last-child { margin-bottom:0; }
    .overview-field label { font-size:12.5px; font-weight:700; color:var(--c-primary); display:flex; align-items:center; gap:6px; margin-bottom:6px; }
    .overview-field label svg { width:14px; height:14px; opacity:.75; }
    .overview-field .opt { font-weight:400; color:var(--c-muted); }
    .overview-field textarea {
        width:100%; padding:10px 13px; border:1.5px solid var(--c-border); border-radius:7px;
        font-size:13px; color:var(--c-text); background:#fff; outline:none;
        resize:vertical; font-family:inherit; line-height:1.6;
        transition:border-color .15s; box-sizing:border-box;
    }
    .overview-field textarea:focus { border-color:var(--c-primary); box-shadow:0 0 0 3px rgba(10,45,41,.07); }
    .overview-field textarea::placeholder { color:#9CA3AF; }
    .overview-notice { font-size:12px; color:var(--c-muted); line-height:1.5; margin-bottom:18px; padding:9px 12px; background:#f9fafb; border-radius:7px; border:1px solid var(--c-border); }

    /* Submit bar */
    .submit-bar { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--radius); padding:18px 22px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; box-shadow:var(--shadow-md); }
    .submit-bar-info strong { display:block; font-size:14px; color:var(--c-text); margin-bottom:2px; }
    .submit-bar-info span { font-size:12px; color:var(--c-muted); }

    @media (max-width:640px) {
        .pipeline-checklist { grid-template-columns:1fr; }
        .skill-grid { grid-template-columns:1fr; }
        .candidate-hero { flex-direction:column; align-items:flex-start; }
        .hero-right { text-align:left; }
        .score-dot { width:24px; height:24px; font-size:10px; }
    }
</style>

<div class="rp-page">

    {{-- Header --}}
    <div class="rp-header">
        <div class="breadcrumb">
            <a href="{{ route('tracker.index') }}">Tracker</a>
            <span class="breadcrumb-sep">/</span>
            <a href="{{ route('tracker.info', $trackerInfo->id) }}">#{{ $trackerInfo->id }} — {{ $trackerInfo->position }}</a>
            <span class="breadcrumb-sep">/</span>
            <span style="color:var(--c-text);font-weight:600;">Submission Report</span>
        </div>
        <a href="{{ route('tracker.info', $trackerInfo->id) }}" class="btn-i btn-secondary-i" style="font-size:12px;padding:7px 14px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Back
        </a>
    </div>

    {{-- Candidate hero --}}
    <div class="candidate-hero">
        <div class="hero-avatar">{{ strtoupper(substr($candidate->full_name, 0, 1)) }}</div>
        <div class="hero-info">
            <div class="hero-name">{{ $candidate->full_name }}</div>
            <div class="hero-meta">
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    {{ $candidate->email }}
                </span>
                @if($candidate->phone)
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13 19.79 19.79 0 0 1 1.61 4.18 2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.29 6.29l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    {{ $candidate->phone }}
                </span>
                @endif
                @if($candidate->work_status)
                <span>Auth: {{ $candidate->work_status }}</span>
                @endif
                @if($candidate->pay_rate)
                <span>Rate: {{ $candidate->pay_rate }}</span>
                @endif
            </div>
        </div>
        <div class="hero-right">
            <div class="hero-stage">{{ $trackerCandidate->status->status ?? 'Identified' }}</div>
            <div class="hero-job">{{ $trackerInfo->position }} @ {{ $trackerInfo->client->client ?? 'Client' }}</div>
        </div>
    </div>

    <form id="reportForm" method="POST"
          action="{{ route('tracker.candidates.report.generate', ['tracker_id' => $trackerInfo->id, 'tracker_candidate_id' => $trackerCandidate->id]) }}">
        @csrf

        {{-- 1. Candidate Overview (shown first in the report) --}}
        <div class="card">
            <div class="card-header">
                <h2>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.58-7 8-7s8 3 8 7"/></svg>
                    Candidate Overview
                </h2>
                <span class="ch-sub">Appears first in the report — highly recommended</span>
            </div>
            <div class="card-body">
                <p class="overview-notice">
                    This section appears at the very top of the Word report, giving the client an immediate snapshot before all the details. Fill in as much or as little as you like — any blank field is simply omitted.
                </p>

                <div class="overview-field">
                    <label>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Brief Summary <span class="opt">— who is this candidate?</span>
                    </label>
                    <textarea name="candidate_summary" rows="3"
                              placeholder="e.g. John is a Senior Cloud Architect with 9+ years of experience in AWS and multi-cloud environments. He brings strong hands-on delivery experience across enterprise-scale infrastructure projects…">{{ old('candidate_summary') }}</textarea>
                </div>

                <div class="overview-field">
                    <label>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Strong Points <span class="opt">— key differentiators of this candidate</span>
                    </label>
                    <textarea name="candidate_strong_points" rows="3"
                              placeholder="e.g. Deep expertise in Terraform and Kubernetes, proven track record of leading cloud migrations, excellent stakeholder communication, AWS Certified Solutions Architect…">{{ old('candidate_strong_points') }}</textarea>
                </div>

                <div class="overview-field">
                    <label>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8a2 2 0 0 0-2 2v2h12V5a2 2 0 0 0-2-2z"/></svg>
                        Must-Have &amp; Desired Skills vs. JD <span class="opt">— how does the candidate map to the role?</span>
                    </label>
                    <textarea name="candidate_jd_skills" rows="4"
                              placeholder="Must-have: AWS (✓ 9 yrs), Terraform (✓ 6 yrs), Kubernetes (✓ 5 yrs), CI/CD pipelines (✓)&#10;Desired: Azure experience (partial — 2 yrs), FinOps (not held), Security certifications (in progress)…">{{ old('candidate_jd_skills') }}</textarea>
                </div>
            </div>
        </div>

        {{-- 2. Pipeline Snapshot --}}
        <div class="card">
            <div class="card-header">
                <h2>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    Recruitment Pipeline Snapshot
                </h2>
                <span class="ch-sub">Auto-pulled — included in report</span>
            </div>
            <div class="card-body">
                <div class="pipeline-checklist">
                    @php
                        use App\Models\JobStatus;
                        $js = fn (int $id, string $fb = '') => JobStatus::labelFor($id, $fb);
                        $checks = [
                            [$js(2, 'Candidate Identified'),          true,                                                                                    null],
                            [$js(3, 'Resume Reviewed'),               $pipeline && $pipeline->resume_reviewed_by_recruiter === 'Completed',                   $pipeline?->resume_reviewed_date?->format('d M Y')],
                            [$js(4, 'Screening Call'),      $pipeline && $pipeline->recruiter_screening_call === 'Completed',                        $pipeline?->recruiter_screening_call_date?->format('d M Y')],
                            [$js(5, 'Shortlisted'),         $pipeline && $pipeline->candidate_shortlisted,                                          null],
                            [$js(6, 'Submitted to client'),    $pipeline && $pipeline->resume_submitted_to_client === 'Submitted',                      null],
                            [$js(7, 'Internal Prep'),       $pipeline && in_array($pipeline->radix_internal_interview_prep, ['Completed','Not Required']), $pipeline?->radix_internal_interview_prep_date?->format('d M Y')],
                            [$js(8, 'Client Review'),          $pipeline && $pipeline->client_resume_review === 'Approved',                             null],
                            [$js(9, 'Round 1'),             $pipeline && $pipeline->client_interview_round_1_date,                                   $pipeline?->client_interview_round_1_date?->format('d M Y')],
                            [$js(10, 'Round 2'),             $pipeline && $pipeline->client_interview_round_2_date,                                   $pipeline?->client_interview_round_2_date?->format('d M Y')],
                            [$js(12, 'Client Decision Awaited'),       $pipeline && $pipeline->client_decision,                                                 $pipeline?->client_decision],
                            [$js(14, 'Offer Extended to Candidate'),                $pipeline && $pipeline->offer_extended_to_candidate,                                     $pipeline?->offer_extended_date?->format('d M Y')],
                            [$js(15, 'Background Check'),    $pipeline && $pipeline->background_check === 'Completed',                                null],
                        ];
                    @endphp
                    @foreach($checks as [$label, $done, $val])
                        <div class="pcheck-item {{ $done ? 'done' : '' }}">
                            <span class="pcheck-dot"></span>
                            <span class="pcheck-label">{{ $label }}</span>
                            @if($val)<span class="pcheck-val">{{ $val }}</span>@endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 3. Skills Assessment --}}
        <div class="card">
            <div class="card-header">
                <h2>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    Skills &amp; Communication Assessment
                </h2>
                <span class="ch-sub">Based on interview — optional</span>
            </div>
            <div class="card-body">
                <p style="font-size:12.5px;color:var(--c-muted);margin-bottom:16px;line-height:1.5;">
                    Rate each dimension based on the interview. Scores and notes will appear as a structured assessment section in the report. Leave blank to skip the section entirely.
                </p>

                <div class="skill-grid">
                    @foreach($skillFields as $sf)
                        <div class="skill-card">
                            <div class="skill-card-head">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $sf['icon'] }}"/></svg>
                                <span class="skill-label">{{ $sf['label'] }}</span>
                                <span class="skill-hint">{{ $sf['hint'] }}</span>
                            </div>
                            <div class="skill-card-body">
                                <div class="score-row">
                                    <span class="score-label">Score</span>
                                    <div class="score-dots" id="dots_{{ $sf['key'] }}">
                                        @for($n = 1; $n <= 10; $n++)
                                            <div class="score-dot" data-score="{{ $n }}" data-field="{{ $sf['key'] }}" onclick="setScore('{{ $sf['key'] }}', {{ $n }})">{{ $n }}</div>
                                        @endfor
                                    </div>
                                    <span class="score-display" id="display_{{ $sf['key'] }}" style="color:var(--c-border);">—</span>
                                    <input type="hidden" name="skill_{{ $sf['key'] }}_score" id="score_{{ $sf['key'] }}" value="">
                                </div>
                                <textarea class="skill-notes"
                                          name="skill_{{ $sf['key'] }}_notes"
                                          placeholder="Brief notes on {{ strtolower($sf['label']) }}…"
                                          rows="2"></textarea>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Overall recommendation --}}
                <div class="overall-box" style="margin-top:14px;">
                    <div class="overall-box-head">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        Overall Recommendation
                    </div>
                    <textarea class="overall-notes" name="overall_recommendation"
                              placeholder="Summarize the candidate's overall suitability and give a clear hiring recommendation for this role…"></textarea>
                </div>
            </div>
        </div>

        {{-- 4. Recruiter Details & Notes --}}
        <div class="card">
            <div class="card-header">
                <h2>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Recruiter Details &amp; Notes
                </h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Submitted by <span class="opt">— appears in the report signature</span></label>
                    <input type="text" name="recruiter_name"
                           placeholder="{{ $trackerInfo->leadRecruiter->username ?? 'Your name' }}"
                           value="{{ old('recruiter_name', $trackerInfo->leadRecruiter->username ?? '') }}">
                </div>
                <div class="form-group">
                    <label>Additional Notes <span class="opt">— optional context for the client</span></label>
                    <textarea name="additional_notes" rows="4"
                              placeholder="Anything else the client should know — special strengths, availability, context…">{{ old('additional_notes') }}</textarea>
                    <span class="field-hint">Appears as a dedicated section at the end of the report.</span>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="submit-bar">
            <div class="submit-bar-info">
                <strong>Ready to generate</strong>
                <span>Downloads instantly as a Word (.docx) document — ready to send to the client.</span>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <a href="{{ route('tracker.info', $trackerInfo->id) }}" class="btn-i btn-secondary-i">Cancel</a>
                <button type="submit" class="btn-i btn-generate">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Generate &amp; Download
                </button>
            </div>
        </div>

    </form>
</div>

<script>
const scoreColors = {
    1:'#EF4444',2:'#EF4444',3:'#F97316',4:'#F97316',5:'#EAB308',
    6:'#84CC16',7:'#22C55E',8:'#10B981',9:'#059669',10:'#047857'
};

function setScore(field, score) {
    // Update hidden input
    document.getElementById('score_' + field).value = score;

    // Update display number
    const display = document.getElementById('display_' + field);
    display.textContent = score;
    display.style.color = scoreColors[score];

    // Update dot states
    document.querySelectorAll('#dots_' + field + ' .score-dot').forEach(dot => {
        const n = parseInt(dot.dataset.score);
        if (n <= score) {
            dot.classList.add('active');
            dot.style.background = scoreColors[score];
        } else {
            dot.classList.remove('active');
            dot.style.background = '';
        }
    });
}
</script>
@endsection
