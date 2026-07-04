@extends('layouts.app')

@section('title', 'Dashboard Analytics')
@section('page_heading', 'Analytics Dashboard')

@section('content')
<div class="dash2-page">

    {{-- ── Toolbar: search, filters, export ── --}}
    <div class="dash2-enter dash2-enter-1">
        <form id="dashFilters" class="dash2-toolbar-wrap" autocomplete="off">
            <div class="dash2-toolbar">
                <div class="dash2-toolbar__search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="search" value="{{ $activeFilters['search'] }}" placeholder="Search by position or client...">
                </div>
                <div class="dash2-toolbar__actions">
                    <span class="dash2-loading" id="dashLoading" aria-hidden="true"></span>
                    <button type="button" id="dashFilterToggle" class="dash2-btn dash2-btn--ghost dash2-filter-toggle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
                        Filters
                        <span class="dash2-filter-count" id="dashFilterCount" hidden>0</span>
                        <svg class="dash2-filter-toggle__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <a href="#" id="dashExport" class="dash2-btn dash2-btn--accent">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </a>
                </div>
            </div>

            <div class="dash2-filter-panel" id="dashFilterPanel" hidden>
                <div class="dash2-filter">
                    <label>Year</label>
                    <select name="year" data-label="Year">
                        <option value="">All Years</option>
                        @foreach($filterOptions['years'] as $year)
                            <option value="{{ $year }}" @selected($activeFilters['year'] == $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash2-filter">
                    <label>Month</label>
                    <select name="month_id" data-label="Month">
                        <option value="">All Months</option>
                        @foreach($filterOptions['months'] as $month)
                            <option value="{{ $month['id'] }}" @selected($activeFilters['month_id'] == $month['id'])>{{ $month['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash2-filter">
                    <label>Client</label>
                    <select name="client_id" data-label="Client">
                        <option value="">All Clients</option>
                        @foreach($filterOptions['clients'] as $client)
                            <option value="{{ $client->id }}" @selected($activeFilters['client_id'] == $client->id)>{{ $client->client }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash2-filter">
                    <label>Lead Recruiter</label>
                    <select name="lead_recruiter_id" data-label="Lead Recruiter">
                        <option value="">All Recruiters</option>
                        @foreach($filterOptions['recruiters'] as $recruiter)
                            <option value="{{ $recruiter->id }}" @selected($activeFilters['lead_recruiter_id'] == $recruiter->id)>{{ $recruiter->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash2-filter">
                    <label>Region</label>
                    <select name="region" data-label="Region">
                        <option value="">All Regions</option>
                        @foreach($filterOptions['regionGroups'] as $region)
                            <option value="{{ $region['value'] }}" @selected($activeFilters['region'] == $region['value'])>{{ $region['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash2-filter">
                    <label>Status</label>
                    <select name="status" data-label="Status">
                        <option value="">All Statuses</option>
                        @foreach($filterOptions['statuses'] as $status)
                            <option value="{{ $status['value'] }}" @selected($activeFilters['status'] == $status['value'])>{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash2-filter">
                    <label>Priority</label>
                    <select name="priority" data-label="Priority">
                        <option value="">All Priorities</option>
                        @foreach($filterOptions['priorities'] as $priority)
                            <option value="{{ $priority }}" @selected($activeFilters['priority'] == $priority)>{{ $priority }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash2-filter">
                    <label>Job Type</label>
                    <select name="type_of_job" data-label="Job Type">
                        <option value="">All Types</option>
                        @foreach($filterOptions['jobTypes'] as $type)
                            <option value="{{ $type }}" @selected($activeFilters['type_of_job'] == $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash2-filter">
                    <label>Source</label>
                    <select name="csi" data-label="Source">
                        <option value="">All Sources</option>
                        @foreach($filterOptions['sources'] as $source)
                            <option value="{{ $source }}" @selected($activeFilters['csi'] == $source)>{{ $source }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="dash2-filter-panel__actions">
                    <button type="button" id="dashApplyPanel" class="dash2-btn dash2-btn--primary dash2-btn--sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Apply &amp; Close
                    </button>
                    <button type="button" id="dashReset" class="dash2-btn dash2-btn--ghost dash2-btn--sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9 9 0 0 0-6.36 2.64L3 8"/><polyline points="3 3 3 8 8 8"/></svg>
                        Reset all
                    </button>
                </div>
            </div>

            <div class="dash2-chips" id="dashChips" hidden></div>
        </form>
    </div>

    {{-- ── KPI Cards ── --}}
    <div class="dash2-enter dash2-enter-2">
        <div class="dash2-kpi-grid" id="kpiGrid">
            <div class="dash2-kpi dash2-kpi--hero dash2-kpi--clickable" data-accent="teal" data-kpi-key="total" role="button" tabindex="0" title="Click to view all positions">
                <div class="dash2-kpi__top">
                    <span class="dash2-kpi__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7h-9M14 17H5M17 17a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM7 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg></span>
                    <span class="dash2-kpi__label">Total Positions</span>
                </div>
                <div class="dash2-kpi__value" data-kpi="total">0</div>
                <div class="dash2-kpi__foot"><span data-kpi="total_candidates">0</span> candidates assigned</div>
            </div>
            <div class="dash2-kpi dash2-kpi--clickable" data-accent="teal" data-kpi-key="open" role="button" tabindex="0" title="Click to view open positions">
                <div class="dash2-kpi__top">
                    <span class="dash2-kpi__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
                    <span class="dash2-kpi__label">Open</span>
                </div>
                <div class="dash2-kpi__value" data-kpi="open">0</div>
                <div class="dash2-kpi__foot">Awaiting sourcing</div>
            </div>
            <div class="dash2-kpi dash2-kpi--clickable" data-accent="gold" data-kpi-key="in_progress" role="button" tabindex="0" title="Click to view in-progress positions">
                <div class="dash2-kpi__top">
                    <span class="dash2-kpi__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></span>
                    <span class="dash2-kpi__label">In Progress</span>
                </div>
                <div class="dash2-kpi__value" data-kpi="in_progress">0</div>
                <div class="dash2-kpi__foot"><span data-kpi="active_candidates">0</span> active candidates</div>
            </div>
            <div class="dash2-kpi dash2-kpi--clickable" data-accent="tealglow" data-kpi-key="placed" role="button" tabindex="0" title="Click to view placed positions">
                <div class="dash2-kpi__top">
                    <span class="dash2-kpi__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></span>
                    <span class="dash2-kpi__label">Placed</span>
                </div>
                <div class="dash2-kpi__value" data-kpi="placed">0</div>
                <div class="dash2-kpi__foot"><span data-kpi="placement_rate">0</span>% placement rate</div>
            </div>
            <div class="dash2-kpi dash2-kpi--clickable" data-accent="slate" data-kpi-key="rejected" role="button" tabindex="0" title="Click to view rejected positions">
                <div class="dash2-kpi__top">
                    <span class="dash2-kpi__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></span>
                    <span class="dash2-kpi__label">Rejected</span>
                </div>
                <div class="dash2-kpi__value" data-kpi="rejected">0</div>
                <div class="dash2-kpi__foot"><span data-kpi="win_rate">0</span>% win rate</div>
            </div>
            <div class="dash2-kpi dash2-kpi--hero dash2-kpi--clickable" data-accent="gold" data-kpi-key="attention" role="button" tabindex="0" title="Click to view positions needing attention">
                <div class="dash2-kpi__top">
                    <span class="dash2-kpi__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
                    <span class="dash2-kpi__label">Needs Attention</span>
                </div>
                <div class="dash2-kpi__value" data-kpi="attention_total">0</div>
                <div class="dash2-kpi__foot"><span data-kpi="overdue">0</span> overdue · <span data-kpi="urgent">0</span> urgent</div>
            </div>
        </div>
    </div>

    {{-- ── Trends & Performance ── --}}
    <div class="dash2-enter dash2-enter-3">
        <div class="dash2-section">
            <div class="dash2-section__head">
                <span class="dash2-section__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></span>
                <h2 class="dash2-section__title">Trends &amp; Performance</h2>
            </div>
            <div class="dash2-grid">
                <div class="dash2-card dash2-card--wide" data-accent="teal">
                    <div class="dash2-card__head">
                        <span class="dash2-card__icon" data-accent="teal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></span>
                        <div>
                            <h3>Positions Trend</h3>
                            <span class="dash2-card__sub">Raised vs Placed over time</span>
                        </div>
                    </div>
                    <div class="dash2-chart dash2-chart--tall"><canvas id="chartTrend"></canvas></div>
                </div>

                <div class="dash2-card dash2-card--wide" data-accent="gold">
                    <div class="dash2-card__head">
                        <span class="dash2-card__icon" data-accent="gold"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                        <div>
                            <h3>Recruiter Performance</h3>
                            <span class="dash2-card__sub">Positions handled vs placements</span>
                        </div>
                    </div>
                    <div class="dash2-chart"><canvas id="chartRecruiters"></canvas></div>
                </div>
            </div>
        </div>

        {{-- ── Pipeline Overview ── --}}
        <div class="dash2-section">
            <div class="dash2-section__head">
                <span class="dash2-section__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg></span>
                <h2 class="dash2-section__title">Pipeline Overview</h2>
            </div>
            <div class="dash2-grid dash2-grid--3col">
                <div class="dash2-card" data-accent="tealglow">
                    <div class="dash2-card__head">
                        <span class="dash2-card__icon" data-accent="tealglow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20"/></svg></span>
                        <div>
                            <h3>Status Breakdown</h3>
                            <span class="dash2-card__sub">Position lifecycle</span>
                        </div>
                    </div>
                    <div class="dash2-chart"><canvas id="chartStatus"></canvas></div>
                </div>

                <div class="dash2-card" data-accent="goldd">
                    <div class="dash2-card__head">
                        <span class="dash2-card__icon" data-accent="goldd"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg></span>
                        <div>
                            <h3>Candidate Funnel</h3>
                            <span class="dash2-card__sub">Pipeline progression</span>
                        </div>
                    </div>
                    <div class="dash2-chart"><canvas id="chartFunnel"></canvas></div>
                </div>

                <div class="dash2-card" data-accent="gold">
                    <div class="dash2-card__head">
                        <span class="dash2-card__icon" data-accent="gold"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1m-6 4h1m4 0h1m-6 4h1m4 0h1"/></svg></span>
                        <div>
                            <h3>Top Clients</h3>
                            <span class="dash2-card__sub">By position volume</span>
                        </div>
                    </div>
                    <div class="dash2-chart"><canvas id="chartClients"></canvas></div>
                </div>
            </div>
        </div>

        {{-- ── Distribution ── --}}
        <div class="dash2-section">
            <div class="dash2-section__head">
                <span class="dash2-section__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg></span>
                <h2 class="dash2-section__title">Distribution</h2>
            </div>
            <div class="dash2-grid dash2-grid--3col">
                <div class="dash2-card" data-accent="goldd">
                    <div class="dash2-card__head">
                        <span class="dash2-card__icon" data-accent="goldd"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg></span>
                        <div>
                            <h3>Priority Mix</h3>
                            <span class="dash2-card__sub">Urgency distribution</span>
                        </div>
                    </div>
                    <div class="dash2-chart"><canvas id="chartPriority"></canvas></div>
                </div>

                <div class="dash2-card" data-accent="slate">
                    <div class="dash2-card__head">
                        <span class="dash2-card__icon" data-accent="slate"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></span>
                        <div>
                            <h3>Job Type</h3>
                            <span class="dash2-card__sub">Work arrangement</span>
                        </div>
                    </div>
                    <div class="dash2-chart"><canvas id="chartJobType"></canvas></div>
                </div>

                <div class="dash2-card" data-accent="tealglow">
                    <div class="dash2-card__head">
                        <span class="dash2-card__icon" data-accent="tealglow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg></span>
                        <div>
                            <h3>Sourcing Channel</h3>
                            <span class="dash2-card__sub">Where demands come from</span>
                        </div>
                    </div>
                    <div class="dash2-chart"><canvas id="chartSource"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Recent Positions ── --}}
    <div class="dash2-enter dash2-enter-4">
        <div class="dash2-section">
            <div class="dash2-section__head">
                <span class="dash2-section__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
                <h2 class="dash2-section__title">Recent Positions</h2>
            </div>
            <div class="dash2-card" data-accent="teal">
                <div class="dash2-table-wrap">
                    <table class="dash2-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Position</th>
                                <th>Client</th>
                                <th>Recruiter</th>
                                <th>Month</th>
                                <th>Priority</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="recentPositionsBody"></tbody>
                    </table>
                    <div class="dash2-empty" id="recentEmpty" hidden>No positions match these filters.</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- KPI Detail Modal --}}
<div class="dash2-modal" id="kpiModal" hidden aria-hidden="true">
    <div class="dash2-modal__backdrop" data-close-modal></div>
    <div class="dash2-modal__panel" role="dialog" aria-labelledby="kpiModalTitle" aria-modal="true">
        <div class="dash2-modal__head">
            <div>
                <h2 id="kpiModalTitle">Details</h2>
                <p id="kpiModalSubtitle"></p>
            </div>
            <button type="button" class="dash2-modal__close" data-close-modal aria-label="Close">&times;</button>
        </div>
        <div class="dash2-modal__body" id="kpiModalBody">
            <div class="dash2-modal__loading" id="kpiModalLoading">Loading...</div>
        </div>
    </div>
</div>

@include('dashboard._styles')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    window.DASHBOARD_PAYLOAD = @json($payload);
    window.DASHBOARD_DATA_URL = @json(route('dashboard.data'));
    window.DASHBOARD_KPI_URL = @json(url('/dashboard/kpi'));
    window.DASHBOARD_EXPORT_URL = @json(route('dashboard.export'));
</script>
@include('dashboard._scripts')
@endpush
