@extends('layouts.app')

@section('title', 'Job Details — #' . $trackerInfo->id)

@section('content')
@php
    $stageColors = [
        2  => '#0EA5E9', 3  => '#0EA5E9', 4  => '#0EA5E9', 5  => '#0EA5E9',
        6  => '#F59E0B', 7  => '#F59E0B', 8  => '#F59E0B',
        9  => '#8B5CF6', 10 => '#8B5CF6', 11 => '#8B5CF6',
        12 => '#6366F1',
        13 => '#10B981', 14 => '#10B981', 15 => '#10B981', 16 => '#10B981',
        17 => '#059669',
        18 => '#EF4444',
    ];
@endphp

<style>
    :root {
        --c-primary:     #0a2d29;
        --c-accent:      #f1cd86;
        --c-accent-d:    #c9a84c;
        --c-bg:          #f5f6f8;
        --c-surface:     #ffffff;
        --c-border:      #e5e7eb;
        --c-text:        #111827;
        --c-muted:       #6b7280;
        --c-danger:      #dc2626;
        --c-success:     #059669;
        --c-warn:        #d97706;
        --radius:        10px;
        --shadow-sm:     0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
        --shadow-md:     0 4px 12px rgba(0,0,0,.08), 0 2px 4px rgba(0,0,0,.04);
        --shadow-lg:     0 10px 30px rgba(0,0,0,.10), 0 4px 8px rgba(0,0,0,.06);
    }

    /* ── Layout ── */
    .info-page { max-width: 1200px; margin: 0 auto; padding: 0 0 40px; }

    /* ── Breadcrumb Header ── */
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 0 16px;
        border-bottom: 1px solid var(--c-border);
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .breadcrumb {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; color: var(--c-muted);
    }
    .breadcrumb a { color: var(--c-primary); text-decoration: none; font-weight: 500; }
    .breadcrumb a:hover { text-decoration: underline; }
    .breadcrumb-sep { color: var(--c-border); }
    .breadcrumb-current { color: var(--c-text); font-weight: 600; }

    .header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

    /* ── Buttons ── */
    .btn-i {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: 7px; font-size: 13px;
        font-weight: 600; cursor: pointer; border: none;
        text-decoration: none; transition: all .15s;
        white-space: nowrap;
    }
    .btn-i svg { width: 15px; height: 15px; flex-shrink: 0; }
    .btn-primary-i  { background: var(--c-primary); color: #fff; }
    .btn-primary-i:hover  { background: #0d3d38; box-shadow: var(--shadow-sm); }
    .btn-secondary-i { background: var(--c-surface); color: var(--c-text); border: 1px solid var(--c-border); }
    .btn-secondary-i:hover { background: #f9fafb; }
    .btn-accent-i   { background: var(--c-accent); color: var(--c-primary); }
    .btn-accent-i:hover   { background: var(--c-accent-d); }
    .btn-danger-i   { background: #FEF2F2; color: var(--c-danger); border: 1px solid #FECACA; }
    .btn-danger-i:hover   { background: #FEE2E2; }
    .btn-ghost-i    { background: transparent; color: var(--c-muted); border: 1px solid var(--c-border); }
    .btn-ghost-i:hover    { color: var(--c-text); background: #f3f4f6; }
    .btn-sm-i { padding: 5px 10px; font-size: 11.5px; }

    /* ── Card ── */
    .card {
        background: var(--c-surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--c-border);
        overflow: hidden;
        margin-bottom: 20px;
    }
    .card-header {
        padding: 14px 20px;
        border-bottom: 1px solid var(--c-border);
        display: flex; align-items: center; justify-content: space-between;
        background: linear-gradient(135deg, var(--c-primary) 0%, #0d3d38 100%);
        color: #fff;
        gap: 12px; flex-wrap: wrap;
    }
    .card-header h2 {
        font-size: 14px; font-weight: 700; margin: 0;
        display: flex; align-items: center; gap: 8px; color: #fff;
    }
    .card-header h2 svg { width: 16px; height: 16px; opacity: .85; }
    .card-body { padding: 20px; }

    /* ── Job Meta Grid ── */
    .meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 14px;
    }
    .meta-item { display: flex; flex-direction: column; gap: 3px; }
    .meta-label {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: var(--c-muted);
    }
    .meta-value { font-size: 13.5px; font-weight: 600; color: var(--c-text); }
    .meta-value.muted { font-weight: 400; color: var(--c-muted); }

    /* ── Status Pill ── */
    .pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 9px; border-radius: 20px;
        font-size: 11px; font-weight: 700; letter-spacing: .03em;
    }
    .pill-primary { background: #E6F0EF; color: var(--c-primary); }
    .pill-accent  { background: #FDF6E3; color: var(--c-accent-d); border: 1px solid var(--c-accent); }
    .pill-success { background: #ECFDF5; color: var(--c-success); }
    .pill-danger  { background: #FEF2F2; color: var(--c-danger); }
    .pill-muted   { background: #F3F4F6; color: var(--c-muted); }

    /* ── Stats Row ── */
    .stats-row {
        display: flex; gap: 12px; flex-wrap: wrap;
    }
    .stat-box {
        flex: 1; min-width: 100px;
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: 8px;
        padding: 12px 16px;
        display: flex; flex-direction: column; gap: 4px;
    }
    .stat-num { font-size: 22px; font-weight: 800; color: var(--c-primary); line-height: 1; }
    .stat-lbl { font-size: 11px; color: var(--c-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }

    /* ── Assign bar ── */
    .assign-bar {
        display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;
        padding: 16px 20px;
        background: #f9fafb;
        border-bottom: 1px solid var(--c-border);
    }
    .assign-bar .fg { flex: 0 0 300px; min-width: 200px; }
    .assign-bar label { font-size: 11.5px; font-weight: 700; color: var(--c-primary); display: block; margin-bottom: 5px; }
    .assign-bar select,
    .assign-bar input[type="text"] {
        width: 100%; padding: 7px 10px; border: 1px solid var(--c-border);
        border-radius: 7px; font-size: 13px; color: var(--c-text);
        background: #fff; outline: none;
        transition: border-color .15s;
    }
    .assign-bar select:focus,
    .assign-bar input:focus { border-color: var(--c-primary); box-shadow: 0 0 0 3px rgba(10,45,41,.08); }

    /* ── Candidates Table ── */
    .table-wrap { overflow-x: auto; }
    .ctable { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .ctable th {
        padding: 9px 12px; text-align: center; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--c-muted); background: #f9fafb;
        border-bottom: 1px solid var(--c-border);
        white-space: nowrap;
    }
    .ctable td {
        padding: 11px 12px; border-bottom: 1px solid #f3f4f6;
        color: var(--c-text); vertical-align: middle; text-align: center;
    }
    .ctable tbody tr:hover td { background: #f9fafb; }
    .ctable tbody tr:last-child td { border-bottom: none; }

    .candidate-name { font-weight: 700; color: var(--c-primary); font-size: 13px; }
    .candidate-email { color: var(--c-muted); font-size: 11.5px; margin-top: 1px; }
    .candidate-meta { color: var(--c-muted); font-size: 10.5px; margin-top: 3px; line-height: 1.35; }
    .candidate-summary { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .stage-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 8px; border-radius: 20px;
        font-size: 10.5px; font-weight: 700;
        background: var(--c-bg); color: var(--c-muted);
        border: 1px solid var(--c-border);
    }
    .stage-badge .dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

    /* ── Actions ── */
    .action-group { display: flex; gap: 5px; align-items: center; flex-wrap: wrap; justify-content: center; }
    .actions-wrap { justify-content: center !important; }

    /* ── Empty state ── */
    .empty-state-box {
        text-align: center; padding: 48px 20px;
        color: var(--c-muted);
    }
    .empty-state-box svg { width: 48px; height: 48px; opacity: .3; margin-bottom: 12px; }
    .empty-state-box p { font-size: 14px; margin: 0; }

    /* ── Approved rows (manual, checklist) — light yellow ── */
    .ctable tr.row-approved { background: #FEFCE8; }
    .ctable tr.row-approved:hover { background: #FEF9C3; }
    .ctable tr.row-approved td { border-bottom-color: #FDE68A; }
    .ctable tr.row-approved .candidate-name { color: #854D0E; }
    .candidate-approved-meta {
        font-size: 10px; color: #A16207; margin-top: 5px; font-weight: 600;
    }
    .stage-badge-approved {
        background: #FEF9C3; color: #854D0E; border-color: #FDE047;
    }

    /* ── Collapsible Placed Section — light green (pipeline completion) ── */
    .stage-pick-grid { display: grid; gap: 8px; }
    .stage-pick-option { position: relative; }
    .stage-pick-option input {
        position: absolute; opacity: 0; width: 0; height: 0;
    }
    .stage-pick-card {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 14px; border: 1.5px solid var(--c-border);
        border-radius: 10px; background: #fff; cursor: pointer;
        transition: border-color .18s ease, background .18s ease, box-shadow .18s ease;
    }
    .stage-pick-option input:checked + .stage-pick-card {
        border-color: #CA8A04; background: #FEFCE8;
        box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.15);
    }
    .stage-pick-card:hover { border-color: #EAB308; }
    .stage-pick-dot {
        width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
        background: #EAB308;
    }
    .stage-pick-label { font-size: 13px; font-weight: 700; color: var(--c-text); }
    .stage-pick-desc { font-size: 11px; color: var(--c-muted); margin-top: 2px; }

    /* ── Collapsible Placed Section — light green (pipeline completion) ── */
    .section-toggle-placed {
        display: flex; align-items: center; justify-content: space-between;
        cursor: pointer; user-select: none;
        padding: 14px 20px;
        background: #F0FDF4;
        border-bottom: 1px solid #BBF7D0;
        color: #065F46;
    }
    .section-toggle-placed:hover { background: #DCFCE7; }
    .section-toggle-placed .toggle-title {
        display: flex; align-items: center; gap: 8px;
        font-size: 14px; font-weight: 700;
    }
    .section-toggle-placed .toggle-title svg { width: 16px; height: 16px; }

    /* ── Rejected rows in main pipeline table ── */
    .ctable tr.row-rejected { background: #FEF2F2; }
    .ctable tr.row-rejected:hover { background: #FEE2E2; }
    .ctable tr.row-rejected td { border-bottom-color: #FECACA; }
    .ctable tr.row-rejected .candidate-name { color: #7F1D1D; }
    .candidate-rejection-reason {
        font-size: 11px; color: #B91C1C; margin-top: 5px;
        line-height: 1.4; font-style: italic;
    }
    .candidate-rejection-meta {
        font-size: 10px; color: #9CA3AF; margin-top: 2px;
    }

    /* ── Placed table (pipeline) ── */
    .ptable { width: 100%; border-collapse: collapse; font-size: 12px; }
    .ptable th {
        padding: 8px 12px; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .05em;
        color: #9CA3AF; background: #f6fef9;
        border-bottom: 1px solid #BBF7D0;
        white-space: nowrap; text-align: center;
    }
    .ptable td {
        padding: 10px 12px; border-bottom: 1px solid #ECFDF5;
        vertical-align: middle; text-align: center;
    }
    .ptable tbody tr:last-child td { border-bottom: none; }

    /* ── Inline actions popover (left of Actions button) ── */
    .actions-cell { position: relative; overflow: visible !important; }
    .actions-wrap { position: relative; display: flex; align-items: center; justify-content: center; gap: 0; }
    .actions-popover {
        position: absolute; right: calc(100% + 6px); top: 50%;
        transform: translateY(-50%) scale(0.96);
        transform-origin: right center;
        min-width: 148px;
        background: #fff;
        border: 1px solid var(--c-border);
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,.1);
        padding: 4px;
        opacity: 0; visibility: hidden; pointer-events: none;
        z-index: 50;
        transition: opacity 0.18s ease, transform 0.2s cubic-bezier(0.32,0.72,0,1), visibility 0.18s;
    }
    .actions-popover.is-open {
        opacity: 1; visibility: visible; pointer-events: auto;
        transform: translateY(-50%) scale(1);
    }
    .ap-item {
        display: block; width: 100%; padding: 8px 10px;
        border: none; background: transparent; border-radius: 6px;
        font-size: 12px; font-weight: 600; color: var(--c-text);
        text-align: left; cursor: pointer; text-decoration: none;
        transition: background 0.14s ease;
    }
    .ap-item:hover:not(.ap-item-disabled) { background: #f3f4f6; }
    .ap-item-gold { color: #A16207; }
    .ap-item-gold:hover { background: #FEF9C3 !important; }
    .ap-item-danger { color: #DC2626; }
    .ap-item-danger:hover { background: #FEF2F2 !important; }
    .ap-item-disabled {
        opacity: 0.4; cursor: not-allowed; font-size: 11px;
    }
    .ap-form { margin: 0; padding: 0; }

    .toggle-icon { transition: transform .3s; }
    .toggle-icon.open { transform: rotate(180deg); }

    /* ── Modal Backdrop ── */
    .modal-overlay {
        display: none; position: fixed; inset: 0; z-index: 1000;
        background: rgba(0,0,0,.45); backdrop-filter: blur(3px);
        align-items: center; justify-content: center;
    }
    .modal-overlay.active { display: flex; }

    /* ── Modal Box ── */
    .modal-box {
        background: var(--c-surface); border-radius: 12px;
        box-shadow: var(--shadow-lg); max-height: 90vh;
        overflow-y: auto; width: 100%; max-width: 720px;
        animation: slideUp .25s ease;
    }
    .modal-box.modal-sm { max-width: 460px; }
    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }
    .modal-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px; border-bottom: 1px solid var(--c-border);
        background: linear-gradient(135deg, var(--c-primary) 0%, #0d3d38 100%);
        border-radius: 12px 12px 0 0;
        position: sticky; top: 0; z-index: 1;
    }
    .modal-head h3 { font-size: 16px; font-weight: 700; color: #fff; margin: 0; }
    .modal-head h3 span { font-size: 13px; font-weight: 400; opacity: .75; margin-left: 6px; }
    .modal-close {
        background: rgba(255,255,255,.15); border: none; color: #fff;
        width: 28px; height: 28px; border-radius: 50%; cursor: pointer;
        font-size: 18px; line-height: 1; display: flex; align-items: center;
        justify-content: center; transition: background .15s;
    }
    .modal-close:hover { background: rgba(255,255,255,.3); }
    .modal-body { padding: 20px; }
    .modal-foot {
        padding: 14px 20px; border-top: 1px solid var(--c-border);
        display: flex; justify-content: flex-end; gap: 10px;
        background: #f9fafb; border-radius: 0 0 12px 12px;
        position: sticky; bottom: 0;
    }

    /* ── Pipeline stages ── */
    .pipeline-stage { display: none; }
    .pipeline-step-card {
        background: #fdf6e3; border: 1px solid var(--c-accent);
        border-radius: 8px; padding: 16px;
    }
    .pipeline-step-card .step-title {
        font-size: 14px; font-weight: 700; color: var(--c-primary);
        margin-bottom: 12px; padding-bottom: 8px;
        border-bottom: 2px solid var(--c-accent);
        display: flex; align-items: center; gap: 8px;
    }
    .pipeline-step-card .step-title .step-num {
        background: var(--c-primary); color: var(--c-accent);
        width: 22px; height: 22px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 800; flex-shrink: 0;
    }
    .form-field { margin-bottom: 14px; }
    .form-field label {
        display: block; font-size: 12px; font-weight: 700;
        color: var(--c-primary); margin-bottom: 5px;
    }
    .form-field select,
    .form-field input[type="date"],
    .form-field textarea {
        width: 100%; padding: 8px 10px; border: 1px solid var(--c-border);
        border-radius: 7px; font-size: 13px; color: var(--c-text);
        background: #fff; outline: none; transition: border-color .15s;
    }
    .form-field select:focus,
    .form-field input:focus,
    .form-field textarea:focus { border-color: var(--c-primary); box-shadow: 0 0 0 3px rgba(10,45,41,.08); }
    .form-field.checkbox-field label {
        display: flex; align-items: center; gap: 9px; cursor: pointer; font-size: 13px;
    }
    .form-field.checkbox-field input[type="checkbox"] { width: 16px; height: 16px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    .note-danger {
        font-size: 11px; color: var(--c-danger);
        background: #FEF2F2; border: 1px solid #FECACA;
        padding: 6px 10px; border-radius: 5px; margin-top: 8px;
    }

    /* ── Pipeline Summary Timeline ── */
    .timeline { padding-left: 24px; position: relative; }
    .timeline::before {
        content: ''; position: absolute; left: 7px; top: 0; bottom: 0;
        width: 2px; background: var(--c-border);
    }
    .tl-item { position: relative; margin-bottom: 16px; }
    .tl-item::before {
        content: ''; position: absolute; left: -24px; top: 4px;
        width: 14px; height: 14px; border-radius: 50%;
        background: #fff; border: 2.5px solid var(--c-border); z-index: 1;
    }
    .tl-item.done::before { background: var(--c-accent); border-color: var(--c-primary); }
    .tl-item.active::before { background: var(--c-primary); border-color: var(--c-primary); box-shadow: 0 0 0 4px rgba(10,45,41,.12); }
    .tl-content {
        background: #f9fafb; border: 1px solid var(--c-border);
        border-radius: 7px; padding: 10px 12px;
        border-left: 3px solid var(--c-border);
    }
    .tl-item.done .tl-content  { border-left-color: var(--c-accent); background: #fff; }
    .tl-item.active .tl-content { border-left-color: var(--c-primary); background: #fff; box-shadow: var(--shadow-sm); }
    .tl-title { font-size: 13px; font-weight: 700; color: var(--c-primary); display: flex; justify-content: space-between; align-items: center; }
    .tl-date  { font-size: 11px; color: var(--c-muted); font-weight: 400; }
    .tl-desc  { font-size: 12px; color: var(--c-muted); margin-top: 3px; }

    /* ── Reject modal ── */
    .reject-modal-box { max-width: 420px; }
    .reject-reason { width: 100%; padding: 8px 12px; border: 1px solid var(--c-border); border-radius: 7px; font-size: 13px; resize: vertical; min-height: 80px; }
    .reject-reason:focus { outline: none; border-color: var(--c-danger); box-shadow: 0 0 0 3px rgba(220,38,38,.08); }

    /* ── Revert modal ── */
    .revert-modal-box { max-width: 440px; }
    .revert-notice {
        background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 10px;
        padding: 14px 16px; margin-bottom: 14px;
    }
    .revert-notice strong {
        display: block; font-size: 12px; color: #065F46; margin-bottom: 8px;
    }
    .revert-notice ul {
        margin: 0; padding-left: 18px; font-size: 12px; color: #047857; line-height: 1.55;
    }
    .revert-notice li { margin-bottom: 4px; }
    .btn-revert {
        background: linear-gradient(135deg, #059669, #047857);
        color: #fff; border: none;
    }
    .btn-revert:hover { filter: brightness(1.05); }

    /* ── Toast notification (slides from right) ── */
    .toast-stack {
        position: fixed;
        top: 88px;
        right: 24px;
        z-index: 2000;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
        max-width: min(420px, calc(100vw - 32px));
    }
    .toast-notify {
        pointer-events: auto;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 14px 14px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.45;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.06);
        transform: translateX(calc(100% + 32px));
        opacity: 0;
        transition: transform 0.5s cubic-bezier(0.32, 0.72, 0, 1), opacity 0.4s ease;
    }
    .toast-notify.is-visible {
        transform: translateX(0);
        opacity: 1;
    }
    .toast-notify.is-leaving {
        transform: translateX(calc(100% + 32px));
        opacity: 0;
    }
    .toast-notify svg.toast-icon { width: 20px; height: 20px; flex-shrink: 0; margin-top: 1px; }
    .toast-notify__body { flex: 1; min-width: 0; }
    .toast-notify__close {
        flex-shrink: 0;
        width: 28px; height: 28px;
        border: none; background: transparent;
        border-radius: 8px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: inherit; opacity: 0.55;
        transition: opacity 0.15s ease, background 0.15s ease;
        margin: -4px -4px 0 0;
    }
    .toast-notify__close:hover { opacity: 1; background: rgba(0, 0, 0, 0.06); }
    .toast-notify__close svg { width: 16px; height: 16px; }
    .toast-success {
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
    }
    .toast-error {
        background: #FEF2F2;
        color: #991B1B;
        border: 1px solid #FECACA;
    }

    @media (max-width: 768px) {
        .meta-grid { grid-template-columns: 1fr 1fr; }
        .stats-row .stat-box { min-width: 80px; }
        .ctable .hide-mobile { display: none; }
        .assign-bar .fg { flex: 1 1 100%; }
        .form-row { grid-template-columns: 1fr; }
    }
</style>

@if(session('success') || session('error'))
<div class="toast-stack" id="toastStack" aria-live="polite">
    @if(session('success'))
        <div class="toast-notify toast-success" id="pageToast" role="status">
            <svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span class="toast-notify__body">{{ session('success') }}</span>
            <button type="button" class="toast-notify__close" onclick="dismissPageToast()" aria-label="Dismiss">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="toast-notify toast-error" id="pageToast" role="alert">
            <svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <span class="toast-notify__body">{{ session('error') }}</span>
            <button type="button" class="toast-notify__close" onclick="dismissPageToast()" aria-label="Dismiss">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
    @endif
</div>
@endif

<div class="info-page">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('tracker.index') }}">Tracker</a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">#{{ $trackerInfo->id }} — {{ $trackerInfo->position ?? 'Job Details' }}</span>
        </div>
        <div class="header-actions">
            <a href="{{ route('tracker.edit', $trackerInfo->id) }}" class="btn-i btn-secondary-i">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </a>
            <a href="{{ route('tracker.export', $trackerInfo->id) }}" class="btn-i btn-accent-i">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </a>
            <a href="{{ route('tracker.index') }}" class="btn-i btn-ghost-i">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                Back
            </a>
        </div>
    </div>

    {{-- Job Details Card --}}
    <div class="card">
        <div class="card-header">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                Job Requisition
                <span style="font-size:12px;font-weight:400;opacity:.75;margin-left:4px;">ID #{{ $trackerInfo->id }}</span>
            </h2>
        </div>
        <div class="card-body">
            <div class="meta-grid">
                <div class="meta-item">
                    <span class="meta-label">Position</span>
                    <span class="meta-value">{{ $trackerInfo->position ?? 'N/A' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Client</span>
                    <span class="meta-value">{{ $trackerInfo->client->client ?? 'N/A' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">{{ $trackerInfo->regions->count() > 1 ? 'Locations' : 'Location' }}</span>
                    <span class="meta-value">
                        @php
                            $regionLabel = fn ($region) => $region->city ? $region->city . ', ' . $region->region : $region->region;
                        @endphp
                        @if($trackerInfo->regions->isNotEmpty())
                            {{ $trackerInfo->regions->map($regionLabel)->implode(' | ') }}
                        @elseif($trackerInfo->region)
                            {{ $regionLabel($trackerInfo->region) }}
                        @else
                            {{ $trackerInfo->country ?? 'N/A' }}
                        @endif
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Bill Rate / Salary</span>
                    <span class="meta-value">{{ $trackerInfo->bill_rate_salary_range ?? 'N/A' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Date of Demand (PRD)</span>
                    <span class="meta-value">{{ $trackerInfo->prd ? $trackerInfo->prd->format('d M Y') : 'N/A' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Target Date</span>
                    <span class="meta-value {{ $trackerInfo->submission_deadline && $trackerInfo->submission_deadline->isPast() ? 'muted' : '' }}">
                        @if($trackerInfo->submission_deadline)
                            {{ $trackerInfo->submission_deadline->format('d M Y') }}
                        @else
                            N/A
                        @endif
                        @if($trackerInfo->submission_deadline && $trackerInfo->submission_deadline->isPast())
                            <span style="font-size:11px;color:var(--c-danger);font-weight:700;">(Overdue)</span>
                        @elseif($trackerInfo->submission_deadline && $trackerInfo->submission_deadline->diffInDays(now()) <= 7)
                            <span style="font-size:11px;color:var(--c-warn);font-weight:700;">(Due Soon)</span>
                        @endif
                    </span>
        </div>
                <div class="meta-item">
                    <span class="meta-label">Country Fulfillment</span>
                    <span class="meta-value">{{ $trackerInfo->cf ?? 'N/A' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Lead Recruiter</span>
                    <span class="meta-value">{{ $trackerInfo->leadRecruiter->username ?? 'N/A' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Source</span>
                    <span class="meta-value">{{ $trackerInfo->csi ?? 'N/A' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Month</span>
                    <span class="meta-value">{{ $trackerInfo->month->month ?? 'N/A' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Priority</span>
                    <span class="meta-value">{{ $trackerInfo->priority ?? 'N/A' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Status</span>
                    <span class="meta-value">{{ $trackerInfo->jobStatus->status ?? 'Demand Raised' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Type of Job</span>
                    <span class="meta-value">{{ $trackerInfo->type_of_job ? ucfirst($trackerInfo->type_of_job) : 'N/A' }}</span>
                </div>
            </div>
            @if($trackerInfo->notes)
                <div style="margin-top:16px;padding:12px 14px;background:#f9fafb;border:1px solid var(--c-border);border-radius:8px;">
                    <span class="meta-label" style="display:block;margin-bottom:6px;">Job Notes</span>
                    <p style="margin:0;font-size:13px;line-height:1.55;color:var(--c-text);white-space:pre-wrap;">{{ $trackerInfo->notes }}</p>
                </div>
            @endif
    </div>
</div>

    {{-- Pipeline Stats --}}
    <div class="stats-row" style="margin-bottom:20px;">
        <div class="stat-box">
            <span class="stat-num">{{ $pipelineCount }}</span>
            <span class="stat-lbl">In Pipeline</span>
        </div>
        <div class="stat-box">
            <span class="stat-num" style="color:#059669;">{{ $placedCandidates->count() }}</span>
            <span class="stat-lbl">Placed</span>
        </div>
        <div class="stat-box">
            <span class="stat-num" style="color:var(--c-danger);">{{ $rejectedCandidates->count() }}</span>
            <span class="stat-lbl">Rejected</span>
        </div>
    </div>

    {{-- Candidates Section --}}
    <div class="card">
        <div class="card-header">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Candidates Pipeline
            </h2>
        </div>

        {{-- Assign bar --}}
        <div class="assign-bar">
            <form method="POST" action="{{ route('tracker.candidates.assign', $trackerInfo->id) }}" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;width:100%;">
        @csrf
                <div class="fg">
                    <label for="candidate_id">Assign Existing Candidate</label>
            <select id="candidate_id" name="candidate_id" required>
                        <option value="">Select or search candidate…</option>
                @foreach($availableCandidates as $candidate)
                            @if(!$assignedCandidateIds->contains($candidate->id))
                        <option value="{{ $candidate->id }}">
                            {{ $candidate->full_name }} 
                                    @if($candidate->email) — {{ $candidate->email }} @endif
                                    @if($candidate->work_status) ({{ $candidate->work_status }}) @endif
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
                <div style="display:flex;gap:8px;align-items:center;padding-bottom:1px;">
                    <button type="submit" class="btn-i btn-primary-i">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Assign
                    </button>
                    <button type="button" class="btn-i btn-accent-i" onclick="openCreateCandidateModal()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                        New Candidate
                    </button>
        </div>
    </form>
        </div>

        {{-- Pipeline table: in-progress + rejected (inline) --}}
        @if($activeCandidates->count() > 0 || $approvedCandidates->count() > 0 || $rejectedCandidates->count() > 0)
            <div class="table-wrap">
                <table class="ctable">
                    <thead>
                        <tr>
                            <th style="width:28px;">#</th>
                            <th>Candidate</th>
                            <th class="hide-mobile">Phone</th>
                            <th class="hide-mobile">Location</th>
                            <th class="hide-mobile">Auth</th>
                            <th>Stage</th>
                            <th>Resume</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rowNum = 0; @endphp
                        @foreach($activeCandidates as $tc)
                            @php
                                $rowNum++;
                            @endphp
                            @include('tracker._info_candidate_row', [
                                'tc' => $tc,
                                'index' => $rowNum,
                                'isRejected' => false,
                                'status' => $tc->pipelineStatus,
                                'hasResume' => (bool) $tc->candidate->resume_file_url,
                                'checklistProgress' => $pipelineService->checklistProgress(
                                    $pipelineService->checklistItems($tc->pipelineStatus, (bool) $tc->candidate->resume_file_url)
                                ),
                            ])
                        @endforeach

                        @foreach($approvedCandidates as $tc)
                            @php $rowNum++; @endphp
                            @include('tracker._info_candidate_row', [
                                'tc' => $tc,
                                'index' => $rowNum,
                                'isRejected' => false,
                                'isApproved' => true,
                                'status' => $tc->pipelineStatus,
                                'hasResume' => (bool) $tc->candidate->resume_file_url,
                                'checklistProgress' => 100,
                            ])
                        @endforeach

                        @foreach($rejectedCandidates as $tc)
                            @php
                                $rowNum++;
                            @endphp
                            @include('tracker._info_candidate_row', [
                                'tc' => $tc,
                                'index' => $rowNum,
                                'isRejected' => true,
                                'status' => $tc->pipelineStatus,
                                'hasResume' => (bool) $tc->candidate->resume_file_url,
                                'checklistProgress' => $pipelineService->checklistProgress(
                                    $pipelineService->checklistItems($tc->pipelineStatus, (bool) $tc->candidate->resume_file_url)
                                ),
                            ])
                        @endforeach
                    </tbody>
                </table>
            </div>
                                        @else
            <div class="empty-state-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <p>No candidates assigned yet. Use the form above to assign or create a candidate.</p>
            </div>
                                        @endif
    </div>

    {{-- Placed Candidates (pipeline completion) — light green ── --}}
    <div class="card" style="border:1px solid #BBF7D0;overflow:visible;" id="placedCard">
        <div class="section-toggle-placed" id="placedToggle" onclick="togglePlaced()">
            <span class="toggle-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Placed Candidates
                <span style="background:#DCFCE7;color:#065F46;padding:2px 8px;border-radius:12px;font-size:11px;">
                    {{ $placedCandidates->count() }}
                </span>
            </span>
            <svg class="toggle-icon" id="placedChevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;"><polyline points="6 9 12 15 18 9"/></svg>
        </div>

        <div id="placedBody" style="display:none;">
            @if($placedCandidates->count() > 0)
                <div class="table-wrap">
                    <table class="ptable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Candidate</th>
                                <th>Placement Date</th>
                                <th>Project Start</th>
                                <th>Stage</th>
                                <th>Resume</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($placedCandidates as $i => $tc)
                                @php
                                    $pStatus = $tc->pipelineStatus;
                                    $placementDate = $pStatus?->placement_completion_date;
                                    $projectStart = $pStatus?->candidate_project_start_date;
                                @endphp
                                <tr data-tracker-candidate-id="{{ $tc->id }}"
                                    data-placed="1"
                                    data-rejected="0"
                                    data-candidate-name="{{ $tc->candidate->full_name }}"
                                    data-pay-rate="{{ $tc->candidate->pay_rate ?? '' }}"
                                    data-placement-pay-rate="{{ $tc->candidate->placement_pay_rate ?? '' }}"
                                    data-candidate-summary="{{ e($tc->candidate->summary ?? '') }}"
                                    data-recruiter-notes="{{ $pStatus ? e($pStatus->recruiter_notes ?? '') : '' }}"
                                    data-checklist-progress="100"
                                    data-stage-label="{{ $placementConfirmedLabel }}"
                                    data-current-status-id="{{ $tc->current_status_id }}"
                                    data-placement-date="{{ $placementDate ? $placementDate->format('Y-m-d') : '' }}"
                                    data-project-start="{{ $projectStart ? $projectStart->format('Y-m-d') : '' }}"
                                    data-final-status="Confirmed"
                                    data-has-resume="{{ $tc->candidate->resume_file_url ? '1' : '0' }}">
                                    <td style="color:var(--c-muted);font-size:11px;">{{ $i + 1 }}</td>
                                    <td>
                                        <div style="font-weight:700;font-size:13px;color:#065F46;">{{ $tc->candidate->full_name }}</div>
                                        <div class="candidate-email" style="font-size:11px;color:var(--c-muted);">{{ $tc->candidate->email }}</div>
                                        @if($tc->candidate->pay_rate)
                                            <div class="candidate-meta">Pay: {{ $tc->candidate->pay_rate }}</div>
                                    @endif
                                        @if($tc->candidate->placement_pay_rate)
                                            <div class="candidate-meta">Placement: {{ $tc->candidate->placement_pay_rate }}</div>
                                        @endif
                                        <div style="font-size:10px;color:#059669;margin-top:3px;font-weight:600;">Pipeline placement confirmed</div>
                                    </td>
                                    <td style="font-size:12px;color:var(--c-muted);">
                                        {{ $placementDate ? $placementDate->format('d M Y') : '—' }}
                                    </td>
                                    <td style="font-size:12px;color:var(--c-muted);">
                                        {{ $projectStart ? $projectStart->format('d M Y') : '—' }}
                                </td>
                                <td>
                                        <span class="stage-badge" style="background:#DCFCE7;color:#065F46;border-color:#BBF7D0;">
                                            <span class="dot" style="background:#059669;"></span>
                                            {{ $placementConfirmedLabel }}
                                    </span>
                                </td>
                                <td>
                                        @if($tc->candidate->resume_file_url)
                                            <a href="{{ $tc->candidate->resume_file_url }}" target="_blank" class="btn-i btn-sm-i btn-accent-i" style="text-decoration:none;">CV</a>
                                        @else
                                            <span style="color:var(--c-muted);font-size:11px;">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                                            <button type="button" class="btn-i btn-sm-i btn-secondary-i" onclick="openCandidateDrawer({{ $tc->id }})">View</button>
                                            <a href="{{ route('tracker.candidates.report.form', ['tracker_id' => $trackerInfo->id, 'tracker_candidate_id' => $tc->id]) }}" class="btn-i btn-sm-i btn-accent-i" style="text-decoration:none;">Report</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>
    @else
                <div class="empty-state-box" style="padding:32px;">
                    <p style="color:var(--c-muted);font-size:13px;">No placed candidates yet. Complete the pipeline final step — <strong>Placement Completion → Confirmed</strong> — with a placement date.</p>
                </div>
    @endif
</div>
        </div>

</div><!-- /info-page -->

@include('tracker._candidate_drawer')
@include('tracker._mail_composer')

{{-- =============== APPROVED STAGE MODAL =============== --}}
<div id="approvedStageModal" class="modal-overlay" onclick="closeOnBackdrop(event, 'approvedStageModal')">
    <div class="modal-box modal-sm" style="max-width:420px;">
        <div class="modal-head" style="background:linear-gradient(135deg,#92400E,#CA8A04);">
            <h3>Update Status <span id="approvedStageCandidateName"></span></h3>
            <button class="modal-close" onclick="closeModal('approvedStageModal')">×</button>
                    </div>
        <form id="approvedStageForm" method="POST">
            @csrf
            <div class="modal-body" style="padding:18px;">
                <p style="font-size:12px;color:var(--c-muted);margin:0 0 14px;line-height:1.5;">
                    Progress this approved candidate until placement is confirmed in the pipeline.
                </p>
                <div class="stage-pick-grid">
                    @php
                        $stageDescriptions = [
                            'on_hold' => 'Paused — not actively progressing.',
                            'in_progress' => 'Actively working toward client submission.',
                            'submitted_to_client' => 'Resume submitted to the client.',
                            'awaited_from_client' => 'Waiting on client feedback.',
                        ];
                    @endphp
                    @foreach($approvedStageLabels as $key => $label)
                        <label class="stage-pick-option">
                            <input type="radio" name="approved_stage" value="{{ $key }}" id="stage_{{ $key }}">
                            <div class="stage-pick-card">
                                <span class="stage-pick-dot"></span>
                                <div>
                                    <div class="stage-pick-label">{{ $label }}</div>
                                    <div class="stage-pick-desc">{{ $stageDescriptions[$key] ?? '' }}</div>
                </div>
                    </div>
                        </label>
                    @endforeach
                        </div>
                    </div>
            <div class="modal-foot">
                <button type="button" class="btn-i btn-ghost-i" onclick="closeModal('approvedStageModal')">Cancel</button>
                <button type="submit" class="btn-i btn-primary-i">Save Status</button>
                </div>
        </form>
                    </div>
                </div>

{{-- =============== REVERT MODAL =============== --}}
<div id="revertModal" class="modal-overlay" onclick="closeOnBackdrop(event, 'revertModal')">
    <div class="modal-box revert-modal-box">
        <div class="modal-head" style="background:linear-gradient(135deg,#065F46,#059669);">
            <h3>Revert Candidate <span id="revertCandidateName"></span></h3>
            <button class="modal-close" onclick="closeModal('revertModal')">×</button>
                    </div>
        <form id="revertForm" method="POST">
            @csrf
            <div class="modal-body">
                <p style="font-size:13px;color:var(--c-text);margin:0 0 14px;line-height:1.5;">
                    This will move the candidate back into the active pipeline and <strong>reset all progress</strong>.
                </p>
                <div class="revert-notice">
                    <strong>What gets cleared</strong>
                    <ul>
                        <li>Checklist — all steps unchecked</li>
                        <li>Pipeline — screening, interviews, offers, placement</li>
                        <li>Approval status and post-approval stage</li>
                        <li>Rejection record</li>
                    </ul>
                </div>
                <p style="font-size:11.5px;color:var(--c-muted);margin:0;line-height:1.5;">
                    The candidate assignment and uploaded resume are kept. Stage resets to <strong>Candidate Identified</strong>.
                </p>
                    </div>
            <div class="modal-foot" style="background:#f0fdf4;">
                <button type="button" class="btn-i btn-ghost-i" onclick="closeModal('revertModal')">Cancel</button>
                <button type="submit" class="btn-i btn-revert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                    Revert to Pipeline
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =============== REJECT MODAL =============== --}}
<div id="rejectModal" class="modal-overlay" onclick="closeOnBackdrop(event, 'rejectModal')">
    <div class="modal-box modal-sm">
        <div class="modal-head" style="background:linear-gradient(135deg,#7F1D1D,#991B1B);">
            <h3>Reject Candidate <span id="rejectCandidateName"></span></h3>
            <button class="modal-close" onclick="closeModal('rejectModal')">×</button>
        </div>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="modal-body">
                <p style="font-size:13px;color:var(--c-text);margin-bottom:14px;">
                    The candidate will remain in the pipeline table with a <strong>red highlight</strong>. The rejection reason is stored in the checklist drawer.
                </p>
                <div class="form-field">
                    <label for="rejection_reason_input">Reason for Rejection <span style="color:var(--c-muted);font-weight:400;">(optional)</span></label>
                    <textarea id="rejection_reason_input" name="rejection_reason" class="reject-reason" placeholder="e.g., Candidate salary expectations exceed budget, not a skill match..."></textarea>
            </div>
        </div>
            <div class="modal-foot" style="background:#fef9f9;">
                <button type="button" class="btn-i btn-ghost-i" onclick="closeModal('rejectModal')">Cancel</button>
                <button type="submit" class="btn-i" style="background:#DC2626;color:#fff;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    Confirm Rejection
                </button>
        </div>
        </form>
    </div>
</div>

{{-- =============== CREATE CANDIDATE MODAL =============== --}}
@include('tracker._modals')

<script>
// ─── Page toast (slide in/out from right) ─────────────────────────────────────
let pageToastTimer = null;

function dismissPageToast() {
    const toast = document.getElementById('pageToast');
    if (!toast || toast.classList.contains('is-leaving')) return;

    if (pageToastTimer) {
        clearTimeout(pageToastTimer);
        pageToastTimer = null;
    }

    toast.classList.remove('is-visible');
    toast.classList.add('is-leaving');

    setTimeout(() => {
        const stack = document.getElementById('toastStack');
        toast.remove();
        if (stack && !stack.children.length) stack.remove();
    }, 520);
}

document.addEventListener('DOMContentLoaded', function () {
    const toast = document.getElementById('pageToast');
    if (!toast) return;

    requestAnimationFrame(() => {
        requestAnimationFrame(() => toast.classList.add('is-visible'));
    });

    pageToastTimer = setTimeout(dismissPageToast, 5500);
});

// ─── Modal Helpers ────────────────────────────────────────────────────────────
function syncBodyScrollLock() {
    const drawerOpen = document.body.classList.contains('drawer-open');
    const modalOpen = document.querySelector('.modal-overlay.active');
    document.body.style.overflow = (drawerOpen || modalOpen) ? 'hidden' : '';
}
function openModal(id) {
    const el = document.getElementById(id);
    el.classList.add('active');
    syncBodyScrollLock();
}
function closeModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('active');
    syncBodyScrollLock();
}
function closeOnBackdrop(e, id) {
    if (e.target.id === id) closeModal(id);
}

// ─── Placed Section Toggle ────────────────────────────────────────────────────
function togglePlaced() {
    const body    = document.getElementById('placedBody');
    const chevron = document.getElementById('placedChevron');
    const isOpen  = body.style.display !== 'none';
    body.style.display    = isOpen ? 'none' : 'block';
    chevron.classList.toggle('open', !isOpen);
}

// ─── Approved stage modal ─────────────────────────────────────────────────────
function openApprovedStageModal(tcId, name, currentStage) {
    document.getElementById('approvedStageCandidateName').textContent = name;
    document.getElementById('approvedStageForm').action =
        '/tracker/info/' + {{ $trackerInfo->id }} + '/candidates/' + tcId + '/approved-stage';

    document.querySelectorAll('#approvedStageForm input[name="approved_stage"]').forEach(radio => {
        radio.checked = radio.value === currentStage;
    });

    openModal('approvedStageModal');
}

// ─── Inline actions popover ───────────────────────────────────────────────────
let openPopoverId = null;

function toggleActionsPopover(id, event) {
    event.stopPropagation();
    const el = document.getElementById(id);
    if (!el) return;
    if (openPopoverId === id) {
        closeAllPopovers();
        return;
    }
    closeAllPopovers();
    el.classList.add('is-open');
    openPopoverId = id;
}

function closeAllPopovers() {
    document.querySelectorAll('.actions-popover.is-open').forEach(p => p.classList.remove('is-open'));
    openPopoverId = null;
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.actions-wrap')) closeAllPopovers();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAllPopovers();
});

// ─── Revert Modal ─────────────────────────────────────────────────────────────
function openRevertModal(tcId, candidateName) {
    document.getElementById('revertCandidateName').textContent = '— ' + candidateName;
    document.getElementById('revertForm').action = '/tracker/info/{{ $trackerInfo->id }}/candidates/' + tcId + '/revert';
    openModal('revertModal');
}

// ─── Reject Modal ─────────────────────────────────────────────────────────────
function openRejectModal(tcId, candidateName) {
    document.getElementById('rejectCandidateName').textContent = '— ' + candidateName;
    document.getElementById('rejectForm').action = '/tracker/info/{{ $trackerInfo->id }}/candidates/' + tcId + '/reject';
    document.getElementById('rejection_reason_input').value = '';
    openModal('rejectModal');
}

// ─── Create Candidate Modal ───────────────────────────────────────────────────
function openCreateCandidateModal() { openModal('createCandidateModal'); }
function closeCreateCandidateModal() {
    closeModal('createCandidateModal');
    setTimeout(() => {
        const form = document.getElementById('createCandidateForm');
        if (form) form.reset();
        const locationHidden = document.getElementById('create_location_id');
        if (locationHidden) locationHidden.value = '';
        const locationSearch = document.getElementById('location_search');
        if (locationSearch) locationSearch.value = '';
        const dropzone = document.getElementById('createResumeDropzone');
        const fileNameEl = document.getElementById('createResumeFileName');
        if (dropzone) dropzone.classList.remove('has-file', 'is-dragover');
        if (fileNameEl) fileNameEl.textContent = '';
    }, 300);
}

// ─── Select2 init ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
        $('#candidate_id').select2({
            placeholder: 'Search for a candidate…',
            allowClear: true,
            width: '100%',
            dropdownParent: $('.assign-bar')
        });
    }

    // Location search in create modal
    const locationSearch = document.getElementById('location_search');
    const locationDrop   = document.getElementById('location_dropdown');
    const locationHidden = document.getElementById('create_location_id');

    if (locationSearch && locationDrop) {
        locationSearch.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            locationDrop.style.display = 'block';
            document.querySelectorAll('.location-option').forEach(opt => {
                opt.style.display = opt.textContent.toLowerCase().includes(q) ? 'block' : 'none';
            });
        });
        document.querySelectorAll('.location-option').forEach(opt => {
            opt.addEventListener('click', function() {
                locationSearch.value  = this.textContent.trim();
                locationHidden.value  = this.dataset.value;
                locationDrop.style.display = 'none';
            });
        });
        document.addEventListener('click', function(e) {
            const wrap = locationSearch.closest('.cc-loc-wrap');
            if (wrap && !wrap.contains(e.target)) {
                locationDrop.style.display = 'none';
            }
        });
    }
});
</script>
@endsection
