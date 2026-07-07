@extends('layouts.app')

@section('title', 'User Guide')
@section('page_heading', 'User Guide')

@section('content')
<div class="guide-wrap" id="guideWrap">
    {{-- Top progress --}}
    <div class="guide-progress"><span id="guideProgressBar"></span></div>

    <div class="guide-shell">
        {{-- Chapter navigation --}}
        <aside class="guide-toc" id="guideToc">
            <div class="guide-toc__head">
                <span class="guide-toc__eyebrow">Handbook</span>
                <span class="guide-toc__title">Chapters</span>
            </div>
            <nav class="guide-toc__list" id="guideTocList"></nav>
        </aside>

        {{-- Booklet --}}
        <div class="guide-book" id="guideBook">
            <div class="guide-pages" id="guidePages">

                {{-- 0 · Cover --}}
                <section class="guide-page guide-cover guide-cover--play is-active" data-title="Welcome" data-icon="book">
                    <div class="guide-cover__ambient" aria-hidden="true"></div>
                    <div class="guide-cover__glow"></div>
                    <div class="guide-cover__inner">
                        <div class="guide-cover__badge">User Handbook</div>
                        <img src="{{ asset('logo.png') }}" alt="RADiiX INFINITEii" class="guide-cover__logo">
                        <h1 class="guide-cover__title">RADiiX <span>INFINITEii</span></h1>
                        <p class="guide-cover__tag">
                            <span class="guide-cover__tag-text">Rooting Intelligence, Inspiring Innovation</span>
                            <span class="guide-cover__tag-shine" aria-hidden="true"></span>
                        </p>
                        <p class="guide-cover__lead">
                            A complete walkthrough of your recruitment workspace — from posting a demand
                            to placing a candidate, with analytics, AI resume screening, and smart tools along the way.
                        </p>
                        <button type="button" class="guide-cover__cta" data-go="1">
                            Start reading
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </button>
                        <div class="guide-cover__meta">
                            <span>10 chapters</span>
                            <span class="dot"></span>
                            <span>~6 min read</span>
                        </div>
                    </div>
                </section>

                {{-- 1 · Getting around --}}
                <section class="guide-page" data-title="Getting Around" data-icon="compass">
                    <div class="guide-page__head">
                        <span class="guide-chip">Chapter 1</span>
                        <h2>Getting around the workspace</h2>
                        <p>Every screen shares the same frame, so once you learn it, you know the whole tool.</p>
                    </div>
                    <div class="guide-grid guide-grid--2">
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'menu'])</div>
                            <h3>The sidebar</h3>
                            <p>Your main navigation lives on the left — Dashboard, Recruitment Workspace, Resume Analysis, Find Candidates, and the Register group. It stays compact by default and expands when you hover over it.</p>
                            @include('guide._go', ['url' => route('tracker.index'), 'label' => 'Open workspace'])
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'clock'])</div>
                            <h3>The top bar</h3>
                            <p>Shows the page title, a live <strong>world clock</strong> (IST, CDT, EST), the <strong>holiday calendar</strong> icon, and your profile. The clock and calendar help you coordinate across India, USA and Canada.</p>
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'grid'])</div>
                            <h3>Register group</h3>
                            <p>Under <em>Register</em> you manage the building blocks: Months, Users, Clients, Regions and Candidates. Set these up first so they’re ready to pick everywhere else.</p>
                            @include('guide._go', ['url' => route('clients.info'), 'label' => 'Open Register'])
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'shield'])</div>
                            <h3>Signing in</h3>
                            <p>Log in with your official credentials. Your account carries your name and <strong>@rinfinite.com</strong> email, which is used when drafting candidate emails from the tool.</p>
                            @include('guide._go', ['url' => route('users.index'), 'label' => 'Manage users'])
                        </div>
                    </div>
                </section>

                {{-- 2 · Dashboard --}}
                <section class="guide-page" data-title="Dashboard" data-icon="chart">
                    <div class="guide-page__head">
                        <span class="guide-chip">Chapter 2</span>
                        <h2>Dashboard analytics</h2>
                        <p>Your bird’s-eye view of recruitment health, updated from live data.</p>
                    </div>
                    <div class="guide-steps">
                        <div class="guide-step">
                            <span class="guide-step__n">1</span>
                            <div>
                                <h3>Read the KPI cards</h3>
                                <p>The cards up top summarise totals — open demands, candidates in play, submissions, placements and items needing attention. Each card is clickable to drill into the details behind the number.</p>
                                @include('guide._go', ['url' => route('dashboard.index'), 'label' => 'Open Dashboard'])
                            </div>
                        </div>
                        <div class="guide-step">
                            <span class="guide-step__n">2</span>
                            <div>
                                <h3>Filter what you see</h3>
                                <p>Open the filter dropdown to narrow by month, recruiter, client, status and <strong>region</strong> (grouped into USA / Canada). Charts and tables update to match.</p>
                                @include('guide._go', ['url' => route('dashboard.index'), 'label' => 'Try filters'])
                            </div>
                        </div>
                        <div class="guide-step">
                            <span class="guide-step__n">3</span>
                            <div>
                                <h3>Explore the charts</h3>
                                <p>Visual breakdowns show trends and distribution across your pipeline so you can spot bottlenecks at a glance.</p>
                                @include('guide._go', ['url' => route('dashboard.index'), 'label' => 'View charts'])
                            </div>
                        </div>
                        <div class="guide-step">
                            <span class="guide-step__n">4</span>
                            <div>
                                <h3>Export</h3>
                                <p>Use <strong>Export</strong> to download the current view for reporting or sharing offline.</p>
                                @include('guide._go', ['url' => route('dashboard.export'), 'label' => 'Export data'])
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 3 · Recruitment workspace --}}
                <section class="guide-page" data-title="Workspace" data-icon="briefcase">
                    <div class="guide-page__head">
                        <span class="guide-chip">Chapter 3</span>
                        <h2>The recruitment workspace</h2>
                        <p>Where job demands live. Create, import, and open each demand to work its candidates.</p>
                    </div>
                    <div class="guide-grid guide-grid--3">
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'plus'])</div>
                            <h3>Create a demand</h3>
                            <p>Click <strong>Create Demand</strong> and fill in the role — position, client, region, and a <strong>Job Description</strong> that later powers resume matching and posters.</p>
                            @include('guide._go', ['url' => route('tracker.create'), 'label' => 'Create demand'])
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'upload'])</div>
                            <h3>Import in bulk</h3>
                            <p>Have a spreadsheet? Use <strong>Import</strong> to bring many demands in at once instead of adding them one by one.</p>
                            @include('guide._go', ['url' => route('tracker.import'), 'label' => 'Import demands'])
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'open'])</div>
                            <h3>Open a demand</h3>
                            <p>Click any row to open its detail page, where you assign candidates and drive them through the pipeline.</p>
                            @include('guide._go', ['url' => route('tracker.index'), 'label' => 'Open workspace'])
                        </div>
                    </div>
                    <div class="guide-callout">
                        <span class="guide-callout__ico">@include('guide._icon', ['n' => 'bulb'])</span>
                        <p><strong>Tip:</strong> A clear, complete Job Description makes the AI resume analysis and LinkedIn posters noticeably better.</p>
                    </div>
                </section>

                {{-- 4 · Candidate pipeline --}}
                <section class="guide-page" data-title="Pipeline" data-icon="flow">
                    <div class="guide-page__head">
                        <span class="guide-chip">Chapter 4</span>
                        <h2>The candidate pipeline</h2>
                        <p>Each candidate moves through a clear set of stages, from sourced to placed.</p>
                    </div>
                    <div class="guide-flow">
                        <span class="guide-flow__node">Sourced &amp; identified</span>
                        <span class="guide-flow__arrow">→</span>
                        <span class="guide-flow__node">Screened</span>
                        <span class="guide-flow__arrow">→</span>
                        <span class="guide-flow__node">Shortlisted</span>
                        <span class="guide-flow__arrow">→</span>
                        <span class="guide-flow__node">Submitted to client</span>
                        <span class="guide-flow__arrow">→</span>
                        <span class="guide-flow__node">Client interviews</span>
                        <span class="guide-flow__arrow">→</span>
                        <span class="guide-flow__node">Selected &amp; offer</span>
                        <span class="guide-flow__arrow">→</span>
                        <span class="guide-flow__node guide-flow__node--gold">Placed</span>
                    </div>
                    <div class="guide-grid guide-grid--2">
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'user-plus'])</div>
                            <h3>Assign a candidate</h3>
                            <p>On a demand’s page, assign a candidate to start their journey. They enter at <em>Sourced &amp; identified</em>.</p>
                            @include('guide._go', ['url' => route('tracker.index'), 'label' => 'Go to demands'])
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'check'])</div>
                            <h3>Work the checklist</h3>
                            <p>Open the candidate drawer to update stages and tick the checklist — screening call, documents (govt ID, work auth, LinkedIn), RTR signed, submission, interviews and more. The stage advances automatically as items complete.</p>
                            @include('guide._go', ['url' => route('tracker.index'), 'label' => 'Open pipeline'])
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'revert'])</div>
                            <h3>Reject or revert</h3>
                            <p>Mark a candidate rejected with a reason, or revert a step if something changes. The pipeline stays accurate.</p>
                            @include('guide._go', ['url' => route('tracker.index'), 'label' => 'Manage candidates'])
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'star'])</div>
                            <h3>Approve &amp; place</h3>
                            <p>Once selected and confirmed, approve the candidate and record the final placement stage.</p>
                            @include('guide._go', ['url' => route('tracker.index'), 'label' => 'View placements'])
                        </div>
                    </div>
                </section>

                {{-- 5 · Candidate actions --}}
                <section class="guide-page" data-title="Actions" data-icon="bolt">
                    <div class="guide-page__head">
                        <span class="guide-chip">Chapter 5</span>
                        <h2>Candidate actions</h2>
                        <p>Handy tools available from each candidate row’s actions menu.</p>
                    </div>
                    <div class="guide-grid guide-grid--2">
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'mail'])</div>
                            <h3>Draft an initialization email</h3>
                            <p>Once a candidate is identified, choose <strong>Mail</strong> to open a themed composer. Set From (your official email), To, and CC from staff, edit the body with rich formatting, then open it straight in Outlook as a ready draft.</p>
                            @include('guide._go', ['url' => route('tracker.index'), 'label' => 'Open workspace'])
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'doc'])</div>
                            <h3>Generate a submission report</h3>
                            <p>Produce a polished, branded submission report for the client — formatted and ready to download for a professional handoff.</p>
                            @include('guide._go', ['url' => route('tracker.index'), 'label' => 'Generate report'])
                        </div>
                    </div>
                    <div class="guide-callout">
                        <span class="guide-callout__ico">@include('guide._icon', ['n' => 'bulb'])</span>
                        <p><strong>Note:</strong> The Mail option appears once a candidate reaches the <em>identified</em> stage and requires your profile to have an <strong>@rinfinite.com</strong> email.</p>
                    </div>
                </section>

                {{-- 6 · Resume analysis --}}
                <section class="guide-page" data-title="Resume AI" data-icon="scan">
                    <div class="guide-page__head">
                        <span class="guide-chip">Chapter 6</span>
                        <h2>AI resume analysis</h2>
                        <p>Screen a resume against a role and get a structured fit report in seconds.</p>
                    </div>
                    <div class="guide-steps">
                        <div class="guide-step">
                            <span class="guide-step__n">1</span>
                            <div>
                                <h3>Provide the role &amp; resume</h3>
                                <p>Open <strong>Resume Analysis</strong>, point it at the position (and its job description), and upload the candidate’s resume.</p>
                                @include('guide._go', ['url' => route('resume.analysis.index'), 'label' => 'Open Resume Analysis'])
                            </div>
                        </div>
                        <div class="guide-step">
                            <span class="guide-step__n">2</span>
                            <div>
                                <h3>Watch it work</h3>
                                <p>A live progress indicator shows the analysis running — extracting text, matching skills, and scoring the fit.</p>
                                @include('guide._go', ['url' => route('resume.analysis.index'), 'label' => 'Start analysis'])
                            </div>
                        </div>
                        <div class="guide-step">
                            <span class="guide-step__n">3</span>
                            <div>
                                <h3>Review the fit report</h3>
                                <p>You get a match score with strengths, gaps and a clear summary to support your screening decision.</p>
                                @include('guide._go', ['url' => route('resume.analysis.index'), 'label' => 'View reports'])
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 7 · Find candidates --}}
                <section class="guide-page" data-title="Find Candidates" data-icon="search">
                    <div class="guide-page__head">
                        <span class="guide-chip">Chapter 7</span>
                        <h2>Find candidates</h2>
                        <p>Search your candidate base to quickly surface the right people.</p>
                    </div>
                    <div class="guide-grid guide-grid--2">
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'search'])</div>
                            <h3>Search &amp; filter</h3>
                            <p>Enter your criteria to find matching candidates across the database, then jump straight into their details.</p>
                            @include('guide._go', ['url' => route('candidates.search.index'), 'label' => 'Find candidates'])
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'link'])</div>
                            <h3>Act on results</h3>
                            <p>From results you can move a candidate toward a demand and continue in the pipeline without losing context.</p>
                            @include('guide._go', ['url' => route('candidates.index'), 'label' => 'Candidate register'])
                        </div>
                    </div>
                </section>

                {{-- 8 · Register --}}
                <section class="guide-page" data-title="Register" data-icon="grid">
                    <div class="guide-page__head">
                        <span class="guide-chip">Chapter 8</span>
                        <h2>The Register group</h2>
                        <p>Your master data. Keep these tidy and everything else stays clean.</p>
                    </div>
                    <div class="guide-grid guide-grid--3">
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'calendar'])</div>
                            <h3>Months</h3>
                            <p>Reporting periods used across the dashboard and trackers.</p>
                            @include('guide._go', ['url' => route('months.index'), 'label' => 'Manage months'])
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'users'])</div>
                            <h3>Users</h3>
                            <p>Your team — names, roles and official emails used for mailing.</p>
                            @include('guide._go', ['url' => route('users.index'), 'label' => 'Manage users'])
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'building'])</div>
                            <h3>Clients</h3>
                            <p>The companies you recruit for, linked to demands.</p>
                            @include('guide._go', ['url' => route('clients.info'), 'label' => 'Manage clients'])
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'globe'])</div>
                            <h3>Regions</h3>
                            <p>Locations grouped into regions (USA / Canada) for filtering.</p>
                            @include('guide._go', ['url' => route('locations.index'), 'label' => 'Manage locations'])
                        </div>
                        <div class="guide-card">
                            <div class="guide-card__ico">@include('guide._icon', ['n' => 'id'])</div>
                            <h3>Candidates</h3>
                            <p>Your talent pool — the people you assign to demands.</p>
                            @include('guide._go', ['url' => route('candidates.index'), 'label' => 'Manage candidates'])
                        </div>
                    </div>
                </section>

                {{-- 9 · Calendar & posters --}}
                <section class="guide-page" data-title="Calendar" data-icon="calendar">
                    <div class="guide-page__head">
                        <span class="guide-chip">Chapter 9</span>
                        <h2>Holiday calendar &amp; poster generator</h2>
                        <p>Track holidays across India, USA and Canada — and spin up branded LinkedIn posters.</p>
                    </div>
                    <div class="guide-steps">
                        <div class="guide-step">
                            <span class="guide-step__n">1</span>
                            <div>
                                <h3>Open the calendar</h3>
                                <p>Click the calendar icon in the top bar. Switch between <strong>India</strong>, <strong>USA</strong> and <strong>Canada</strong>, and browse month by month.</p>
                            </div>
                        </div>
                        <div class="guide-step">
                            <span class="guide-step__n">2</span>
                            <div>
                                <h3>Pick a holiday</h3>
                                <p>Days with holidays or observances are highlighted. Click one to see it, or browse the month’s list on the right.</p>
                            </div>
                        </div>
                        <div class="guide-step">
                            <span class="guide-step__n">3</span>
                            <div>
                                <h3>Generate a poster</h3>
                                <p>Hit <strong>Generate Poster</strong>. ChatGPT opens with a ready prompt (teal/gold/white, neural-network theme) and the poster header image is copied for you — paste it, and you’ll also get a LinkedIn caption.</p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 10 · Wrap up --}}
                <section class="guide-page guide-end" data-title="You're set" data-icon="flag">
                    <div class="guide-end__inner">
                        <div class="guide-end__ico">@include('guide._icon', ['n' => 'check-big'])</div>
                        <h2>You’re ready to go</h2>
                        <p>That’s the whole tool end to end. Set up your Register data, post a demand, move candidates through the pipeline, and lean on analytics, AI screening and the smart tools to move faster.</p>
                        <div class="guide-end__actions">
                            <a href="{{ route('dashboard.index') }}" class="guide-btn guide-btn--gold">Open Dashboard</a>
                            <a href="{{ route('tracker.index') }}" class="guide-btn guide-btn--ghost">Go to Workspace</a>
                            <button type="button" class="guide-btn guide-btn--ghost" data-go="0">Restart guide</button>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>

    {{-- Bottom controls --}}
    <div class="guide-controls">
        <button type="button" class="guide-nav-btn" id="guidePrev" aria-label="Previous page">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="15 18 9 12 15 6"/></svg>
            <span>Back</span>
        </button>
        <div class="guide-dots" id="guideDots"></div>
        <button type="button" class="guide-nav-btn guide-nav-btn--primary" id="guideNext" aria-label="Next page">
            <span>Next</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </div>
</div>

@include('guide._styles')
@include('guide._scripts')
@endsection
