@extends('layouts.app')

@section('title', 'Radiix Infiniteii Tracker')

@section('content')
<div class="content-header">
    <h1>Radiix Infiniteii Tracker</h1>
    <a href="{{ route('tracker.create') }}" class="btn btn-primary">Add</a>
</div>

<style>
    .table-container {
        overflow-x: auto;
        width: 100%;
        background: white;
        border-radius: 0 0 8px 8px; /* Rounded bottom only */
        padding: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .table-container table {
        font-size: 11px;
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }
    
    .table-container th,
    .table-container td {
        text-align: center;
        padding: 6px 4px;
        border-bottom: 1px solid #eee;
    }
    
    .table-container th {
        background-color: #0a2d29;
        color: white;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table-container td {
        color: #444;
    }

    .action-buttons {
        display: flex;
        gap: 4px;
        justify-content: center;
    }
    
    .btn-sm {
        padding: 4px 8px;
        font-size: 10px;
        border-radius: 3px;
    }
    
    .results-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        color: #666;
        font-size: 13px;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .search-filter-bar {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .search-input-wrapper {
        position: relative;
        width: 100%;
        max-width: 250px;
    }
    
    .search-input-wrapper input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
    }
    
    .search-input-wrapper input:focus {
        outline: none;
        border-color: #f1cd86;
    }
    
    .filter-icon-btn {
        background: #0a2d29;
        color: white;
        border: none;
        padding: 6px 10px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
        transition: background 0.3s;
        white-space: nowrap;
    }
    
    .filter-icon-btn:hover {
        background: #0d3a33;
    }
    
    .filter-icon-btn svg {
        width: 14px;
        height: 14px;
    }
    
    .filter-dropdown {
        position: absolute;
        right: 0;
        top: 40px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        padding: 15px;
        width: 280px;
        z-index: 1000;
        display: none;
    }
    
    .filter-dropdown.active {
        display: block;
    }
    
    .filter-dropdown h3 {
        margin: 0 0 12px 0;
        color: #0a2d29;
        font-size: 14px;
        border-bottom: 2px solid #f1cd86;
        padding-bottom: 5px;
    }
    
    .filter-group {
        margin-bottom: 12px;
    }
    
    .filter-group label {
        display: block;
        margin-bottom: 4px;
        font-weight: 600;
        color: #0a2d29;
        font-size: 12px;
    }
    
    .filter-group select {
        width: 100%;
        padding: 6px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 12px;
    }
    
    .filter-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }
    
    .filter-actions button,
    .filter-actions a {
        flex: 1;
        text-align: center;
        padding: 6px;
        font-size: 12px;
    }

    /* Pagination Styling */
    .pagination-container {
        display: flex;
        justify-content: center;
        width: 100%;
    }
    
    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        gap: 5px;
        margin: 0;
    }
    
    .pagination li {
        display: inline-block;
    }
    
    .pagination li a,
    .pagination li span {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        color: #0a2d29;
        text-decoration: none;
        transition: all 0.3s;
        font-size: 12px;
        background: white;
    }
    
    .pagination li a:hover {
        background-color: #f1cd86;
        border-color: #f1cd86;
        color: #0a2d29;
    }
    
    .pagination li.active span {
        background-color: #0a2d29;
        border-color: #0a2d29;
        color: white;
    }
    
    .pagination li.disabled span {
        color: #999;
        cursor: not-allowed;
        background: #f9f9f9;
    }

    /* Tabs Styling - Theme based */
    .tabs-bar {
        background: #0a2d29; /* Website dark green */
        border-radius: 8px 8px 0 0; /* Rounded top only */
        padding: 5px;
        display: flex;
        gap: 2px;
        margin-bottom: 0;
    }
    
    .tabs-bar::-webkit-scrollbar {
        display: none;
    }
    
    .tab-item {
        display: flex;
        align-items: center;
        justify-content: center; /* Center content */
        flex: 1; /* Distribute equally */
        gap: 8px;
        padding: 8px 10px; /* Adjusted padding */
        color: rgba(255, 255, 255, 0.7);
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.3s;
        white-space: nowrap;
        font-size: 13px;
        font-weight: 500;
        background: transparent;
        border: none;
    }

    .tab-item:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }
    
    .tab-item.active {
        background: #f1cd86; /* Website gold */
        color: #0a2d29; /* Dark green text for contrast */
        font-weight: 700;
    }
    
    .tab-icon {
        width: 16px;
        height: 16px;
    }

    .header-controls {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 15px;
        gap: 20px;
    }

    .header-title {
        font-size: 18px;
        font-weight: 700;
        color: #0a2d29;
        margin: 0;
    }

    .controls-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .search-wrapper {
        position: relative;
        width: 250px;
    }

    .search-wrapper input {
        width: 100%;
        padding: 8px 35px 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
    }

    .search-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aec0;
        width: 16px;
        height: 16px;
    }

    .filter-btn {
        background: #4a5568;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
    }

    .filter-btn:hover {
        background: #2d3748;
    }
    .tab-badge {
        background: #f1cd86;
        color: #0a2d29;
        font-size: 10px;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 10px;
        min-width: 18px;
        text-align: center;
    }
    
    .tab-item.active .tab-badge {
        background: #0a2d29;
        color: white;
    }

    .tab-item:not(.active) .tab-badge {
        background: rgba(241, 205, 134, 0.2);
        color: #f1cd86;
        border: 1px solid rgba(241, 205, 134, 0.3);
    }
