@extends('layouts.app')

@section('title', 'Notes & Tasks')
@section('page_heading', 'Notes & Tasks')

@section('content')
<style>
    .notes-page {
        max-width: 1040px;
        margin: 0 auto;
        padding-bottom: 48px;
        position: relative;
        isolation: isolate;
    }

    @keyframes notesFadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .notes-enter { animation: notesFadeUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) backwards; }
    .notes-enter-1 { animation-delay: 0.04s; }
    .notes-enter-2 { animation-delay: 0.1s; }
    .notes-enter-3 { animation-delay: 0.16s; }

    .notes-hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .notes-hero__title {
        font-size: clamp(1.6rem, 3vw, 2.1rem);
        font-weight: 800;
        color: var(--teal-deep, #0a2d29);
        letter-spacing: -0.02em;
        margin: 0;
    }

    .notes-hero__sub {
        margin-top: 6px;
        color: var(--teal-matte, #0f3d37);
        opacity: 0.8;
        font-size: 14px;
        max-width: 520px;
    }

    .notes-grid {
        display: grid;
        grid-template-columns: 0.95fr 1.35fr;
        gap: 16px;
    }

    @media (max-width: 900px) {
        .notes-grid { grid-template-columns: 1fr; }
    }

    .notes-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.96), #ffffff);
        border: 1px solid rgba(229, 231, 235, 0.9);
        border-radius: 14px;
        box-shadow:
            0 10px 40px rgba(10, 45, 41, 0.05),
            0 0 0 1px rgba(241,205,134,0.08) inset;
        padding: 16px 16px;
        transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
    }
    .notes-card:hover {
        transform: translateY(-1px);
        border-color: rgba(241, 205, 134, 0.65);
        box-shadow:
            0 14px 48px rgba(10, 45, 41, 0.08),
            0 0 0 1px rgba(241,205,134,0.12) inset;
    }

    .notes-card__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        padding: 14px 16px 12px;
        margin-left: -16px;
        margin-right: -16px;
        border-bottom: 3px solid rgba(241, 205, 134, 0.95);
        background: var(--teal-deep, #0a2d29);
    }

    .notes-card__title {
        font-weight: 800;
        color: #ffffff;
        position: relative;
    }

    .notes-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        background: rgba(241, 205, 134, 0.18);
        border: 1px solid rgba(241, 205, 134, 0.42);
        color: var(--teal-deep, #0a2d29);
    }

    .notes-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 12px;
    }

    @media (max-width: 520px) {
        .notes-form-row { grid-template-columns: 1fr; }
    }

    .notes-label {
        font-size: 12px;
        font-weight: 800;
        color: #374151;
        margin-bottom: 6px;
        letter-spacing: 0.02em;
    }

    .notes-input, .notes-textarea, .notes-select {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 11px 12px;
        font-size: 14px;
        outline: none;
        transition: border-color .2s ease, box-shadow .2s ease;
        background: #fff;
    }

    .notes-textarea { min-height: 120px; resize: vertical; }
    .notes-input:focus, .notes-textarea:focus, .notes-select:focus {
        border-color: rgba(10, 45, 41, 0.35);
        box-shadow: 0 0 0 3px rgba(241, 205, 134, 0.25);
    }

    .notes-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 12px 18px;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 800;
        transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease;
        text-decoration: none;
        font-family: inherit;
    }

    .notes-btn--primary {
        background: linear-gradient(135deg, var(--teal-deep, #0a2d29) 0%, var(--teal-matte, #0f3d37) 100%);
        color: var(--gold, #f1cd86);
        box-shadow: 0 6px 18px rgba(10, 45, 41, 0.18);
    }
    .notes-btn--primary:hover { transform: translateY(-2px); }

    .notes-btn--ghost {
        background: transparent;
        border: 1px solid #e5e7eb;
        color: #374151;
    }
    .notes-btn--ghost:hover { transform: translateY(-1px); border-color: rgba(241, 205, 134, 0.6); }

    .notes-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
        margin-top: 12px;
    }

    .notes-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0 0 12px 0;
    }
    .notes-tab {
        border: 1px solid rgba(229, 231, 235, 0.95);
        border-radius: 999px;
        padding: 8px 12px;
        background: #fff;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        color: #374151;
        transition: transform .2s ease, background .2s ease, border-color .2s ease, color .2s ease;
    }
    .notes-tab:hover { transform: translateY(-1px); }
    .notes-tab.is-active {
        background: rgba(241, 205, 134, 0.2);
        border-color: rgba(241, 205, 134, 0.7);
        color: var(--teal-deep, #0a2d29);
    }

    .notes-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .notes-item {
        border: 1px solid rgba(241, 205, 134, 0.28);
        border-radius: 14px;
        background: linear-gradient(180deg, rgba(10, 45, 41, 0.96), rgba(10, 45, 41, 0.92));
        padding: 12px 14px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 12px;
        transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        box-shadow: 0 6px 24px rgba(10, 45, 41, 0.04);
    }
    .notes-item:hover {
        transform: translateY(-1px);
        border-color: rgba(241, 205, 134, 0.75);
        box-shadow: 0 18px 40px rgba(10, 45, 41, 0.16);
    }

    .notes-item.is-done {
        opacity: 0.8;
    }

    .notes-item__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-bottom: 6px;
    }

    .notes-kind {
        font-size: 12px;
        font-weight: 900;
        padding: 5px 10px;
        border-radius: 999px;
        border: 1px solid rgba(241, 205, 134, 0.35);
        background: rgba(241, 205, 134, 0.12);
        color: var(--gold, #f1cd86);
    }

    .notes-when {
        font-size: 12px;
        font-weight: 800;
        color: rgba(255, 255, 255, 0.78);
    }

    .notes-title {
        font-size: 15px;
        font-weight: 900;
        margin-bottom: 6px;
        color: var(--gold, #f1cd86);
    }

    .notes-body {
        color: #ffffff;
        font-size: 14px;
        line-height: 1.55;
    }
    .notes-body.is-trim {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .notes-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.06);
        cursor: pointer;
        transition: transform .2s ease, background .2s ease, border-color .2s ease, color .2s ease;
        color: #ffffff;
    }
    .notes-pill.is-done { background: rgba(5, 150, 105, 0.22); border-color: rgba(52, 211, 153, 0.35); color: #34d399; }
    .notes-pill.is-open { background: rgba(241, 205, 134, 0.22); border-color: rgba(241, 205, 134, 0.45); color: var(--gold, #f1cd86); }

    .notes-item__right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 10px;
        min-width: 180px;
    }

    .notes-item__btnrow {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .notes-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.06);
        color: rgba(255, 255, 255, 0.92);
        text-decoration: none;
        font-weight: 900;
        font-size: 13px;
        transition: transform .2s ease, border-color .2s ease;
    }

    .notes-link:hover { transform: translateY(-1px); border-color: rgba(241, 205, 134, 0.6); }

    .notes-link--danger {
        color: #f87171;
        border-color: rgba(248, 113, 113, 0.3);
    }

    .notes-search {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 12px;
    }

    .notes-search input {
        flex: 1;
    }

    .notes-alert {
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 14px;
        font-weight: 800;
        border: 1px solid transparent;
    }
    .notes-alert--success {
        background: #ecfdf5;
        color: #065f46;
        border-color: #a7f3d0;
    }
    .notes-alert--error {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
    }
</style>

<div class="notes-page">
    <div class="notes-hero notes-enter notes-enter-1">
        <div>
            <h1 class="notes-hero__title">Notes, Tasks & Daily Reminders</h1>
            <div class="notes-hero__sub">
                Type quickly on the left, and keep everything organized in one place.
            </div>
        </div>
        <div class="notes-badge">
            Today: {{ $today ?? now()->toDateString() }}
        </div>
    </div>

    @if(! $staff)
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 12px; padding: 14px 16px; margin-bottom: 18px;">
            Notes cannot be loaded until your user account is linked to a staff profile.
        </div>
    @else
        <div class="notes-grid">
            <div class="notes-card notes-enter notes-enter-2">
                <div class="notes-card__head">
                    <div class="notes-card__title">Quick Add</div>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 12px; padding: 14px 16px; margin-bottom: 14px;">
                        Please fix the highlighted fields.
                    </div>
                @endif

                <form method="POST" action="{{ route('notes.store') }}" id="notesQuickForm">
                    @csrf
                    <div class="notes-form-row">
                        <div>
                            <div class="notes-label">Type</div>
                            <select class="notes-select" name="kind" id="notesKind">
                                <option value="note" @selected(old('kind','note')==='note')>Note</option>
                                <option value="task" @selected(old('kind')==='task')>Task</option>
                                <option value="reminder" @selected(old('kind')==='reminder')>Daily Reminder</option>
                            </select>
                        </div>
                        <div>
                            <div class="notes-label">Date</div>
                            <input class="notes-input" type="date" name="due_date" id="notesDueDate" value="{{ old('due_date') }}">
                            <input class="notes-input" type="date" name="reminder_date" id="notesReminderDate" value="{{ old('reminder_date') }}" style="display:none;">
                        </div>
                    </div>

                    <div class="notes-form-row" style="grid-template-columns: 1fr;">
                        <div>
                            <div class="notes-label">Title</div>
                            <input class="notes-input" type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Call client / Update tracker" maxlength="140" required>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div class="notes-label">Details</div>
                        <textarea class="notes-textarea" name="body" placeholder="Write your note here...">{{ old('body') }}</textarea>
                    </div>

                    <div class="notes-actions">
                        <button class="notes-btn notes-btn--primary" type="submit">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            Save
                        </button>
                        <a href="{{ route('notes.index') }}" class="notes-btn notes-btn--ghost" style="padding: 12px 14px;">
                            Clear
                        </a>
                    </div>
                </form>

                <script>
                    (function(){
                        const kind = document.getElementById('notesKind');
                        const due = document.getElementById('notesDueDate');
                        const rem = document.getElementById('notesReminderDate');
                        function sync(){
                            if(kind.value === 'task'){
                                due.style.display = 'block';
                                rem.style.display = 'none';
                                rem.disabled = true;
                                due.disabled = false;
                            }else if(kind.value === 'reminder'){
                                due.style.display = 'none';
                                rem.style.display = 'block';
                                due.disabled = true;
                                rem.disabled = false;
                            }else{
                                due.style.display = 'none';
                                rem.style.display = 'none';
                                due.disabled = true;
                                rem.disabled = true;
                            }
                        }
                        kind.addEventListener('change', sync);
                        sync();
                    })();
                </script>
            </div>

            <div class="notes-card notes-enter notes-enter-3">
                <div class="notes-card__head">
                    <div class="notes-card__title">Your Items</div>
                </div>

                <div class="notes-tabs">
                    @php $k = $activeKind ?? 'all'; @endphp
                    <a class="notes-tab {{ $k === 'all' ? 'is-active' : '' }}" href="{{ route('notes.index', ['kind' => 'all', 'q' => request('q')]) }}">All</a>
                    <a class="notes-tab {{ $k === 'note' ? 'is-active' : '' }}" href="{{ route('notes.index', ['kind' => 'note', 'q' => request('q')]) }}">Notes</a>
                    <a class="notes-tab {{ $k === 'task' ? 'is-active' : '' }}" href="{{ route('notes.index', ['kind' => 'task', 'q' => request('q')]) }}">Tasks</a>
                    <a class="notes-tab {{ $k === 'reminder' ? 'is-active' : '' }}" href="{{ route('notes.index', ['kind' => 'reminder', 'q' => request('q')]) }}">Reminders</a>
                </div>

                <form class="notes-search" method="GET" action="{{ route('notes.index') }}">
                    <input type="hidden" name="kind" value="{{ $activeKind ?? 'all' }}">
                    <input class="notes-input" type="search" name="q" value="{{ $q ?? '' }}" placeholder="Search notes, tasks...">
                    <button class="notes-btn notes-btn--ghost" type="submit" style="padding: 11px 14px;">
                        Search
                    </button>
                </form>

                @if(($todayReminders->isNotEmpty() ?? false) && ($activeKind ?? 'all') !== 'note')
                    <div style="margin-bottom: 12px;">
                        <div class="notes-label" style="margin-bottom: 8px;">Daily reminders for today</div>
                        <div class="notes-list">
                            @foreach($todayReminders as $item)
                                @include('notes._item', ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="notes-list">
                    @forelse($items as $item)
                        @if(($todayReminders->pluck('id')->contains($item->id) ?? false))
                            @continue
                        @endif
                        @include('notes._item', ['item' => $item])
                    @empty
                        <div class="notes-alert" style="background:#f9fafb;border-color:#f3f4f6;color:#6b7280;">
                            No items yet. Add one using Quick Add.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

