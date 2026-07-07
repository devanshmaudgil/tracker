@extends('layouts.app')

@section('title', 'Candidates')
@section('page_heading', 'Candidate Database')

@section('content')
@php
    $totalCount = $stats['total'];
    $withResume = $stats['with_resume'];
    $withJobs = $stats['with_jobs'];
@endphp

@include('candidates._styles')

<div class="cand-page">

    {{-- Hero --}}
    <header class="cand-hero cand-enter cand-enter-1">
        <div>
            <div class="cand-hero__eyebrow">RADiiX INFINITEii</div>
            <h1 class="cand-hero__title">Candidate Database</h1>
            <p class="cand-hero__sub">Search, filter, and manage your talent pool. Matches highlight live as you type — character by character.</p>
        </div>
        <button type="button" class="cand-btn-add" onclick="openCandidateModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Candidate
        </button>
    </header>

    {{-- Stats --}}
    <div class="cand-stats cand-enter cand-enter-2">
        <div class="cand-stat">
            <div class="cand-stat__num" id="statTotal">{{ $totalCount }}</div>
            <div class="cand-stat__lbl">Total Candidates</div>
        </div>
        <div class="cand-stat">
            <div class="cand-stat__num">{{ $withResume }}</div>
            <div class="cand-stat__lbl">With Resume</div>
        </div>
        <div class="cand-stat">
            <div class="cand-stat__num">{{ $withJobs }}</div>
            <div class="cand-stat__lbl">On Active Jobs</div>
        </div>
        <div class="cand-stat">
            <div class="cand-stat__num">{{ $totalCount - $withJobs }}</div>
            <div class="cand-stat__lbl">Unassigned</div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="cand-toolbar cand-enter cand-enter-3">
        <div class="cand-search" id="candSearchWrap">
            <svg class="cand-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="search"
                   id="candSearchInput"
                   class="cand-search__input"
                   placeholder="Search Candidates"
                   value="{{ request('search') }}"
                   autocomplete="off"
                   spellcheck="false">
            <button type="button" class="cand-search__clear" id="candSearchClear" aria-label="Clear search">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="cand-search-meta" id="candSearchMeta">
            @if($candidates->total() > 0)
                Showing <strong>{{ $candidates->firstItem() }}</strong>–<strong>{{ $candidates->lastItem() }}</strong> of <strong>{{ $candidates->total() }}</strong> candidates
            @else
                No candidates found
            @endif
        </div>
        <div class="cand-chips">
            <button type="button" class="cand-chip {{ !request('work_status') ? 'is-active' : '' }}" data-filter="">All</button>
            @foreach(['Citizen', 'GC', 'H1B', 'OPT', 'PR'] as $ws)
                @if(($statusCounts[$ws] ?? 0) > 0)
                    <button type="button" class="cand-chip {{ request('work_status') === $ws ? 'is-active' : '' }}" data-filter="{{ $ws }}">{{ $ws }} ({{ $statusCounts[$ws] }})</button>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Table --}}
    <div class="cand-table-card cand-enter cand-enter-4" id="candTableCard">
        <div class="cand-table-scroll">
            <table class="cand-table">
                <thead>
                    <tr>
                        <th class="cand-col-person">Candidate</th>
                        <th>Phone</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Company</th>
                        <th>Rate</th>
                        <th>Jobs</th>
                        <th>Resume</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="candTableBody">
                    @include('candidates._table')
                </tbody>
            </table>
        </div>
    </div>

    <div id="paginationContainer">
        @include('candidates._pagination')
    </div>
</div>

{{-- Candidate Detail Card --}}
<div id="candDetailOverlay" class="cand-detail-overlay" role="dialog" aria-labelledby="candDetailName">
    <div class="cand-detail-card">
        <div class="cand-detail-card__head">
            <div class="cand-detail-card__avatar" id="candDetailAvatar">?</div>
            <div>
                <div class="cand-detail-card__name" id="candDetailName">Candidate</div>
                <div class="cand-detail-card__email" id="candDetailEmail"></div>
            </div>
            <button type="button" class="cand-detail-card__close" onclick="closeCandidateDetail()" aria-label="Close">&times;</button>
        </div>
        <div class="cand-detail-card__body">
            <div class="cand-detail-card__grid">
                <div class="cand-detail-card__item">
                    <label>Phone</label>
                    <span id="candDetailPhone">—</span>
                </div>
                <div class="cand-detail-card__item">
                    <label>Location</label>
                    <span id="candDetailLocation">—</span>
                </div>
                <div class="cand-detail-card__item">
                    <label>Work Status</label>
                    <span id="candDetailStatus">—</span>
                </div>
                <div class="cand-detail-card__item">
                    <label>Current Company</label>
                    <span id="candDetailCompany">—</span>
                </div>
                <div class="cand-detail-card__item">
                    <label>Pay Rate</label>
                    <span id="candDetailRate">—</span>
                </div>
                <div class="cand-detail-card__item">
                    <label>Placement Pay Rate</label>
                    <span id="candDetailPlacementRate">—</span>
                </div>
                <div class="cand-detail-card__item">
                    <label>Agency</label>
                    <span id="candDetailAgency">—</span>
                </div>
                <div class="cand-detail-card__item">
                    <label>Agency POC</label>
                    <span id="candDetailAgencyPoc">—</span>
                </div>
                <div class="cand-detail-card__item">
                    <label>POC Phone</label>
                    <span id="candDetailAgencyPhone">—</span>
                </div>
                <div class="cand-detail-card__item">
                    <label>Job Demands</label>
                    <span id="candDetailJobs">—</span>
                </div>
                <div class="cand-detail-card__item">
                    <label>Resume</label>
                    <span id="candDetailResume">—</span>
                </div>
                <div class="cand-detail-card__item span-2">
                    <label>Summary</label>
                    <span id="candDetailSummary">—</span>
                </div>
                <div class="cand-detail-card__item span-2">
                    <label>Remarks</label>
                    <span id="candDetailRemarks">—</span>
                </div>
            </div>
        </div>
        <div class="cand-detail-card__foot">
            <button type="button" class="cand-btn cand-btn--ghost" onclick="closeCandidateDetail()">Close</button>
            <a href="#" id="candDetailEditBtn" class="cand-btn cand-btn--primary">Edit Candidate</a>
        </div>
    </div>
