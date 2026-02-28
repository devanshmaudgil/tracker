@extends('layouts.app')

@section('title', 'Resume Analysis')

@section('content')
<div class="content-header">
    <h1>Resume Analysis (AI - Gemini)</h1>
</div>

<style>
    .analysis-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(0, 1.3fr);
        gap: 20px;
    }

    @media (max-width: 992px) {
        .analysis-layout {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    .analysis-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .analysis-card h2 {
        color: #0a2d29;
        margin-bottom: 12px;
        font-size: 18px;
        border-bottom: 2px solid #f1cd86;
        padding-bottom: 6px;
    }

    .analysis-card h3 {
        color: #0a2d29;
        margin-bottom: 8px;
        font-size: 15px;
    }

    .analysis-meta {
        font-size: 12px;
        color: #777;
        margin-bottom: 12px;
    }

    .analysis-output {
        font-size: 14px;
        line-height: 1.6;
        color: #333;
        white-space: pre-wrap;
    }

    .analysis-output ul,
    .analysis-output ol {
        margin-left: 18px;
        margin-bottom: 10px;
    }

    .analysis-output li {
        margin-bottom: 4px;
    }

    .badge-soft {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11px;
        background-color: rgba(241, 205, 134, 0.15);
        color: #0a2d29;
        border: 1px solid rgba(241, 205, 134, 0.7);
        margin-left: 6px;
    }

    .qa-toggle-btn {
        margin-top: 10px;
    }

    .qa-card {
        margin-top: 10px;
        display: none;
    }

    .qa-card.active {
        display: block;
    }

    .hint-text {
        font-size: 12px;
        color: #666;
        margin-top: 6px;
    }
</style>

@php
    $jobDescriptionValue = old('job_description', $jobDescription ?? '');
@endphp

<div class="analysis-layout">
    <div>
        <div class="analysis-card">
            <h2>Run New Analysis</h2>
            <p class="analysis-meta">
                Paste the job description and upload the candidate's PDF resume. Gemini will compare both and create a recruiter-friendly report.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; margin-bottom: 15px;">
                    <ul style="margin-left: 18px; font-size: 13px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('resume.analysis.analyze') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="job_description">Job Description</label>
                    <textarea id="job_description" name="job_description" rows="10" required>{{ $jobDescriptionValue }}</textarea>
                    <div class="hint-text">
                        You can paste the JD from your tracker or client email. Try to include key skills, experience range, location, and any must-haves.
                    </div>
                </div>

                <div class="form-group">
                    <label for="resume">Candidate Resume (PDF)</label>
                    <input type="file" id="resume" name="resume" accept="application/pdf" required>
                    <div class="hint-text">
                        Only PDF files are supported right now. Max size 5 MB.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Analyze Resume
                </button>
            </form>
        </div>
    </div>

    <div>
        @if (isset($resumeAnalysis))
            <div class="analysis-card">
                <h2>
                    AI Resume Analysis
                    <span class="badge-soft">Gemini</span>
                </h2>
                <div class="analysis-meta">
                    Generated from the latest JD and resume you submitted.
                </div>
                <div class="analysis-output">
                    {{ $resumeAnalysis }}
                </div>

                <button type="button" class="btn btn-secondary btn-sm qa-toggle-btn" onclick="toggleQaCard()">
                    View Screening Questions & Answers
                </button>
            </div>

            @if (isset($screeningQa))
                <div id="qaCard" class="analysis-card qa-card">
                    <h2>Screening Call Questions (with Ideal Answers)</h2>
                    <div class="analysis-meta">
                        Use these during your first screening call to validate fit quickly.
                    </div>
                    <div class="analysis-output">
                        {{ $screeningQa }}
                    </div>
                </div>
            @endif
        @else
            <div class="analysis-card">
                <h2>AI Analysis Output</h2>
                <p class="analysis-meta">
                    Run an analysis from the left panel to see a full report here.
                </p>
                <p style="font-size: 13px; color: #555;">
                    The report will include strengths, gaps, percentage match, and a clear recommendation on whether to consider the candidate.
                    You can then open a second card with ready-to-use screening questions (with ideal answers) tailored to this JD and resume.
                </p>
            </div>
        @endif
    </div>
</div>

<script>
    function toggleQaCard() {
        const qaCard = document.getElementById('qaCard');
        if (!qaCard) return;
        qaCard.classList.toggle('active');
    }
</script>
@endsection

