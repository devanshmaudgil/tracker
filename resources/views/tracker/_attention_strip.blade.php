@php
    $overdue = $attentionSummary['overdue'] ?? 0;
    $dueSoon = $attentionSummary['due_soon'] ?? 0;
    $urgent = $attentionSummary['urgent'] ?? 0;
    $hasAlerts = ($overdue + $dueSoon + $urgent) > 0;
@endphp

<div class="attention-strip">
    <span class="attention-strip__title">Needs attention</span>
    @if($hasAlerts)
        <div class="attention-strip__items">
            <button type="button"
                class="attention-pill attention-pill--clickable {{ $overdue > 0 ? 'attention-pill--alert' : 'attention-pill--muted' }}"
                data-attention-type="overdue"
                title="View overdue demands">
                <strong>{{ $overdue }}</strong> overdue
            </button>
            <button type="button"
                class="attention-pill attention-pill--clickable {{ $dueSoon > 0 ? 'attention-pill--warn' : 'attention-pill--muted' }}"
                data-attention-type="due_soon"
                title="View demands due this week">
                <strong>{{ $dueSoon }}</strong> due this week
            </button>
            <button type="button"
                class="attention-pill attention-pill--clickable {{ $urgent > 0 ? 'attention-pill--urgent' : 'attention-pill--muted' }}"
                data-attention-type="urgent"
                title="View urgent demands">
                <strong>{{ $urgent }}</strong> urgent
            </button>
        </div>
    @else
        <span class="attention-strip__clear">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Nothing urgent right now
        </span>
    @endif
</div>
