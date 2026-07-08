@extends('layouts.app')

@section('title', 'Edit Note')
@section('page_heading', 'Edit Item')

@section('content')
<style>
    .notes-page { max-width: 880px; margin: 0 auto; padding-bottom: 48px; position: relative; isolation: isolate; }

    @keyframes notesFadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .notes-enter { animation: notesFadeUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) backwards; }
    .notes-enter-1 { animation-delay: 0.04s; }

    .notes-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.96), #ffffff);
        border: 1px solid rgba(229, 231, 235, 0.9);
        border-radius: 14px;
        box-shadow:
            0 10px 40px rgba(10,45,41,0.05),
            0 0 0 1px rgba(241,205,134,0.08) inset;
        padding:16px;
        transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
    }
    .notes-card:hover {
        border-color: rgba(241, 205, 134, 0.65);
        box-shadow:
            0 14px 48px rgba(10,45,41,0.08),
            0 0 0 1px rgba(241,205,134,0.12) inset;
        transform: translateY(-1px);
    }
    .notes-card__head { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:12px; }
    .notes-card__head { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:14px; padding-bottom:10px; border-bottom: 1px solid rgba(241, 205, 134, 0.25); }
    .notes-card__title { font-weight:900; color: var(--teal-deep, #0a2d29); position: relative; }
    .notes-card__title::after {
        content:'';
        position:absolute;
        left:0;
        bottom:-13px;
        width:54px;
        height:3px;
        border-radius:999px;
        background: linear-gradient(90deg, rgba(241, 205, 134, 0.95), rgba(241, 205, 134, 0));
    }
    .notes-label { font-size:12px; font-weight:900; color:#374151; margin-bottom:6px; letter-spacing:0.02em; }
    .notes-input, .notes-textarea, .notes-select { width:100%; border:1px solid #e5e7eb; border-radius:12px; padding:11px 12px; font-size:14px; outline:none; background:#fff; transition:border-color .2s ease, box-shadow .2s ease; }
    .notes-textarea { min-height: 160px; resize: vertical; }
    .notes-input:focus, .notes-textarea:focus, .notes-select:focus { border-color: rgba(10,45,41,0.35); box-shadow: 0 0 0 3px rgba(241,205,134,0.25); }
    .notes-btn { display:inline-flex; align-items:center; justify-content:center; gap:10px; padding:12px 18px; border:none; border-radius:12px; cursor:pointer; font-weight:900; transition:transform .2s ease, box-shadow .2s ease, opacity .2s ease; text-decoration:none; font-family:inherit; }
    .notes-btn--primary { background: linear-gradient(135deg, var(--teal-deep, #0a2d29) 0%, var(--teal-matte, #0f3d37) 100%); color: var(--gold, #f1cd86); box-shadow: 0 6px 18px rgba(10,45,41,0.18); }
    .notes-btn--primary:hover { transform: translateY(-2px); }
    .notes-btn--ghost { background:transparent; border:1px solid #e5e7eb; color:#374151; }
    .notes-btn--ghost:hover { transform: translateY(-1px); border-color: rgba(241,205,134,0.6); }
    .notes-form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px; }
    @media (max-width: 520px){ .notes-form-row{ grid-template-columns:1fr; } }
    .notes-actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:14px; }
</style>

<div class="notes-page">
    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:14px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 12px; padding: 14px 16px; font-weight: 800;">
            Please fix the highlighted fields.
        </div>
    @endif

    <div class="notes-card notes-enter notes-enter-1">
        <div class="notes-card__head">
            <div class="notes-card__title">Edit {{ $note->kindLabel() }}</div>
            <a class="notes-btn notes-btn--ghost" href="{{ route('notes.index') }}" style="padding: 12px 14px;">Back</a>
        </div>

        <form method="POST" action="{{ route('notes.update', ['id' => $note->id]) }}">
            @csrf
            @method('PUT')

            <div class="notes-form-row">
                <div>
                    <div class="notes-label">Type</div>
                    <select class="notes-select" name="kind" id="notesKind">
                        <option value="note" @selected($note->kind==='note')>Note</option>
                        <option value="task" @selected($note->kind==='task')>Task</option>
                        <option value="reminder" @selected($note->kind==='reminder')>Daily Reminder</option>
                    </select>
                </div>
                <div>
                    <div class="notes-label">Date</div>
                    <input class="notes-input" type="date" name="due_date" id="notesDueDate" value="{{ $note->due_date }}">
                    <input class="notes-input" type="date" name="reminder_date" id="notesReminderDate" value="{{ $note->reminder_date }}" style="display:none;">
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <div class="notes-label">Title</div>
                <input class="notes-input" type="text" name="title" value="{{ old('title', $note->title) }}" maxlength="140" required>
            </div>

            <div style="margin-bottom:12px;">
                <div class="notes-label">Details</div>
                <textarea class="notes-textarea" name="body" placeholder="Write details...">{{ old('body', $note->body) }}</textarea>
            </div>

            <div class="notes-actions">
                <button type="submit" class="notes-btn notes-btn--primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    Save Changes
                </button>
                <button type="button" class="notes-btn notes-btn--ghost" style="padding: 12px 14px;" onclick="window.location='{{ route('notes.index') }}'">
                    Cancel
                </button>
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
</div>
@endsection

