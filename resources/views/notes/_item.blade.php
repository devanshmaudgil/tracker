@php
    /** @var \App\Models\UserNote $item */
    $isDone = (bool) ($item->is_completed ?? false);
    $kind = $item->kind;
    $when = null;
    if ($kind === \App\Models\UserNote::KIND_TASK && $item->due_date) {
        $when = 'Due ' . \Illuminate\Support\Carbon::parse($item->due_date)->toDateString();
    } elseif ($kind === \App\Models\UserNote::KIND_REMINDER && $item->reminder_date) {
        $when = 'Reminder ' . \Illuminate\Support\Carbon::parse($item->reminder_date)->toDateString();
    }
@endphp

<div class="notes-item {{ $isDone ? 'is-done' : '' }}">
    <div>
        <div class="notes-item__meta">
            <span class="notes-kind">{{ $item->kindLabel() }}</span>
            <span class="notes-when">{{ $when ?? 'Created ' . $item->created_at->format('d M Y') }}</span>
            @if($isDone)
                <span class="notes-kind" style="background: rgba(5, 150, 105, 0.08); border-color: rgba(5, 150, 105, 0.22); color:#047857;">
                    Done
                </span>
            @endif
        </div>

        <div class="notes-title" style="{{ $isDone ? 'text-decoration: line-through;' : '' }}">
            {{ $item->title }}
        </div>
        @if($item->body)
            <div class="notes-body {{ $isDone ? '' : 'is-trim' }} {{ $isDone ? '' : '' }}">
                {{ $item->body }}
            </div>
        @endif
    </div>

    <div class="notes-item__right">
        @if(in_array($item->kind, [\App\Models\UserNote::KIND_TASK, \App\Models\UserNote::KIND_REMINDER], true))
            <form method="POST" action="{{ route('notes.toggle', ['id' => $item->id]) }}">
                @csrf
                <button class="notes-pill {{ $isDone ? 'is-done' : 'is-open' }}" type="submit">
                    {{ $isDone ? 'Re-open' : 'Mark done' }}
                </button>
            </form>
        @endif

        <div class="notes-item__btnrow">
            <a class="notes-link" href="{{ route('notes.edit', ['id' => $item->id]) }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 20h9" />
                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
                </svg>
                Edit
            </a>
            <form method="POST" action="{{ route('notes.destroy', ['id' => $item->id]) }}" onsubmit="return confirm('Delete this item?')">
                @csrf
                @method('DELETE')
                <button class="notes-link notes-link--danger" type="submit" style="background:transparent;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                        <path d="M10 11v6" />
                        <path d="M14 11v6" />
                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

