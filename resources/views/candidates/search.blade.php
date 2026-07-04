@extends('layouts.app')

@section('title', 'Find Candidates')

@section('content')
<div class="content-header">
    <h1>Find Candidates from Job Description</h1>
</div>

<style>
    .search-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr);
        gap: 24px;
    }
    @media (max-width: 992px) {
        .search-layout {
            grid-template-columns: 1fr;
        }
    }
    .search-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }
    .search-card h2 {
        color: #0a2d29;
        margin-bottom: 12px;
        font-size: 18px;
        border-bottom: 2px solid #f1cd86;
        padding-bottom: 6px;
    }
    .search-meta {
        font-size: 12px;
        color: #777;
        margin-bottom: 12px;
    }
    .hint-text {
        font-size: 12px;
        color: #666;
        margin-top: 6px;
    }
    .result-item {
        padding: 14px 0;
        border-bottom: 1px solid #eee;
        transition: background 0.15s;
    }
    .result-item:hover {
        background: #fafafa;
    }
    .result-item:last-child {
        border-bottom: none;
    }
    .result-title {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .result-title a {
        color: #0a2d29;
        text-decoration: none;
    }
    .result-title a:hover {
        color: #f1cd86;
        text-decoration: underline;
    }
    .result-link {
        font-size: 12px;
        color: #0066cc;
        word-break: break-all;
        margin-bottom: 4px;
    }
    .result-snippet {
        font-size: 13px;
        color: #555;
        line-height: 1.45;
    }
    .result-source {
        display: inline-block;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 4px;
        color: #fff;
        margin-top: 6px;
        font-weight: 600;
    }
    .result-source.source-linkedin { background: #0077b5; }
    .result-source.source-indeed { background: #2164f3; }
    .result-source.source-naukri { background: #4a90d9; }
    .platform-check {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        cursor: pointer;
        padding: 6px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        transition: all 0.15s;
        user-select: none;
    }
    .platform-check:has(input:checked) {
        border-color: #0a2d29;
        background: rgba(10, 45, 41, 0.06);
    }
    .platform-check input { display: none; }
    .platform-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 4px;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        line-height: 1;
    }
    .keywords-used {
        font-size: 13px;
        color: #555;
        margin-bottom: 16px;
    }
    .keywords-used strong {
        color: #0a2d29;
    }
    .match-badge {
        display: inline-block;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 999px;
        background: rgba(10, 45, 41, 0.08);
        color: #0a2d29;
        margin-left: 8px;
    }
    .pagination {
        margin-top: 12px;
        display: flex;
        justify-content: center;
        gap: 4px;
        flex-wrap: wrap;
    }
    .page-btn {
        border: 1px solid #ddd;
        background: #fff;
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 4px;
        cursor: pointer;
        min-width: 28px;
        text-align: center;
    }
    .page-btn.active {
        background: #0a2d29;
        color: #fff;
        border-color: #0a2d29;
    }
    .page-btn:disabled {
        opacity: 0.5;
        cursor: default;
    }
</style>

@php
    $jdValue = old('job_description', $jobDescription ?? '');
    $selectedPlatforms = old('platforms', $selectedPlatforms ?? ['linkedin']);
@endphp

<div class="search-layout">
    <div>
        <div class="search-card">
            <h2>Paste Job Description</h2>
            <p class="search-meta">
                Paste a job description below. We'll use AI to extract the role and key skills, then search selected platforms for relevant candidate profiles.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; margin-bottom: 15px; padding: 12px; border-radius: 6px;">
                    <ul style="margin-left: 18px; font-size: 13px; margin-bottom: 0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; margin-bottom: 15px; padding: 12px; border-radius: 6px;">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('candidates.search.submit') }}">
                @csrf
                <div class="form-group">
                    <label for="job_description">Job Description</label>
                    <textarea id="job_description" name="job_description" rows="12" required placeholder="Paste the full job description here (title, requirements, skills, experience...)">{{ $jdValue }}</textarea>
                    <div class="hint-text">
                        Include job title, required skills, technologies, and experience level for better search results.
                    </div>
                </div>

                <div class="form-group" style="margin-top: 14px;">
                    <label>Search Platforms</label>
                    <div style="display: flex; gap: 18px; margin-top: 6px; flex-wrap: wrap;">
                        <label class="platform-check">
                            <input type="checkbox" name="platforms[]" value="linkedin" {{ in_array('linkedin', $selectedPlatforms) ? 'checked' : '' }}>
                            <span class="platform-icon" style="background: #0077b5;">in</span>
                            LinkedIn
                        </label>
                        <label class="platform-check">
                            <input type="checkbox" name="platforms[]" value="indeed" {{ in_array('indeed', $selectedPlatforms) ? 'checked' : '' }}>
                            <span class="platform-icon" style="background: #2164f3;">iD</span>
                            Indeed
                        </label>
                        <label class="platform-check">
                            <input type="checkbox" name="platforms[]" value="naukri" {{ in_array('naukri', $selectedPlatforms) ? 'checked' : '' }}>
                            <span class="platform-icon" style="background: #4a90d9;">N</span>
                            Naukri
                        </label>
                    </div>
                    <div class="hint-text">Select one or more platforms to search. Each uses a separate SerpAPI call.</div>
                </div>

                @if (!empty($booleanQueries))
                    <div class="form-group" style="margin-top: 12px;">
                        <label>Boolean Search Queries</label>
                        @foreach ($booleanQueries as $platformLabel => $bq)
                            <div style="margin-bottom: 8px;">
                                <span style="font-size: 12px; font-weight: 600; color: #0a2d29;">{{ $platformLabel }}</span>
                                <textarea rows="2" readonly onclick="this.select()" style="margin-top: 2px;">{{ $bq }}</textarea>
                            </div>
                        @endforeach
                        <div class="hint-text">
                            Copy any of these and paste into Google or Bing to run the search manually.
                        </div>
                    </div>
                @endif

                <button type="submit" class="btn btn-primary">
                    Find Candidates
                </button>
            </form>
        </div>
    </div>

    <div>
        @if (isset($results) && count($results) > 0)
            <div class="search-card">
                <h2>Candidate Profiles Found</h2>
                <p class="search-meta">
                    Role: <strong>{{ $jobTitle ?? '—' }}</strong>
                </p>
                @if (!empty($keywords))
                    <p class="keywords-used">
                        Search keywords: <strong>{{ implode(', ', $keywords) }}</strong>
                    </p>
                @endif
                <p style="font-size: 13px; color: #666; margin-bottom: 16px;">
                    Open links in new tabs to view candidate profiles. You can reach out to candidates directly from their profiles.
                </p>
                <div id="resultsContainer">
                    @foreach ($results as $result)
                        <div class="result-item" data-index="{{ $loop->index }}">
                            <div class="result-title">
                                <a href="{{ $result['link'] }}" target="_blank" rel="noopener noreferrer">
                                    {{ $result['title'] ?: 'View profile' }}
                                </a>
                                @if (!empty($result['match_percentage']))
                                    <span class="match-badge">{{ $result['match_percentage'] }}% match</span>
                                @endif
                            </div>
                            <div class="result-link">{{ $result['link'] }}</div>
                            @if (!empty($result['snippet']))
                                <div class="result-snippet">{{ $result['snippet'] }}</div>
                            @endif
                            @if (!empty($result['source']))
                                <span class="result-source source-{{ strtolower($result['source']) }}">{{ $result['source'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if (count($results) > 10)
                    <div id="resultsPagination" class="pagination"></div>
                    <script>
                        (function () {
                            const perPage = 10;
                            const items = Array.from(document.querySelectorAll('#resultsContainer .result-item'));
                            const total = items.length;
                            if (!total) return;
                            const totalPages = Math.ceil(total / perPage);
                            const pagination = document.getElementById('resultsPagination');
                            let currentPage = 1;

                            function renderPage(page) {
                                currentPage = page;
                                const start = (page - 1) * perPage;
                                const end = start + perPage;
                                items.forEach((el, idx) => {
                                    el.style.display = (idx >= start && idx < end) ? 'block' : 'none';
                                });
                                renderControls();
                            }

                            function renderControls() {
                                pagination.innerHTML = '';
                                const createBtn = (label, page, disabled = false, active = false) => {
                                    const btn = document.createElement('button');
                                    btn.textContent = label;
                                    btn.className = 'page-btn' + (active ? ' active' : '');
                                    if (disabled) {
                                        btn.disabled = true;
                                    } else {
                                        btn.addEventListener('click', function () {
                                            if (page !== currentPage) renderPage(page);
                                        });
                                    }
                                    pagination.appendChild(btn);
                                };

                                createBtn('Prev', Math.max(1, currentPage - 1), currentPage === 1);

                                for (let p = 1; p <= totalPages; p++) {
                                    createBtn(String(p), p, false, p === currentPage);
                                }

                                createBtn('Next', Math.min(totalPages, currentPage + 1), currentPage === totalPages);
                            }

                            renderPage(1);
                        })();
                    </script>
                @endif
            </div>
        @elseif (isset($results) && count($results) === 0)
            <div class="search-card">
                <h2>No Results</h2>
                <p class="search-meta">
                    Role: <strong>{{ $jobTitle ?? '—' }}</strong>
                </p>
                <p style="font-size: 14px; color: #555;">
                    No candidate profiles were found for this job description. Try simplifying the keywords or broadening the role title and run the search again.
                </p>
            </div>
        @else
            <div class="search-card">
                <h2>Results</h2>
                <p class="search-meta">
                    Paste a job description and click <strong>Find Candidates</strong> to search for matching profiles.
                </p>
                <p style="font-size: 13px; color: #555;">
                    Results will appear here as links to candidate profiles on LinkedIn, Indeed, and Naukri. You can open each link to review the candidate and reach out.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
