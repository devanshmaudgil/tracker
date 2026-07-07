@extends('layouts.app')

@section('title', 'Locations')

@section('content')
@include('partials._crud_theme')

<div class="crud-page">

    {{-- Hero --}}
    <header class="crud-hero crud-enter crud-enter-1">
        <div>
            <div class="crud-hero__eyebrow">RADiiX INFINITEii</div>
            <h1 class="crud-hero__title">Locations</h1>
            <p class="crud-hero__sub">Cities and states used across job demands and candidate locations.</p>
        </div>
        <button type="button" class="crud-btn-add" onclick="openModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Locations
        </button>
    </header>

    {{-- Stats --}}
    <div class="crud-stats crud-enter crud-enter-2">
        <div class="crud-stat">
            <div class="crud-stat__num">{{ $stats['total'] }}</div>
            <div class="crud-stat__lbl">Total Entries</div>
        </div>
        <div class="crud-stat">
            <div class="crud-stat__num">{{ $stats['states'] }}</div>
            <div class="crud-stat__lbl">States / Provinces</div>
        </div>
        <div class="crud-stat">
            <div class="crud-stat__num">{{ $stats['cities'] }}</div>
            <div class="crud-stat__lbl">Cities</div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="crud-toolbar crud-enter crud-enter-3">
        <div class="crud-search">
            <svg class="crud-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="search"
                   id="searchInput"
                   class="crud-search__input"
                   placeholder="Search by city or state"
                   value="{{ request('search') }}"
                   autocomplete="off">
        </div>
        <div class="crud-search-meta" id="resultsCount">
            @if($regions->total() > 0)
                Showing {{ $regions->firstItem() }} to {{ $regions->lastItem() }} of {{ $regions->total() }} entries
            @else
                No entries found
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="crud-table-card crud-enter crud-enter-4">
        <div class="crud-table-scroll">
            <table class="crud-table">
                <thead>
                    <tr>
                        <th style="width:60px;">S.No</th>
                        <th class="is-left">City</th>
                        <th>State / Province</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="regionsTableBody">
                    @include('regions._table')
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div id="paginationContainer">
        @if($regions->hasPages())
            <div class="crud-pagination">
                {{ $regions->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        @endif
    </div>
</div>

{{-- Add / Edit Region Modal --}}
<div id="regionModal" class="crud-modal-overlay">
    <div class="crud-modal" role="dialog" aria-labelledby="regionModalTitle">
        <div class="crud-modal__head">
            <h2 id="regionModalTitle">Add Location</h2>
            <button type="button" class="crud-modal__close" onclick="closeModal()" aria-label="Close">&times;</button>
        </div>
        <form id="regionForm" method="POST" action="{{ route('locations.store') }}">
            @csrf
            <input type="hidden" id="regionFormMethod" name="_method" value="POST">
            <div class="crud-modal__body">
                <div class="crud-field">
                    <label for="region">State / Province *</label>
                    <input type="text" id="region" name="region" value="{{ old('region') }}" required placeholder="e.g. Texas">
                    @error('region')<div class="crud-field__error">{{ $message }}</div>@enderror
                </div>
                <div class="crud-field">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="e.g. Austin (optional)">
                    @error('city')<div class="crud-field__error">{{ $message }}</div>@enderror
                </div>
                <div class="crud-modal__foot">
                    <button type="button" class="crud-btn crud-btn--ghost" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="crud-btn crud-btn--primary" id="regionSubmitBtn">Save Location</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(recordId = null, rowElement = null) {
        const overlay = document.getElementById('regionModal');
        const form = document.getElementById('regionForm');
        const title = document.getElementById('regionModalTitle');
        const submitBtn = document.getElementById('regionSubmitBtn');
        const method = document.getElementById('regionFormMethod');

        if (recordId && rowElement) {
            title.textContent = 'Edit Location';
            submitBtn.textContent = 'Update Location';
            method.value = 'PUT';
            form.action = `/locations/${recordId}`;
            document.getElementById('region').value = rowElement.dataset.region || '';
            document.getElementById('city').value = rowElement.dataset.city || '';
        } else {
            title.textContent = 'Add Location';
            submitBtn.textContent = 'Save Location';
            method.value = 'POST';
            form.action = '{{ route('locations.store') }}';
            form.reset();
        }
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.getElementById('region').focus();
    }

    function closeModal() {
        document.getElementById('regionModal').classList.remove('is-open');
        document.body.style.overflow = '';
        document.getElementById('regionForm').reset();
    }

    function editRecord(id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        openModal(id, row);
    }

    document.getElementById('regionModal').addEventListener('click', function (e) {
        if (e.target === this) closeModal();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeModal();
    });

    // AJAX search + pagination
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('regionsTableBody');
        const paginationContainer = document.getElementById('paginationContainer');
        const resultsCount = document.getElementById('resultsCount');
        let debounceTimer;

        function fetchData(url, search = '') {
            const fetchUrl = new URL(url, window.location.origin);
            if (search) fetchUrl.searchParams.set('search', search);

            fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = data.table;
                    paginationContainer.innerHTML = data.pagination
                        ? `<div class="crud-pagination">${data.pagination}</div>`
                        : '';
                    resultsCount.textContent = data.count_text;
                })
                .catch(error => console.error('Error:', error));
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                const searchTerm = this.value;
                debounceTimer = setTimeout(() => {
                    fetchData('{{ route('locations.index') }}', searchTerm);
                }, 400);
            });
        }

        document.addEventListener('click', function (e) {
            const paginationLink = e.target.closest('#paginationContainer a');
            if (paginationLink) {
                e.preventDefault();
                fetchData(paginationLink.getAttribute('href'), searchInput ? searchInput.value : '');
            }
        });
    });

    @if($errors->any())
        openModal();
    @endif
</script>
@endsection
