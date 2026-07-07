@extends('layouts.app')

@section('title', 'Months')

@section('content')
@include('partials._crud_theme')

<div class="crud-page">

    {{-- Hero --}}
    <header class="crud-hero crud-enter crud-enter-1">
        <div>
            <div class="crud-hero__eyebrow">RADiiX INFINITEii</div>
            <h1 class="crud-hero__title">Months</h1>
            <p class="crud-hero__sub">Tracking periods used to organise job demands, newest first.</p>
        </div>
        <button type="button" class="crud-btn-add" onclick="openMonthModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Month
        </button>
    </header>

    {{-- Stats --}}
    <div class="crud-stats crud-enter crud-enter-2">
        <div class="crud-stat">
            <div class="crud-stat__num">{{ $months->count() }}</div>
            <div class="crud-stat__lbl">Total Months</div>
        </div>
        <div class="crud-stat">
            <div class="crud-stat__num">{{ $months->sum('trackers_count') }}</div>
            <div class="crud-stat__lbl">Linked Job Demands</div>
        </div>
        <div class="crud-stat">
            <div class="crud-stat__num">{{ $months->first()->month ?? '—' }}</div>
            <div class="crud-stat__lbl">Latest Month</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="crud-table-card crud-enter crud-enter-3">
        <div class="crud-table-scroll">
            <table class="crud-table">
                <thead>
                    <tr>
                        <th style="width:60px;">S.No</th>
                        <th class="is-left">Month</th>
                        <th>Job Demands</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($months as $index => $month)
                        <tr data-id="{{ $month->id }}" data-month="{{ $month->month }}">
                            <td><span class="crud-index">{{ $index + 1 }}</span></td>
                            <td class="is-left"><span class="crud-name">{{ $month->month }}</span></td>
                            <td>
                                <span class="crud-count-pill {{ $month->trackers_count ? '' : 'crud-count-pill--zero' }}">{{ $month->trackers_count }}</span>
                            </td>
                            <td>
                                <div class="crud-actions">
                                    <button type="button" class="crud-act" onclick="editMonth({{ $month->id }})">Edit</button>
                                    <form method="POST" action="{{ route('months.destroy', $month->id) }}" style="display:inline;" onsubmit="return confirm('Delete this month?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="crud-act crud-act--danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="crud-empty">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <h3>No months yet</h3>
                                    <p><a href="#" onclick="openMonthModal(); return false;">Add your first month</a> to start tracking.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add / Edit Month Modal --}}
<div id="monthModal" class="crud-modal-overlay">
    <div class="crud-modal" role="dialog" aria-labelledby="monthModalTitle">
        <div class="crud-modal__head">
            <h2 id="monthModalTitle">Add Month</h2>
            <button type="button" class="crud-modal__close" onclick="closeMonthModal()" aria-label="Close">&times;</button>
        </div>
        <form id="monthForm" method="POST" action="{{ route('months.store') }}">
            @csrf
            <input type="hidden" id="monthFormMethod" name="_method" value="POST">
            <div class="crud-modal__body">
                <div class="crud-field">
                    <label for="month">Month *</label>
                    <input type="text" id="month" name="month" value="{{ old('month') }}" required placeholder="e.g. August 2026">
                    <div class="crud-field__hint">Use the format "Month Year", e.g. August 2026.</div>
                    @error('month')<div class="crud-field__error">{{ $message }}</div>@enderror
                </div>
                <div class="crud-modal__foot">
                    <button type="button" class="crud-btn crud-btn--ghost" onclick="closeMonthModal()">Cancel</button>
                    <button type="submit" class="crud-btn crud-btn--primary" id="monthSubmitBtn">Save Month</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openMonthModal(recordId = null, rowElement = null) {
        const overlay = document.getElementById('monthModal');
        const form = document.getElementById('monthForm');
        const title = document.getElementById('monthModalTitle');
        const submitBtn = document.getElementById('monthSubmitBtn');
        const method = document.getElementById('monthFormMethod');

        if (recordId && rowElement) {
            title.textContent = 'Edit Month';
            submitBtn.textContent = 'Update Month';
            method.value = 'PUT';
            form.action = `/months/${recordId}`;
            document.getElementById('month').value = rowElement.dataset.month || '';
        } else {
            title.textContent = 'Add Month';
            submitBtn.textContent = 'Save Month';
            method.value = 'POST';
            form.action = '{{ route('months.store') }}';
            form.reset();
        }
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.getElementById('month').focus();
    }

    function closeMonthModal() {
        document.getElementById('monthModal').classList.remove('is-open');
        document.body.style.overflow = '';
        document.getElementById('monthForm').reset();
    }

    function editMonth(id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        openMonthModal(id, row);
    }

    document.getElementById('monthModal').addEventListener('click', function (e) {
        if (e.target === this) closeMonthModal();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeMonthModal();
    });

    @if($errors->any())
        openMonthModal();
    @endif
</script>
@endsection