</style>

<!-- Header Controls -->
<div class="header-controls">
    
    
    <div class="controls-right">
        <div class="search-wrapper">
            <input type="text" id="searchInput" placeholder="Search" value="{{ request('search') }}">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        
        <div style="position: relative;">
            <button type="button" class="filter-btn" id="filterToggle">
                <svg style="width: 16px; height: 16px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span id="filterBadge" style="background: #f1cd86; color: #0a2d29; border-radius: 50%; width: 18px; height: 18px; display: {{ (request('month_id') || request('client_id') || request('lead_recruiter_id')) ? 'inline-flex' : 'none' }}; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; margin-left: 5px;">
                    {{ collect([request('month_id'), request('client_id'), request('lead_recruiter_id')])->filter()->count() }}
                </span>
            </button>
            
            <div class="filter-dropdown" id="filterDropdown" style="right: 0; top: 45px;">
                <form id="filterForm">
                    <h3>Filter Results</h3>
                    <div class="filter-group">
                        <label for="month_id">Month</label>
                        <select name="month_id" id="month_id">
                            <option value="">All Months</option>
                            @foreach($months as $month)
                                <option value="{{ $month->id }}" {{ request('month_id') == $month->id ? 'selected' : '' }}>{{ $month->month }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="client_id">Client</label>
                        <select name="client_id" id="client_id">
                            <option value="">All Clients</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->client }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="lead_recruiter_id">Lead Recruiter</label>
                        <select name="lead_recruiter_id" id="lead_recruiter_id">
                            <option value="">All Recruiters</option>
                            @foreach($leadRecruiters as $recruiter)
                                <option value="{{ $recruiter->id }}" {{ request('lead_recruiter_id') == $recruiter->id ? 'selected' : '' }}>{{ $recruiter->username }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Apply</button>
                        <button type="button" id="clearFilters" class="btn btn-secondary">Clear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Bar -->
<div class="tabs-bar" id="tabsContainer">
    <div class="tab-item {{ request('tab', 'demand_raised') == 'demand_raised' ? 'active' : '' }}" data-tab="demand_raised">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
        <span>Demand Raised</span>
        <span class="tab-badge">{{ $counts['demand_raised'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'identified' ? 'active' : '' }}" data-tab="identified">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
        <span>Identified</span>
        <span class="tab-badge">{{ $counts['identified'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'screening' ? 'active' : '' }}" data-tab="screening">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
        <span>Initial Screening</span>
        <span class="tab-badge">{{ $counts['screening'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'submission' ? 'active' : '' }}" data-tab="submission">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
        <span>Submission</span>
        <span class="tab-badge">{{ $counts['submission'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'interview' ? 'active' : '' }}" data-tab="interview">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        <span>Interview</span>
        <span class="tab-badge">{{ $counts['interview'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'decision' ? 'active' : '' }}" data-tab="decision">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
        <span>Decision</span>
        <span class="tab-badge">{{ $counts['decision'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'accepted' ? 'active' : '' }}" data-tab="accepted">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>Accepted</span>
        <span class="tab-badge">{{ $counts['accepted'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'rejected' ? 'active' : '' }}" data-tab="rejected">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>Rejected</span>
        <span class="tab-badge">{{ $counts['rejected'] ?? 0 }}</span>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th title="ID">ID</th>
                <th title="Month">Month</th>
                <th title="PRD">PRD</th>
                <th title="Submission Deadline">Deadline</th>
                <th title="Client Name">Client</th>
                <th title="Job Location">Location</th>
                <th title="Position">Position</th>
                <th title="Lead Recruiter">LR</th>
                <th title="Status">Status</th>
                <th title="Actions">Actions</th>
            </tr>
        </thead>
        <tbody id="trackerTableBody">
            @include('tracker._table')
        </tbody>
    </table>
</div>

<!-- Pagination and Count -->
<div class="bottom-info">
    <div id="countText" style="color: #666; font-size: 13px;">
        @if($trackerInfos->total() > 0)
            Showing {{ $trackerInfos->firstItem() }} to {{ $trackerInfos->lastItem() }} of {{ $trackerInfos->total() }} entries
        @else
            No entries found
        @endif
    </div>
    
    <div id="paginationContainer">
        @if($trackerInfos->hasPages())
            <div class="pagination-container" style="margin-top: 0;">
                {{ $trackerInfos->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('trackerTableBody');
        const paginationContainer = document.getElementById('paginationContainer');
        const countText = document.getElementById('countText');
        const filterToggle = document.getElementById('filterToggle');
        const filterDropdown = document.getElementById('filterDropdown');
        const filterForm = document.getElementById('filterForm');
        const clearFilters = document.getElementById('clearFilters');
        const filterBadge = document.getElementById('filterBadge');
        const tabs = document.querySelectorAll('.tab-item');
        let currentTab = 'demand_raised';
        let debounceTimer;
        
        function fetchData(url, params = {}) {
            const fetchUrl = new URL(url);
            Object.keys(params).forEach(key => {
                if (params[key]) fetchUrl.searchParams.set(key, params[key]);
            });
            
            fetch(fetchUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = data.table;
                paginationContainer.innerHTML = data.pagination;
                countText.textContent = data.count_text;
                
                // Update tab badges
                if (data.counts) {
                    Object.keys(data.counts).forEach(tabKey => {
                        const tabItem = document.querySelector(`.tab-item[data-tab="${tabKey}"]`);
                        if (tabItem) {
                            const badge = tabItem.querySelector('.tab-badge');
                            if (badge) badge.textContent = data.counts[tabKey];
                        }
                    });
                }

                // Update filter badge
                const activeFilters = [
                    document.getElementById('month_id').value,
                    document.getElementById('client_id').value,
                    document.getElementById('lead_recruiter_id').value
                ].filter(v => v).length;
                
                if (activeFilters > 0) {
                    filterBadge.textContent = activeFilters;
                    filterBadge.style.display = 'inline-flex';
                } else {
                    filterBadge.style.display = 'none';
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function getParams() {
            return {
                search: searchInput.value,
                month_id: document.getElementById('month_id').value,
                client_id: document.getElementById('client_id').value,
                lead_recruiter_id: document.getElementById('lead_recruiter_id').value,
                tab: currentTab
            };
        }

        // Tab switching
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentTab = this.dataset.tab;
                fetchData('{{ route('tracker.index') }}', getParams());
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchData('{{ route('tracker.index') }}', getParams());
                }, 500);
            });
        }

        if (filterToggle) {
            filterToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                filterDropdown.classList.toggle('active');
            });
        }

        document.addEventListener('click', (e) => {
            if (filterDropdown && !filterDropdown.contains(e.target) && e.target !== filterToggle) {
                filterDropdown.classList.remove('active');
            }
        });

        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            fetchData('{{ route('tracker.index') }}', getParams());
            filterDropdown.classList.remove('active');
        });

        clearFilters.addEventListener('click', () => {
            filterForm.reset();
            fetchData('{{ route('tracker.index') }}', getParams());
            filterDropdown.classList.remove('active');
        });

        document.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('#paginationContainer a');
            if (paginationLink) {
                e.preventDefault();
                fetchData(paginationLink.getAttribute('href'), getParams());
            }
        });
    });
</script>
@endsection
