@extends('layouts.app')

@section('title', 'Clients')

@section('content')
@php
    $directCount = $clients->where('type', 'Direct')->count();
    $endCount = $clients->where('type', 'End')->count();
    $activeJobs = $clients->sum('trackers_count');
@endphp

@include('partials._crud_theme')

<div class="crud-page">

    {{-- Hero --}}
    <header class="crud-hero crud-enter crud-enter-1">
        <div>
            <div class="crud-hero__eyebrow">RADiiX INFINITEii</div>
            <h1 class="crud-hero__title">Clients</h1>
            <p class="crud-hero__sub">Manage your direct and end clients, and see how many job demands each one drives.</p>
        </div>
        <button type="button" class="crud-btn-add" onclick="openClientModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Client
        </button>
    </header>

    {{-- Stats --}}
    <div class="crud-stats crud-enter crud-enter-2">
        <div class="crud-stat">
            <div class="crud-stat__num">{{ $clients->count() }}</div>
            <div class="crud-stat__lbl">Total Clients</div>
        </div>
        <div class="crud-stat">
            <div class="crud-stat__num">{{ $directCount }}</div>
            <div class="crud-stat__lbl">Direct Clients</div>
        </div>
        <div class="crud-stat">
            <div class="crud-stat__num">{{ $endCount }}</div>
            <div class="crud-stat__lbl">End Clients</div>
        </div>
        <div class="crud-stat">
            <div class="crud-stat__num">{{ $activeJobs }}</div>
            <div class="crud-stat__lbl">Linked Job Demands</div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="crud-tabs crud-enter crud-enter-3" role="tablist">
        <button type="button" class="crud-tab is-active" data-tab="Direct" role="tab">
            Direct Client <span class="crud-tab__count">{{ $directCount }}</span>
        </button>
        <button type="button" class="crud-tab" data-tab="End" role="tab">
            End Client <span class="crud-tab__count">{{ $endCount }}</span>
        </button>
    </div>

    {{-- Toolbar --}}
    <div class="crud-toolbar crud-enter crud-enter-3">
        <div class="crud-search">
            <svg class="crud-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="search" id="clientSearch" class="crud-search__input" placeholder="Search clients" autocomplete="off">
        </div>
        <div class="crud-search-meta" id="clientMeta"><strong>{{ $directCount }}</strong> clients</div>
    </div>

    {{-- Table --}}
    <div class="crud-table-card crud-enter crud-enter-4">
        <div class="crud-table-scroll">
            <table class="crud-table">
                <thead>
                    <tr>
                        <th style="width:60px;">S.No</th>
                        <th class="is-left">Client</th>
                        <th>Type</th>
                        <th>Job Demands</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="clientTableBody">
                    @forelse($clients as $client)
                        <tr class="client-row"
                            data-id="{{ $client->id }}"
                            data-client="{{ $client->client }}"
                            data-type="{{ $client->type }}"
                            data-search="{{ strtolower($client->client) }}">
                            <td><span class="crud-index js-index"></span></td>
                            <td class="is-left"><span class="crud-name">{{ $client->client }}</span></td>
                            <td>
                                @if($client->type === 'End')
                                    <span class="crud-badge crud-badge--gold">End Client</span>
                                @else
                                    <span class="crud-badge">Direct Client</span>
                                @endif
                            </td>
                            <td>
                                <span class="crud-count-pill {{ $client->trackers_count ? '' : 'crud-count-pill--zero' }}">{{ $client->trackers_count }}</span>
                            </td>
                            <td>
                                <div class="crud-actions">
                                    <button type="button" class="crud-act" onclick="editClient({{ $client->id }})">Edit</button>
                                    <form method="POST" action="{{ route('clients.info.destroy', $client->id) }}" style="display:inline;" onsubmit="return confirm('Delete this client?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="crud-act crud-act--danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="crud-empty" id="clientEmpty" hidden>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
            <h3>No clients here</h3>
            <p>Try a different search, switch tabs, or <a href="#" onclick="openClientModal(); return false;">add a client</a>.</p>
        </div>
    </div>
</div>