</div>

{{-- Add Candidate Modal --}}
<div id="candidateModal" class="cand-modal-overlay">
    <div class="cand-modal" role="dialog" aria-labelledby="candModalTitle">
        <div class="cand-modal__head">
            <h2 id="candModalTitle">Add Candidate</h2>
            <button type="button" class="cand-modal__close" onclick="closeCandidateModal()" aria-label="Close">&times;</button>
        </div>
        <form id="candidateForm" method="POST" action="{{ route('candidates.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="cand-modal__body">
                <div class="cand-form-grid">
                    <div class="cand-field">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                        @error('full_name')<div style="color:#dc2626;font-size:11px;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="cand-field">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')<div style="color:#dc2626;font-size:11px;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="cand-field">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}">
                    </div>
                    <div class="cand-field">
                        <label for="location_id">Location</label>
                        <select id="location_id" name="location_id">
                            <option value="">Select location</option>
                            @foreach(\App\Models\Region::orderBy('region')->get() as $region)
                                <option value="{{ $region->id }}" @selected(old('location_id') == $region->id)>
                                    {{ $region->city ? $region->city . ', ' : '' }}{{ $region->region }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="cand-field">
                        <label for="location_text">Location (free text)</label>
                        <input type="text" id="location_text" name="location_text" value="{{ old('location_text') }}" placeholder="e.g. Hyderabad, India">
                    </div>
                    <div class="cand-field">
                        <label for="work_status">Work Status</label>
                        <select id="work_status" name="work_status">
                            <option value="">Select</option>
                            @foreach(['GC','PR','Citizen','H1B','OPT'] as $ws)
                                <option value="{{ $ws }}" @selected(old('work_status') == $ws)>{{ $ws }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="cand-field">
                        <label for="current_company">Current Company</label>
                        <input type="text" id="current_company" name="current_company" value="{{ old('current_company') }}">
                    </div>
                    <div class="cand-field">
                        <label for="pay_rate">Pay Rate</label>
                        <input type="text" id="pay_rate" name="pay_rate" value="{{ old('pay_rate') }}" placeholder="e.g. $50/hr">
                    </div>
                    <div class="cand-field">
                        <label for="placement_pay_rate">Placement Pay Rate</label>
                        <input type="text" id="placement_pay_rate" name="placement_pay_rate" value="{{ old('placement_pay_rate') }}" placeholder="e.g. 30 LPA">
                    </div>
                    <div class="cand-field span-2">
                        <label for="summary">Summary</label>
                        <textarea id="summary" name="summary" rows="2" placeholder="Skills, experience, notice period…">{{ old('summary') }}</textarea>
                    </div>
                    <div class="cand-field span-2">
                        <label for="remarks">Remarks</label>
                        <textarea id="remarks" name="remarks" rows="2" placeholder="Internal notes about this candidate…">{{ old('remarks') }}</textarea>
                    </div>
                    <div class="cand-field">
                        <label for="agency_name">Agency Name</label>
                        <input type="text" id="agency_name" name="agency_name" value="{{ old('agency_name') }}">
                    </div>
                    <div class="cand-field">
                        <label for="agency_poc">Agency POC</label>
                        <input type="text" id="agency_poc" name="agency_poc" value="{{ old('agency_poc') }}">
                    </div>
                    <div class="cand-field">
                        <label for="agency_poc_phone">POC Phone</label>
                        <input type="text" id="agency_poc_phone" name="agency_poc_phone" value="{{ old('agency_poc_phone') }}">
                    </div>
                    <div class="cand-field span-2">
                        <label for="resume_file">Resume (PDF / JPG / PNG)</label>
                        <input type="file" id="resume_file" name="resume_file" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="cand-modal__foot">
                    <button type="button" class="cand-btn cand-btn--ghost" onclick="closeCandidateModal()">Cancel</button>
                    <button type="submit" class="cand-btn cand-btn--primary">Save Candidate</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('candidates._scripts')
@endsection
