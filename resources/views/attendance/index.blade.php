@extends('layouts.app')

@section('title', 'Mark Attendance')
@section('page_heading', 'Attendance')

@section('content')
<style>
    .att-page {
        --att-primary: #0a2d29;
        --att-primary-mid: #0f3d37;
        --att-accent: #f1cd86;
        --att-accent-dim: #c9a85c;
        --att-surface: #ffffff;
        --att-border: #e5e7eb;
        --att-muted: #6b7280;
        --att-success: #059669;
        --att-warn: #d97706;
        --att-danger: #dc2626;
        max-width: 1080px;
        margin: 0 auto;
        padding-bottom: 48px;
    }

    @keyframes attFadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes attPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(241, 205, 134, 0.45); }
        50% { box-shadow: 0 0 0 14px rgba(241, 205, 134, 0); }
    }
    @keyframes attSpin {
        to { transform: rotate(360deg); }
    }

    .att-enter { animation: attFadeUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) backwards; }
    .att-enter-1 { animation-delay: 0.04s; }
    .att-enter-2 { animation-delay: 0.1s; }
    .att-enter-3 { animation-delay: 0.16s; }
    .att-enter-4 { animation-delay: 0.22s; }

    .att-hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }
    .att-hero__eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--att-accent-dim);
        margin-bottom: 6px;
    }
    .att-hero__title {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 800;
        color: var(--att-primary);
        letter-spacing: -0.02em;
    }
    .att-hero__sub {
        margin-top: 8px;
        font-size: 14px;
        color: var(--att-muted);
        max-width: 520px;
    }
    .att-policy {
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 12px;
        color: var(--att-muted);
        line-height: 1.5;
        box-shadow: 0 4px 18px rgba(10, 45, 41, 0.06);
    }
    .att-policy strong { color: var(--att-primary); }

    .att-grid {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 18px;
        margin-bottom: 18px;
    }
    @media (max-width: 900px) {
        .att-grid { grid-template-columns: 1fr; }
    }

    .att-punch-card {
        background: linear-gradient(145deg, var(--att-primary) 0%, var(--att-primary-mid) 100%);
        border-radius: 18px;
        padding: 28px 26px;
        color: #fff;
        box-shadow: 0 12px 40px rgba(10, 45, 41, 0.28);
        position: relative;
        overflow: hidden;
        transition: transform 0.35s ease, box-shadow 0.35s ease;
    }
    .att-punch-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 85% 15%, rgba(241, 205, 134, 0.18), transparent 45%);
        pointer-events: none;
    }
    .att-punch-card.is-complete {
        box-shadow: 0 12px 40px rgba(5, 150, 105, 0.22);
    }
    .att-punch-card__date {
        font-size: 13px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.75);
        letter-spacing: 0.02em;
    }
    .att-punch-card__clock {
        font-size: clamp(2.4rem, 6vw, 3.2rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        margin: 10px 0 6px;
        font-variant-numeric: tabular-nums;
        color: var(--att-accent);
        transition: color 0.3s ease;
    }
    .att-punch-card__tz {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: rgba(255, 255, 255, 0.55);
        margin-bottom: 22px;
    }

    .att-status-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 22px;
    }
    .att-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.14);
        transition: background 0.3s ease, border-color 0.3s ease, transform 0.3s ease;
    }
    .att-chip--present { background: rgba(5, 150, 105, 0.22); border-color: rgba(52, 211, 153, 0.35); }
    .att-chip--late { background: rgba(217, 119, 6, 0.22); border-color: rgba(251, 191, 36, 0.35); }
    .att-chip--half { background: rgba(220, 38, 38, 0.2); border-color: rgba(248, 113, 113, 0.35); }
    .att-chip--idle { opacity: 0.7; }

    .att-times {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 22px;
    }
    @media (max-width: 520px) {
        .att-times { grid-template-columns: 1fr; }
    }
    .att-time-box {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 12px 14px;
        transition: background 0.3s ease, transform 0.3s ease;
    }
    .att-time-box.is-active {
        background: rgba(241, 205, 134, 0.14);
        border-color: rgba(241, 205, 134, 0.35);
        transform: translateY(-2px);
    }
    .att-time-box__label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: rgba(255, 255, 255, 0.55);
        margin-bottom: 4px;
    }
    .att-time-box__value {
        font-size: 18px;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        color: #fff;
    }

    .att-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .att-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 13px 22px;
        border: none;
        border-radius: 12px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.25s ease, box-shadow 0.25s ease, opacity 0.25s ease, background 0.25s ease;
    }
    .att-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        transform: none !important;
    }
    .att-btn svg { width: 18px; height: 18px; }
    .att-btn--in {
        background: var(--att-accent);
        color: var(--att-primary);
        box-shadow: 0 6px 18px rgba(241, 205, 134, 0.35);
    }
    .att-btn--in:not(:disabled):hover { transform: translateY(-2px); }
    .att-btn--in.att-btn--pulse:not(:disabled) { animation: attPulse 2s ease infinite; }
    .att-btn--out {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.22);
    }
    .att-btn--out:not(:disabled):hover {
        background: rgba(255, 255, 255, 0.18);
        transform: translateY(-2px);
    }
    .att-btn.is-loading svg.att-btn__spin {
        animation: attSpin 0.8s linear infinite;
    }

    .att-side-panel {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .att-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .att-stat {
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 4px 18px rgba(10, 45, 41, 0.06);
        transition: transform 0.25s ease, border-color 0.25s ease;
    }
    .att-stat:hover {
        transform: translateY(-2px);
        border-color: rgba(241, 205, 134, 0.45);
    }
    .att-stat__num {
        font-size: 26px;
        font-weight: 800;
        color: var(--att-primary);
        line-height: 1;
    }
    .att-stat__lbl {
        font-size: 12px;
        color: var(--att-muted);
        margin-top: 6px;
        font-weight: 500;
    }

    .att-tip {
        background: #f8faf9;
        border: 1px solid var(--att-border);
        border-radius: 14px;
        padding: 16px 18px;
        font-size: 13px;
        color: var(--att-muted);
        line-height: 1.55;
    }
    .att-tip h3 {
        font-size: 13px;
        font-weight: 700;
        color: var(--att-primary);
        margin: 0 0 8px;
    }

    .att-history {
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 22px rgba(10, 45, 41, 0.06);
    }
    .att-history__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--att-border);
    }
    .att-history__title {
        font-size: 15px;
        font-weight: 700;
        color: var(--att-primary);
    }
    .att-history__sub {
        font-size: 12px;
        color: var(--att-muted);
    }
    .att-table-wrap { overflow-x: auto; }
    .att-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .att-table th {
        text-align: center;
        padding: 12px 16px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--att-muted);
        background: #fafbfc;
        border-bottom: 1px solid var(--att-border);
    }
    .att-table td {
        text-align: center;
        padding: 13px 16px;
        border-bottom: 1px solid #f0f1f3;
        color: #374151;
        font-variant-numeric: tabular-nums;
    }

    .att-remark-details {
        display: inline-block;
        text-align: left;
    }
    .att-remark-details > summary {
        cursor: pointer;
        list-style: none;
        user-select: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 12px;
        border: 1px solid rgba(241, 205, 134, 0.35);
        background: rgba(241, 205, 134, 0.10);
        color: var(--att-primary);
        font-weight: 800;
        transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease;
    }
    .att-remark-details > summary::-webkit-details-marker { display: none; }
    .att-remark-details > summary:hover {
        transform: translateY(-1px);
        background: rgba(241, 205, 134, 0.16);
        border-color: rgba(241, 205, 134, 0.6);
    }
    .att-remark-form {
        margin-top: 10px;
        background: #fff;
        border: 1px solid rgba(229, 231, 235, 0.9);
        border-radius: 14px;
        padding: 12px;
        box-shadow: 0 10px 30px rgba(10, 45, 41, 0.06);
        min-width: 280px;
    }
    .att-remark-textarea {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 10px 12px;
        font-size: 13px;
        outline: none;
        resize: vertical;
        min-height: 92px;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .att-remark-textarea:focus {
        border-color: rgba(241, 205, 134, 0.85);
        box-shadow: 0 0 0 3px rgba(241, 205, 134, 0.25);
    }
    .att-remark-save {
        margin-top: 10px;
        width: 100%;
    }
    .att-table tr:last-child td { border-bottom: none; }
    .att-table tr { transition: background 0.2s ease; }
    .att-table tbody tr:hover { background: #f9fafb; }

    .att-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }
    .att-badge--present { background: #d1fae5; color: #065f46; }
    .att-badge--late { background: #fef3c7; color: #92400e; }
    .att-badge--half { background: #fee2e2; color: #991b1b; }
    .att-badge--open { background: #e0f2fe; color: #075985; }

    .att-empty {
        padding: 36px 20px;
        text-align: center;
        color: var(--att-muted);
        font-size: 14px;
    }

    .att-alert {
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 500;
        animation: attFadeUp 0.4s ease;
    }
    .att-alert--success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }
    .att-alert--error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .att-alert--warn {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
    }

    .att-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        min-width: 260px;
        max-width: 360px;
        padding: 14px 16px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        color: #fff;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
        transform: translateY(20px);
        opacity: 0;
        pointer-events: none;
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.35s ease;
    }
    .att-toast.is-visible {
        transform: translateY(0);
        opacity: 1;
    }
    .att-toast--success { background: linear-gradient(135deg, #059669, #047857); }
    .att-toast--error { background: linear-gradient(135deg, #dc2626, #b91c1c); }
</style>

<div class="att-page">
    <header class="att-hero att-enter att-enter-1">
        <div>
            <div class="att-hero__eyebrow">RADiiX INFINITEii</div>
            <h1 class="att-hero__title">Mark Attendance</h1>
            <p class="att-hero__sub">
                @if($staff)
                    Welcome, {{ $staff->username }}. Check in when you start and check out when you finish for the day.
                @else
                    Your login is not linked to a staff profile. Please contact an administrator.
                @endif
            </p>
        </div>
        @if($policy)
        <div class="att-policy att-enter att-enter-2">
            <strong>Office policy</strong><br>
            Start: {{ $policy['office_start'] }} · Late after: {{ $policy['late_after'] }} ·
            Standard day: {{ $policy['standard_hours'] }}h · {{ $policy['timezone_label'] ?? 'EST' }}
        </div>
        @endif
    </header>

    @if(session('success'))
        <div class="att-alert att-alert--success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="att-alert att-alert--error">{{ session('error') }}</div>
    @endif

    @if(!$staff)
        <div class="att-alert att-alert--warn att-enter att-enter-3">
            Attendance cannot be recorded until your user account is linked to a staff profile.
        </div>
    @else
        <div class="att-grid att-enter att-enter-2">
            <section class="att-punch-card" id="attPunchCard" data-complete="{{ ($today['is_complete'] ?? false) ? '1' : '0' }}">
                <div class="att-punch-card__date" id="attDateLabel">{{ $today['date_label'] ?? '—' }}</div>
                <div class="att-punch-card__clock" id="attLiveClock">{{ $today['current_time'] ?? '--:--' }}</div>
                <div class="att-punch-card__tz">{{ $policy['timezone_label'] ?? 'EST' }} · Eastern Time</div>

                <div class="att-status-row" id="attStatusRow">
                    @php
                        $status = $today['status'] ?? null;
                        $chipClass = match($status) {
                            'late' => 'att-chip--late',
                            'half_day' => 'att-chip--half',
                            'present' => 'att-chip--present',
                            default => 'att-chip--idle',
                        };
                    @endphp
                    <span class="att-chip {{ $chipClass }}" id="attStatusChip">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span id="attStatusText">{{ $today['status_label'] ?? 'Not checked in' }}</span>
                    </span>
                </div>

                <div class="att-times">
                    <div class="att-time-box {{ ($today['check_in_at'] ?? null) ? 'is-active' : '' }}" id="attCheckInBox">
                        <div class="att-time-box__label">Check In</div>
                        <div class="att-time-box__value" id="attCheckInTime">{{ $today['check_in_at'] ?? '—' }}</div>
                    </div>
                    <div class="att-time-box {{ ($today['can_check_out'] ?? false) ? 'is-active' : '' }}" id="attWorkedBox">
                        <div class="att-time-box__label">Working</div>
                        <div class="att-time-box__value" id="attWorkedTime">{{ $today['worked_label'] ?? '—' }}</div>
                    </div>
                    <div class="att-time-box {{ ($today['check_out_at'] ?? null) ? 'is-active' : '' }}" id="attCheckOutBox">
                        <div class="att-time-box__label">Check Out</div>
                        <div class="att-time-box__value" id="attCheckOutTime">{{ $today['check_out_at'] ?? '—' }}</div>
                    </div>
                </div>

                <div class="att-actions">
                    <button type="button"
                            class="att-btn att-btn--in {{ ($today['can_check_in'] ?? false) ? 'att-btn--pulse' : '' }}"
                            id="attCheckInBtn"
                            data-url="{{ route('attendance.check-in') }}"
                            @disabled(!($today['can_check_in'] ?? false))>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        Check In
                    </button>
                    <button type="button"
                            class="att-btn att-btn--out"
                            id="attCheckOutBtn"
                            data-url="{{ route('attendance.check-out') }}"
                            @disabled(!($today['can_check_out'] ?? false))>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Check Out
                    </button>
                </div>
            </section>

            <aside class="att-side-panel att-enter att-enter-3">
                <div class="att-stats" id="attStats">
                    <div class="att-stat">
                        <div class="att-stat__num" data-stat="days_marked">{{ $stats['days_marked'] ?? 0 }}</div>
                        <div class="att-stat__lbl">Days This Month</div>
                    </div>
                    <div class="att-stat">
                        <div class="att-stat__num" data-stat="present">{{ $stats['present'] ?? 0 }}</div>
                        <div class="att-stat__lbl">On Time</div>
                    </div>
                    <div class="att-stat">
                        <div class="att-stat__num" data-stat="late">{{ $stats['late'] ?? 0 }}</div>
                        <div class="att-stat__lbl">Late Arrivals</div>
                    </div>
                    <div class="att-stat">
                        <div class="att-stat__num" data-stat="avg_hours">{{ $stats['avg_hours'] !== null ? $stats['avg_hours'].'h' : '—' }}</div>
                        <div class="att-stat__lbl">Avg Hours / Day</div>
                    </div>
                </div>

                <div class="att-tip">
                    <h3>How it works</h3>
                    <ul style="margin:0;padding-left:18px;">
                        <li>Check in once when you begin your workday.</li>
                        <li>Check out when you finish — hours are calculated automatically.</li>
                        <li>Arrivals after {{ $policy['late_after'] ?? '—' }} are marked as late.</li>
                        <li>Less than half a standard day may be flagged as half day.</li>
                    </ul>
                </div>
            </aside>
        </div>

        <section class="att-history att-enter att-enter-4">
            <div class="att-history__head">
                <div>
                    <div class="att-history__title">Recent Attendance</div>
                    <div class="att-history__sub">Last 21 working days</div>
                </div>
            </div>
            <div class="att-table-wrap">
                @if($records->isEmpty())
                    <div class="att-empty">No attendance records yet. Check in to start your history.</div>
                @else
                    <table class="att-table" id="attHistoryTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Hours</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $record)
                                @php
                                    $badge = match($record->status) {
                                        'late' => 'att-badge--late',
                                        'half_day' => 'att-badge--half',
                                        default => 'att-badge--present',
                                    };
                                    if ($record->check_in_at && !$record->check_out_at) {
                                        $badge = 'att-badge--open';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $record->attendance_date->format('D, d M Y') }}</td>
                                    <td>{{ $record->check_in_at?->timezone($policy['timezone'])->format('h:i A') ?? '—' }}</td>
                                    <td>{{ $record->check_out_at?->timezone($policy['timezone'])->format('h:i A') ?? '—' }}</td>
                                    <td>{{ $record->formattedWorkedHours() ?? ($record->check_in_at && !$record->check_out_at ? 'In progress' : '—') }}</td>
                                    <td>
                                        <span class="att-badge {{ $badge }}">
                                            {{ $record->check_in_at && !$record->check_out_at ? 'In Progress' : $record->statusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <details class="att-remark-details">
                                            <summary>
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
                                                </svg>
                                                Remarks
                                            </summary>
                                            <form method="POST" action="{{ route('attendance.remarks.save', ['id' => $record->id]) }}" class="att-remark-form">
                                                @csrf
                                                <textarea name="day_remarks" class="att-remark-textarea" placeholder="Write remarks for this day...">{{ $record->day_remarks }}</textarea>
                                                <button type="submit" class="att-btn att-btn--in att-remark-save">
                                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M20 6 9 17l-5-5"/>
                                                    </svg>
                                                    Save
                                                </button>
                                            </form>
                                        </details>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>
    @endif
</div>

<div class="att-toast" id="attToast" role="status" aria-live="polite"></div>

@if($staff)
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const timezone = @json($policy['timezone'] ?? 'America/New_York');
    const toast = document.getElementById('attToast');
    const punchCard = document.getElementById('attPunchCard');
    const liveClock = document.getElementById('attLiveClock');
    const checkInBtn = document.getElementById('attCheckInBtn');
    const checkOutBtn = document.getElementById('attCheckOutBtn');
    const checkInTime = document.getElementById('attCheckInTime');
    const checkOutTime = document.getElementById('attCheckOutTime');
    const workedTime = document.getElementById('attWorkedTime');
    const statusText = document.getElementById('attStatusText');
    const statusChip = document.getElementById('attStatusChip');
    const checkInBox = document.getElementById('attCheckInBox');
    const checkOutBox = document.getElementById('attCheckOutBox');
    const workedBox = document.getElementById('attWorkedBox');

    let checkInIso = @json($today['check_in_iso'] ?? null);
    let canCheckOut = @json($today['can_check_out'] ?? false);
    let isComplete = @json($today['is_complete'] ?? false);

    const timeFmt = new Intl.DateTimeFormat('en-US', {
        timeZone: timezone,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
    });

    function showToast(message, type) {
        if (!toast) return;
        toast.textContent = message;
        toast.className = 'att-toast att-toast--' + type + ' is-visible';
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => toast.classList.remove('is-visible'), 3200);
    }

    function formatMinutes(mins) {
        const h = Math.floor(mins / 60);
        const m = mins % 60;
        return h + 'h ' + String(m).padStart(2, '0') + 'm';
    }

    function updateLiveClock() {
        if (!liveClock) return;
        liveClock.textContent = timeFmt.format(new Date());

        if (checkInIso && canCheckOut && !isComplete) {
            const start = new Date(checkInIso);
            const diff = Math.max(0, Math.floor((Date.now() - start.getTime()) / 60000));
            workedTime.textContent = formatMinutes(diff);
            workedBox?.classList.add('is-active');
        }
    }

    function statusChipClass(status) {
        if (status === 'late') return 'att-chip att-chip--late';
        if (status === 'half_day') return 'att-chip att-chip--half';
        if (status === 'present') return 'att-chip att-chip--present';
        return 'att-chip att-chip--idle';
    }

    function applyToday(data) {
        if (!data) return;
        checkInIso = data.check_in_iso || null;
        canCheckOut = !!data.can_check_out;
        isComplete = !!data.is_complete;

        checkInTime.textContent = data.check_in_at || '—';
        checkOutTime.textContent = data.check_out_at || '—';
        workedTime.textContent = data.worked_label || (canCheckOut ? '—' : '—');
        statusText.textContent = data.status_label || 'Not checked in';
        statusChip.className = statusChipClass(data.status);

        checkInBtn.disabled = !data.can_check_in;
        checkOutBtn.disabled = !data.can_check_out;

        checkInBtn.classList.toggle('att-btn--pulse', !!data.can_check_in);
        checkInBox.classList.toggle('is-active', !!data.check_in_at);
        checkOutBox.classList.toggle('is-active', !!data.check_out_at);
        workedBox.classList.toggle('is-active', !!data.can_check_out || !!data.worked_label);
        punchCard.classList.toggle('is-complete', !!data.is_complete);
        punchCard.dataset.complete = data.is_complete ? '1' : '0';
    }

    function applyStats(stats) {
        if (!stats) return;
        const map = {
            days_marked: stats.days_marked,
            present: stats.present,
            late: stats.late,
            avg_hours: stats.avg_hours !== null ? stats.avg_hours + 'h' : '—',
        };
        Object.entries(map).forEach(([key, val]) => {
            const el = document.querySelector('[data-stat="' + key + '"]');
            if (el) el.textContent = val;
        });
    }

    async function punch(btn) {
        if (!btn || btn.disabled || btn.classList.contains('is-loading')) return;

        const url = btn.dataset.url;
        const original = btn.innerHTML;
        btn.classList.add('is-loading');
        btn.disabled = true;
        btn.innerHTML = '<svg class="att-btn__spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4"/><path d="M12 18v4"/><path d="M4.93 4.93l2.83 2.83"/><path d="M16.24 16.24l2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/><path d="M4.93 19.07l2.83-2.83"/><path d="M16.24 7.76l2.83-2.83"/></svg> Processing...';

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Unable to record attendance.');
            }
            applyToday(data.today);
            applyStats(data.stats);
            showToast(data.message, 'success');
            if (data.today?.is_complete) {
                setTimeout(() => window.location.reload(), 900);
            }
        } catch (err) {
            showToast(err.message || 'Something went wrong.', 'error');
            btn.disabled = false;
        } finally {
            btn.classList.remove('is-loading');
            btn.innerHTML = original;
        }
    }

    checkInBtn?.addEventListener('click', () => punch(checkInBtn));
    checkOutBtn?.addEventListener('click', () => punch(checkOutBtn));

    updateLiveClock();
    setInterval(updateLiveClock, 1000);
})();
</script>
@endif
@endsection