{{-- Add / Edit Client Modal --}}
<div id="clientModal" class="crud-modal-overlay">
    <div class="crud-modal" role="dialog" aria-labelledby="clientModalTitle">
        <div class="crud-modal__head">
            <h2 id="clientModalTitle">Add Client</h2>
            <button type="button" class="crud-modal__close" onclick="closeClientModal()" aria-label="Close">&times;</button>
        </div>
        <form id="clientForm" method="POST" action="{{ route('clients.info.store') }}">
            @csrf
            <input type="hidden" id="clientFormMethod" name="_method" value="POST">
            <div class="crud-modal__body">
                <div class="crud-field">
                    <label for="client">Client Name *</label>
                    <input type="text" id="client" name="client" value="{{ old('client') }}" required placeholder="e.g. Accenture">
                    @error('client')<div class="crud-field__error">{{ $message }}</div>@enderror
                </div>
                <div class="crud-field">
                    <label>Client Type *</label>
                    <div class="crud-seg">
                        <input type="radio" id="typeDirect" name="type" value="Direct" checked>
                        <label for="typeDirect">Direct Client</label>
                        <input type="radio" id="typeEnd" name="type" value="End">
                        <label for="typeEnd">End Client</label>
                    </div>
                    @error('type')<div class="crud-field__error">{{ $message }}</div>@enderror
                </div>
                <div class="crud-modal__foot">
                    <button type="button" class="crud-btn crud-btn--ghost" onclick="closeClientModal()">Cancel</button>
                    <button type="submit" class="crud-btn crud-btn--primary" id="clientSubmitBtn">Save Client</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const clientState = { tab: 'Direct', search: '' };

    function refreshClientTable() {
        const rows = document.querySelectorAll('#clientTableBody .client-row');
        let visible = 0;
        rows.forEach(row => {
            const matchTab = row.dataset.type === clientState.tab;
            const matchSearch = !clientState.search || row.dataset.search.includes(clientState.search);
            const show = matchTab && matchSearch;
            row.classList.toggle('is-hidden', !show);
            if (show) {
                visible++;
                const idx = row.querySelector('.js-index');
                if (idx) idx.textContent = visible;
            }
        });
        document.getElementById('clientEmpty').hidden = visible > 0;
        document.getElementById('clientMeta').innerHTML = `<strong>${visible}</strong> clients`;
    }

    document.querySelectorAll('.crud-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.crud-tab').forEach(t => t.classList.remove('is-active'));
            tab.classList.add('is-active');
            clientState.tab = tab.dataset.tab;
            refreshClientTable();
        });
    });

    document.getElementById('clientSearch').addEventListener('input', function () {
        clientState.search = this.value.trim().toLowerCase();
        refreshClientTable();
    });

    function openClientModal(recordId = null, rowElement = null) {
        const overlay = document.getElementById('clientModal');
        const form = document.getElementById('clientForm');
        const title = document.getElementById('clientModalTitle');
        const submitBtn = document.getElementById('clientSubmitBtn');
        const method = document.getElementById('clientFormMethod');

        if (recordId && rowElement) {
            title.textContent = 'Edit Client';
            submitBtn.textContent = 'Update Client';
            method.value = 'PUT';
            form.action = `/clients/info/${recordId}`;
            document.getElementById('client').value = rowElement.dataset.client || '';
            const type = rowElement.dataset.type === 'End' ? 'typeEnd' : 'typeDirect';
            document.getElementById(type).checked = true;
        } else {
            title.textContent = 'Add Client';
            submitBtn.textContent = 'Save Client';
            method.value = 'POST';
            form.action = '{{ route('clients.info.store') }}';
            form.reset();
            // Default new clients to whichever tab is active
            document.getElementById(clientState.tab === 'End' ? 'typeEnd' : 'typeDirect').checked = true;
        }
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.getElementById('client').focus();
    }

    function closeClientModal() {
        document.getElementById('clientModal').classList.remove('is-open');
        document.body.style.overflow = '';
        document.getElementById('clientForm').reset();
    }

    function editClient(id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        openClientModal(id, row);
    }

    document.getElementById('clientModal').addEventListener('click', function (e) {
        if (e.target === this) closeClientModal();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeClientModal();
    });

    refreshClientTable();

    @if($errors->any())
        openClientModal();
    @endif
</script>
@endsection
