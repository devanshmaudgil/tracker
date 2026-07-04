@extends('layouts.app')

@section('title', 'Recruiterment Workspace')
@section('page_heading', 'Recruiterment Workspace')

@section('content')
<div class="dashboard-page">
<div class="dash-enter dash-enter-1">
<div class="dashboard-toolbar">
    <div class="month-picker" id="monthPicker">
        <label class="month-picker-label">Select Month</label>
        <div class="month-picker-field">
            <svg class="month-picker-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <input type="text" id="monthSearchInput" class="month-picker-input" placeholder="Search month..." value="{{ $selectedMonth->month ?? '' }}" autocomplete="off">
            <input type="hidden" id="toolbarMonthId" value="{{ $selectedMonthId }}">
            <svg class="month-picker-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            <div class="month-picker-dropdown" id="monthPickerDropdown">
                @foreach($months as $month)
                    <button type="button" class="month-picker-option {{ $selectedMonthId == $month->id ? 'selected' : '' }}" data-id="{{ $month->id }}" data-label="{{ $month->month }}">{{ $month->month }}</button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="toolbar-divider"></div>

    <div class="total-requisition">
        <span class="total-requisition-label">Total Requisition</span>
        <span class="total-requisition-value" id="totalRequisition">{{ $totalRequisition }}</span>
    </div>

    <div class="toolbar-spacer"></div>

    <div class="toolbar-actions">
        <a href="{{ route('tracker.import') }}" class="toolbar-btn toolbar-btn--ghost" title="Import Data from Excel">
            <span class="toolbar-btn__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            </span>
            <span class="toolbar-btn__label">Import</span>
        </a>
        <button type="button" class="toolbar-btn toolbar-btn--ghost" onclick="openExportModal()" title="Export Filtered Data to Excel">
            <span class="toolbar-btn__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </span>
            <span class="toolbar-btn__label">Export</span>
        </button>
        <a href="{{ route('tracker.create') }}" class="toolbar-btn toolbar-btn--primary">
            <span class="toolbar-btn__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </span>
            <span class="toolbar-btn__label">Add Demand</span>
        </a>
    </div>
</div>
</div>

<style>
    /* Page entrance */
    .dashboard-page {
        opacity: 0;
        animation: dashPageIn 0.35s ease forwards;
    }

    .dash-enter {
        opacity: 0;
        transform: translateY(14px);
        animation: dashSlideUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    .dash-enter-1 {
        animation-delay: 0.12s;
        position: relative;
        z-index: 50;
        overflow: visible;
    }

    .dash-enter-2 {
        animation-delay: 0.28s;
        position: relative;
        z-index: 1;
    }

    @keyframes dashPageIn {
        to { opacity: 1; }
    }

    @keyframes dashSlideUp {
        to { opacity: 1; transform: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .dashboard-page,
        .dash-enter {
            animation: none;
            opacity: 1;
            transform: none;
        }
    }

    :root {
        --dash-teal: #0a2d29;
        --dash-gold: #f1cd86;
        --dash-gold-bright: #ffe4a8;
    }

    .header-actions,
    .toolbar-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .header-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        font-family: inherit;
    }

    .header-actions .btn svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    .toolbar-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px 8px 10px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        font-family: inherit;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
        white-space: nowrap;
    }

    .toolbar-btn__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        flex-shrink: 0;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .toolbar-btn__icon svg {
        width: 15px;
        height: 15px;
    }

    .toolbar-btn__label {
        line-height: 1;
        letter-spacing: 0.01em;
    }

    .toolbar-btn--ghost {
        background: linear-gradient(135deg, #f6faf9 0%, #eef4f3 100%);
        color: var(--dash-teal);
        box-shadow: inset 0 0 0 1px rgba(10, 45, 41, 0.08), 0 1px 2px rgba(10, 45, 41, 0.04);
    }

    .toolbar-btn--ghost .toolbar-btn__icon {
        background: rgba(10, 45, 41, 0.06);
        color: var(--dash-teal);
    }

    .toolbar-btn--ghost:hover {
        background: linear-gradient(135deg, #fff 0%, #f4f9f8 100%);
        box-shadow: inset 0 0 0 1px rgba(241, 205, 134, 0.45), 0 4px 12px rgba(10, 45, 41, 0.08);
        transform: translateY(-1px);
        color: var(--dash-teal);
    }

    .toolbar-btn--ghost:hover .toolbar-btn__icon {
        background: rgba(241, 205, 134, 0.22);
        transform: scale(1.05);
    }

    .toolbar-btn--primary {
        background: linear-gradient(135deg, var(--dash-teal) 0%, #0f3d38 100%);
        color: #fff;
        padding-right: 18px;
        box-shadow: 0 4px 14px rgba(10, 45, 41, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.12);
    }

    .toolbar-btn--primary .toolbar-btn__icon {
        background: linear-gradient(135deg, var(--dash-gold) 0%, #e8c078 100%);
        color: var(--dash-teal);
        box-shadow: 0 2px 6px rgba(241, 205, 134, 0.4);
    }

    .toolbar-btn--primary:hover {
        background: linear-gradient(135deg, #0f3d38 0%, var(--dash-teal) 100%);
        box-shadow: 0 6px 20px rgba(10, 45, 41, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
        color: #fff;
    }

    .toolbar-btn--primary:hover .toolbar-btn__icon {
        transform: scale(1.08) rotate(90deg);
    }

    .toolbar-btn:active {
        transform: translateY(0);
    }

    .dashboard-toolbar {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 16px 20px;
        margin-bottom: 16px;
        background: #fff;
        border: 1px solid rgba(10, 45, 41, 0.08);
        border-radius: 14px;
        box-shadow: 0 2px 16px rgba(10, 45, 41, 0.05);
        flex-wrap: wrap;
        position: relative;
        z-index: 100;
        overflow: visible;
    }

    .toolbar-spacer { flex: 1; min-width: 12px; }

    .month-picker {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 240px;
        position: relative;
        z-index: 1;
    }

    .month-picker.open {
        z-index: 2001;
    }

    .month-picker-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--dash-teal);
    }

    .month-picker-field {
        position: relative;
        display: flex;
        align-items: center;
        overflow: visible;
    }

    .month-picker-icon {
        position: absolute;
        left: 12px;
        width: 16px;
        height: 16px;
        color: var(--dash-teal);
        opacity: 0.55;
        pointer-events: none;
    }

    .month-picker-chevron {
        position: absolute;
        right: 12px;
        width: 16px;
        height: 16px;
        color: #8a9e9a;
        pointer-events: none;
        transition: transform 0.25s;
    }

    .month-picker.open .month-picker-chevron { transform: rotate(180deg); }

    .month-picker-input {
        width: 100%;
        min-width: 220px;
        padding: 10px 36px 10px 38px;
        border: 1.5px solid #dfe9e7;
        border-radius: 10px;
        font-size: 13px;
        font-family: inherit;
        color: var(--dash-teal);
        background: #fafcfb;
        transition: border-color 0.25s, box-shadow 0.25s;
    }

    .month-picker-input:focus {
        outline: none;
        border-color: var(--dash-gold);
        box-shadow: 0 0 0 3px rgba(241, 205, 134, 0.15);
        background: #fff;
    }

    .month-picker-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        max-height: 220px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #dfe9e7;
        border-radius: 10px;
        box-shadow: 0 8px 28px rgba(10, 45, 41, 0.12);
        z-index: 2000;
        display: none;
        padding: 6px;
    }

    .month-picker.open .month-picker-dropdown { display: block; }

    .month-picker-dropdown.is-portaled {
        position: fixed;
        z-index: 5000;
        display: block;
        right: auto;
    }

    .month-picker-option {
        display: block;
        width: 100%;
        text-align: left;
        padding: 9px 12px;
        border: none;
        background: transparent;
        border-radius: 8px;
        font-size: 13px;
        font-family: inherit;
        color: #3d4f4c;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }

    .month-picker-option:hover,
    .month-picker-option.selected {
        background: rgba(241, 205, 134, 0.15);
        color: var(--dash-teal);
        font-weight: 600;
    }

    .month-picker-option.hidden { display: none; }

    .toolbar-divider {
        width: 1px;
        height: 44px;
        background: linear-gradient(180deg, transparent, rgba(241, 205, 134, 0.6), transparent);
        flex-shrink: 0;
    }

    .total-requisition {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 140px;
    }

    .total-requisition-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #7a8e8a;
    }

    .total-requisition-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--dash-teal);
        line-height: 1;
    }

    .btn-outline {
        background: #fff;
        color: var(--dash-teal);
        border: 1.5px solid #d8e4e2;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.25s;
    }

    .btn-outline:hover {
        border-color: var(--dash-gold);
        background: rgba(241, 205, 134, 0.08);
        transform: translateY(-1px);
    }

    .header-actions .btn-primary {
        box-shadow: 0 4px 14px rgba(241, 205, 134, 0.35);
    }

    /* Dashboard card */
    .dashboard-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(10, 45, 41, 0.08);
        box-shadow: 0 4px 24px rgba(10, 45, 41, 0.06);
        overflow: hidden;
    }

    .table-container {
        overflow-x: auto;
        width: 100%;
        background: transparent;
        border-radius: 0;
        padding: 0;
        box-shadow: none;
    }

    .table-container table {
        font-size: 13px;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 900px;
    }

    .table-container thead {
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .table-container th {
        background: linear-gradient(180deg, var(--dash-teal) 0%, #0d3a33 100%);
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 14px 12px;
        border: none;
        white-space: nowrap;
    }

    .table-container th:first-child { border-radius: 0; padding-left: 20px; }
    .table-container th:last-child { padding-right: 20px; }

    .table-container th,
    .table-container td {
        text-align: center;
    }

    .table-container td {
        padding: 13px 12px;
        color: #3d4f4c;
        border-bottom: 1px solid #eef2f1;
        vertical-align: middle;
        transition: background 0.2s;
    }

    .table-container td:first-child { padding-left: 20px; }
    .table-container td:last-child { padding-right: 16px; }

    .table-container tbody tr {
        transition: background 0.2s, box-shadow 0.2s;
    }

    .table-container tbody tr:hover {
        background: linear-gradient(90deg, rgba(241, 205, 134, 0.06) 0%, rgba(241, 205, 134, 0.02) 100%);
    }

    .table-container tbody tr:hover td {
        border-bottom-color: rgba(241, 205, 134, 0.15);
    }

    .table-container tbody tr:last-child td {
        border-bottom: none;
    }

    .cell-id {
        font-weight: 700;
        color: var(--dash-teal);
        font-size: 12px;
        background: rgba(10, 45, 41, 0.06);
        padding: 3px 8px;
        border-radius: 6px;
    }

    .cell-client {
        font-weight: 600;
        color: var(--dash-teal);
    }

    .cell-position {
        font-weight: 500;
        color: #2a3a37;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        padding: 4px 10px;
        background: linear-gradient(135deg, rgba(241, 205, 134, 0.2) 0%, rgba(241, 205, 134, 0.35) 100%);
        color: var(--dash-teal);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        border-radius: 20px;
        border: 1px solid rgba(241, 205, 134, 0.4);
        cursor: help;
    }

    /* Icon action buttons */
    .action-buttons {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        justify-content: center;
    }

    .action-form {
        display: inline-flex;
        margin: 0;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1.5px solid transparent;
        background: #f4f7f6;
        color: #5a6e6a;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.25s cubic-bezier(0.34, 1.2, 0.64, 1);
        padding: 0;
    }

    .action-btn svg {
        width: 16px;
        height: 16px;
        pointer-events: none;
    }

    .action-view:hover {
        background: rgba(26, 92, 82, 0.12);
        border-color: rgba(26, 92, 82, 0.25);
        color: var(--dash-teal);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(10, 45, 41, 0.12);
    }

    .action-edit:hover {
        background: rgba(241, 205, 134, 0.2);
        border-color: rgba(241, 205, 134, 0.5);
        color: #8a6d1f;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(241, 205, 134, 0.2);
    }

    .action-delete:hover {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.3);
        color: #dc3545;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
    }

    .empty-state {
        text-align: center;
        padding: 48px 20px !important;
        color: #7a8e8a;
    }

    .empty-state svg {
        width: 48px;
        height: 48px;
        color: #c5d0ce;
        margin-bottom: 12px;
    }

    .empty-state p {
        margin: 0 0 8px;
        font-size: 15px;
    }

    .empty-link {
        color: var(--dash-teal);
        font-weight: 600;
        text-decoration: none;
        border-bottom: 2px solid var(--dash-gold);
        padding-bottom: 2px;
    }

    .empty-link:hover { color: var(--dash-gold-dim, #c9a85c); }

    .bottom-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        padding: 16px 20px;
        background: #fafcfb;
        border-top: 1px solid #eef2f1;
    }

    #countText {
        color: #6b7c7a;
        font-size: 13px;
        font-weight: 500;
    }
    
    .search-filter-bar {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .search-input-wrapper {
        position: relative;
        width: 100%;
        max-width: 250px;
    }
    
    .search-input-wrapper input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
    }
    
    .search-input-wrapper input:focus {
        outline: none;
        border-color: #f1cd86;
    }
    
    .filter-icon-btn {
        background: #0a2d29;
        color: white;
        border: none;
        padding: 6px 10px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
        transition: background 0.3s;
        white-space: nowrap;
    }
    
    .filter-icon-btn:hover {
        background: #0d3a33;
    }
    
    .filter-icon-btn svg {
        width: 14px;
        height: 14px;
    }
    
    .filter-dropdown {
        position: absolute;
        right: 0;
        top: 40px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        padding: 15px;
        width: 280px;
        z-index: 1000;
        display: none;
    }
    
    .filter-dropdown.active {
        display: block;
    }
    
    .filter-dropdown h3 {
        margin: 0 0 12px 0;
        color: #0a2d29;
        font-size: 14px;
        border-bottom: 2px solid #f1cd86;
        padding-bottom: 5px;
    }
    
    .filter-group {
        margin-bottom: 12px;
    }
    
    .filter-group label {
        display: block;
        margin-bottom: 4px;
        font-weight: 600;
        color: #0a2d29;
        font-size: 12px;
    }
    
    .filter-group select {
        width: 100%;
        padding: 6px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 12px;
    }
    
    .filter-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }
    
    .filter-actions button,
    .filter-actions a {
        flex: 1;
        text-align: center;
        padding: 6px;
        font-size: 12px;
    }

    /* Pagination Styling */
    .pagination-container {
        display: flex;
        justify-content: center;
        width: 100%;
    }
    
    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        gap: 5px;
        margin: 0;
    }
    
    .pagination li {
        display: inline-block;
    }
    
    .pagination li a,
    .pagination li span {
        padding: 8px 14px;
        border: 1.5px solid #e2ebe9;
        border-radius: 10px;
        color: var(--dash-teal);
        text-decoration: none;
        transition: all 0.25s;
        font-size: 13px;
        font-weight: 500;
        background: #fff;
    }

    .pagination li a:hover {
        background: rgba(241, 205, 134, 0.15);
        border-color: var(--dash-gold);
        color: var(--dash-teal);
    }

    .pagination li.active span {
        background: var(--dash-teal);
        border-color: var(--dash-teal);
        color: #fff;
    }
    
    .pagination li.disabled span {
        color: #999;
        cursor: not-allowed;
        background: #f9f9f9;
    }

    /* Tabs */
    .tabs-bar {
        background: var(--dash-teal);
        padding: 8px 10px;
        display: flex;
        gap: 4px;
        margin-bottom: 0;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .tabs-bar::-webkit-scrollbar { display: none; }

    .tab-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 9px 14px;
        color: rgba(255, 255, 255, 0.7);
        cursor: pointer;
        border-radius: 10px;
        transition: all 0.25s;
        white-space: nowrap;
        font-size: 12px;
        font-weight: 500;
        background: transparent;
        border: none;
        font-family: inherit;
    }

    .tab-item:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
    }

    .tab-item.active {
        background: var(--dash-gold);
        color: var(--dash-teal);
        font-weight: 700;
        box-shadow: 0 2px 12px rgba(241, 205, 134, 0.3);
    }

    .tab-icon { width: 15px; height: 15px; flex-shrink: 0; }

    .tab-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 10px;
        min-width: 20px;
        text-align: center;
    }

    .tab-item.active .tab-badge {
        background: var(--dash-teal);
        color: #fff;
    }

    .tab-item:not(.active) .tab-badge {
        background: rgba(241, 205, 134, 0.15);
        color: var(--dash-gold);
        border: 1px solid rgba(241, 205, 134, 0.25);
    }

    .header-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        background: #fafcfb;
        border-bottom: 1px solid #eef2f1;
        gap: 16px;
        flex-wrap: wrap;
    }

    .controls-left {
        display: flex;
        align-items: center;
        min-width: 0;
        flex: 1;
    }

    .attention-strip {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        min-width: 0;
    }

    .attention-strip__title {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #7a8e8a;
        white-space: nowrap;
    }

    .attention-strip__items {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .attention-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        color: #5a6e6a;
        background: #fff;
        border: 1px solid #e2ebe9;
        white-space: nowrap;
    }

    .attention-pill strong {
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .attention-pill--muted {
        opacity: 0.55;
    }

    .attention-pill--alert {
        color: #b42318;
        border-color: rgba(180, 35, 24, 0.2);
        background: rgba(180, 35, 24, 0.06);
    }

    .attention-pill--alert strong { color: #b42318; }

    .attention-pill--warn {
        color: #b54708;
        border-color: rgba(181, 71, 8, 0.2);
        background: rgba(181, 71, 8, 0.06);
    }

    .attention-pill--warn strong { color: #b54708; }

    .attention-pill--urgent {
        color: var(--dash-teal);
        border-color: rgba(241, 205, 134, 0.45);
        background: rgba(241, 205, 134, 0.12);
    }

    .attention-pill--urgent strong { color: var(--dash-teal); }

    .attention-strip__clear {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #5a8a72;
        white-space: nowrap;
    }

    .attention-strip__clear svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    .controls-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
        margin-left: auto;
    }

    .search-wrapper {
        position: relative;
        width: 260px;
    }

    .search-wrapper input {
        width: 100%;
        padding: 10px 40px 10px 14px;
        border: 1.5px solid #e2ebe9;
        border-radius: 10px;
        font-size: 13px;
        font-family: inherit;
        background: #fff;
        transition: border-color 0.25s, box-shadow 0.25s;
    }

    .search-wrapper input:focus {
        outline: none;
        border-color: var(--dash-gold);
        box-shadow: 0 0 0 3px rgba(241, 205, 134, 0.15);
    }

    .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9aada9;
        width: 17px;
        height: 17px;
        pointer-events: none;
    }

    .filter-btn {
        background: var(--dash-teal);
        color: #fff;
        border: none;
        padding: 10px 14px;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-family: inherit;
        transition: background 0.25s, transform 0.2s;
    }

    .filter-btn:hover {
        background: #0d3a33;
        transform: translateY(-1px);
    }

    /* Tab switch loading */
    .table-loading-wrap {
        position: relative;
    }

    .table-loading-overlay {
        position: absolute;
        inset: 0;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.72);
        backdrop-filter: blur(2px);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
        pointer-events: none;
    }

    .table-loading-overlay.active {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .table-loading-wrap.is-loading .table-container {
        opacity: 0.45;
        transition: opacity 0.25s ease;
    }

    .table-loader {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    .loader-spinner {
        width: 36px;
        height: 36px;
        border: 3px solid rgba(10, 45, 41, 0.12);
        border-top-color: var(--dash-gold);
        border-right-color: var(--dash-teal);
        border-radius: 50%;
        animation: tabLoaderSpin 0.75s linear infinite;
    }

    .table-loader span {
        font-size: 13px;
        font-weight: 600;
        color: var(--dash-teal);
        letter-spacing: 0.04em;
    }

    @keyframes tabLoaderSpin {
        to { transform: rotate(360deg); }
    }

    .tab-item.is-loading {
        pointer-events: none;
        opacity: 0.75;
    }

    @media (max-width: 768px) {
        .dashboard-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .toolbar-divider {
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(241, 205, 134, 0.6), transparent);
        }

        .toolbar-spacer { display: none; }

        .toolbar-actions { justify-content: flex-end; }

        .total-requisition {
            flex-direction: row;
            align-items: baseline;
            gap: 10px;
        }

        .total-requisition-value { font-size: 22px; }

        .header-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .controls-right {
            width: 100%;
            margin-left: 0;
            justify-content: flex-end;
        }

        .search-wrapper { width: 100%; flex: 1; }
    }

    @media (prefers-reduced-motion: reduce) {
        .loader-spinner { animation: none; border-top-color: var(--dash-teal); }
        .table-loading-overlay { transition: none; }
    }
</style>

<div class="dash-enter dash-enter-2">
<div class="dashboard-card">
<div class="header-controls">
    <div class="controls-left" id="attentionStripContainer">
        @include('tracker._attention_strip')
    </div>
    <div class="controls-right">
        <div class="search-wrapper">
            <input type="text" id="searchInput" placeholder="Search" value="{{ request('search') }}">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        
        <div style="position: relative;">
            <button type="button" class="filter-btn" id="filterToggle">
                <svg style="width: 16px; height: 16px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span id="filterBadge" style="background: #f1cd86; color: #0a2d29; border-radius: 50%; width: 18px; height: 18px; display: {{ (request('client_id') || request('lead_recruiter_id')) ? 'inline-flex' : 'none' }}; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; margin-left: 5px;">
                    {{ collect([request('client_id'), request('lead_recruiter_id')])->filter()->count() }}
                </span>
            </button>
            
            <div class="filter-dropdown" id="filterDropdown" style="right: 0; top: 45px;">
                <form id="filterForm">
                    <h3>Filter Results</h3>
                    <div class="filter-group">
                        <label for="client_id">Client</label>
                        <select name="client_id" id="client_id">
                            <option value="">All Clients</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->client }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="lead_recruiter_id">Lead Recruiter</label>
                        <select name="lead_recruiter_id" id="lead_recruiter_id">
                            <option value="">All Recruiters</option>
                            @foreach($leadRecruiters as $recruiter)
                                <option value="{{ $recruiter->id }}" {{ request('lead_recruiter_id') == $recruiter->id ? 'selected' : '' }}>{{ $recruiter->username }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Apply</button>
                        <button type="button" id="clearFilters" class="btn btn-secondary">Clear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Bar -->
<div class="tabs-bar" id="tabsContainer">
    <div class="tab-item {{ request('tab', 'demand_raised') == 'demand_raised' ? 'active' : '' }}" data-tab="demand_raised">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
        <span>Demand Raised</span>
        <span class="tab-badge">{{ $counts['demand_raised'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'identified' ? 'active' : '' }}" data-tab="identified">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
        <span>Identified</span>
        <span class="tab-badge">{{ $counts['identified'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'screening' ? 'active' : '' }}" data-tab="screening">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
        <span>Initial Screening</span>
        <span class="tab-badge">{{ $counts['screening'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'submission' ? 'active' : '' }}" data-tab="submission">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
        <span>Submission</span>
        <span class="tab-badge">{{ $counts['submission'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'interview' ? 'active' : '' }}" data-tab="interview">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        <span>Interview</span>
        <span class="tab-badge">{{ $counts['interview'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'decision' ? 'active' : '' }}" data-tab="decision">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
        <span>Decision</span>
        <span class="tab-badge">{{ $counts['decision'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'accepted' ? 'active' : '' }}" data-tab="accepted">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>Accepted</span>
        <span class="tab-badge">{{ $counts['accepted'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'rejected' ? 'active' : '' }}" data-tab="rejected">
        <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>Rejected</span>
        <span class="tab-badge">{{ $counts['rejected'] ?? 0 }}</span>
    </div>
</div>

<div class="table-loading-wrap" id="tableLoadingWrap">
    <div class="table-loading-overlay" id="tableLoadingOverlay">
        <div class="table-loader">
            <div class="loader-spinner"></div>
            <span>Loading records...</span>
        </div>
    </div>

<div class="table-container" id="tableContainer">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Month</th>
                <th>Receiving Date</th>
                <th>Target Date</th>
                <th>Client</th>
                <th>Location</th>
                <th>Position</th>
                <th>Recruiter</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="trackerTableBody">
            @include('tracker._table')
        </tbody>
    </table>
</div>

<!-- Pagination and Count -->
<div class="bottom-info">
    <div id="countText">
        @if($trackerInfos->total() > 0)
            Showing {{ $trackerInfos->firstItem() }} to {{ $trackerInfos->lastItem() }} of {{ $trackerInfos->total() }} entries
        @else
            No entries found
        @endif
    </div>
    
    <div id="paginationContainer">
        @if($trackerInfos->hasPages())
            <div class="pagination-container" style="margin-top: 0;">
                {{ $trackerInfos->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        @endif
    </div>
</div>
</div>
</div>
</div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="modal" style="display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
    <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 300px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="font-size: 18px; margin-bottom: 20px; color: #0a2d29; border-bottom: 2px solid #f1cd86; padding-bottom: 5px;">Export to Excel</h2>
        
        <form action="{{ route('tracker.export_all') }}" method="GET" id="exportForm">
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="export_month_id" style="display: block; margin-bottom: 8px; font-weight: 500;">Select Month</label>
                <select name="month_id" id="export_month_id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">-- Select Month --</option>
                    @foreach($months as $month)
                        <option value="{{ $month->id }}">{{ $month->month }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeExportModal()" style="padding: 8px 16px; font-size: 12px;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 12px;">Download</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('trackerTableBody');
        const paginationContainer = document.getElementById('paginationContainer');
        const countText = document.getElementById('countText');
        const filterToggle = document.getElementById('filterToggle');
        const filterDropdown = document.getElementById('filterDropdown');
        const filterForm = document.getElementById('filterForm');
        const clearFilters = document.getElementById('clearFilters');
        const filterBadge = document.getElementById('filterBadge');
        const tabs = document.querySelectorAll('.tab-item');
        const tableLoadingWrap = document.getElementById('tableLoadingWrap');
        const tableLoadingOverlay = document.getElementById('tableLoadingOverlay');
        const monthPicker = document.getElementById('monthPicker');
        const monthSearchInput = document.getElementById('monthSearchInput');
        const toolbarMonthId = document.getElementById('toolbarMonthId');
        const monthPickerDropdown = document.getElementById('monthPickerDropdown');
        const monthOptions = document.querySelectorAll('.month-picker-option');
        const totalRequisitionEl = document.getElementById('totalRequisition');
        const attentionStripContainer = document.getElementById('attentionStripContainer');
        const activeTabEl = document.querySelector('.tab-item.active');
        let currentTab = activeTabEl ? activeTabEl.dataset.tab : 'demand_raised';
        let debounceTimer;
        let loadingTab = null;

        function filterMonthOptions(query) {
            const q = query.trim().toLowerCase();
            monthOptions.forEach(opt => {
                const label = (opt.dataset.label || opt.textContent).toLowerCase();
                opt.classList.toggle('hidden', q !== '' && !label.includes(q));
            });
        }

        let monthDropdownAnchor = null;

        function ensureDefaultMonthSelected() {
            if (toolbarMonthId.value) {
                return;
            }

            const latestOption = monthOptions[0];
            if (!latestOption) {
                return;
            }

            toolbarMonthId.value = latestOption.dataset.id;
            monthSearchInput.value = latestOption.dataset.label;
            monthOptions.forEach(opt => {
                opt.classList.toggle('selected', opt === latestOption);
            });
        }

        function positionMonthDropdown() {
            if (!monthPicker.classList.contains('open') || !monthSearchInput) return;
            const rect = monthSearchInput.getBoundingClientRect();
            monthPickerDropdown.style.top = `${rect.bottom + 6}px`;
            monthPickerDropdown.style.left = `${rect.left}px`;
            monthPickerDropdown.style.width = `${Math.max(rect.width, 220)}px`;
        }

        function openMonthPicker() {
            monthPicker.classList.add('open');
            if (!monthDropdownAnchor) {
                monthDropdownAnchor = monthPickerDropdown.parentElement;
            }
            if (monthPickerDropdown.parentElement !== document.body) {
                document.body.appendChild(monthPickerDropdown);
            }
            monthPickerDropdown.classList.add('is-portaled');
            positionMonthDropdown();
        }

        function closeMonthPicker() {
            monthPicker.classList.remove('open');
            monthPickerDropdown.classList.remove('is-portaled');
            monthPickerDropdown.style.top = '';
            monthPickerDropdown.style.left = '';
            monthPickerDropdown.style.width = '';
            if (monthDropdownAnchor && monthPickerDropdown.parentElement === document.body) {
                monthDropdownAnchor.appendChild(monthPickerDropdown);
            }
        }

        function isMonthPickerTarget(target) {
            return monthPicker.contains(target) || monthPickerDropdown.contains(target);
        }

        function selectMonth(id, label) {
            toolbarMonthId.value = id;
            monthSearchInput.value = label;
            monthOptions.forEach(opt => {
                opt.classList.toggle('selected', opt.dataset.id === String(id));
            });
            closeMonthPicker();
            fetchData('{{ route('tracker.index') }}', getParams(), { loading: true });
        }

        ensureDefaultMonthSelected();

        if (monthPicker && monthSearchInput) {
            monthSearchInput.addEventListener('focus', () => {
                openMonthPicker();
                filterMonthOptions(monthSearchInput.value);
            });

            monthSearchInput.addEventListener('input', () => {
                openMonthPicker();
                filterMonthOptions(monthSearchInput.value);
            });

            monthOptions.forEach(opt => {
                opt.addEventListener('click', () => {
                    selectMonth(opt.dataset.id, opt.dataset.label);
                });
            });

            document.addEventListener('click', (e) => {
                if (!isMonthPickerTarget(e.target)) {
                    closeMonthPicker();
                    const selected = document.querySelector('.month-picker-option.selected');
                    if (selected) {
                        monthSearchInput.value = selected.dataset.label;
                    }
                }
            });

            window.addEventListener('scroll', () => {
                if (monthPicker.classList.contains('open')) {
                    positionMonthDropdown();
                }
            }, true);

            window.addEventListener('resize', () => {
                if (monthPicker.classList.contains('open')) {
                    positionMonthDropdown();
                }
            });
        }

        function setTableLoading(isLoading, tabEl = null) {
            if (isLoading) {
                tableLoadingOverlay.classList.add('active');
                tableLoadingWrap.classList.add('is-loading');
                if (tabEl) tabEl.classList.add('is-loading');
                loadingTab = tabEl;
            } else {
                tableLoadingOverlay.classList.remove('active');
                tableLoadingWrap.classList.remove('is-loading');
                if (loadingTab) {
                    loadingTab.classList.remove('is-loading');
                    loadingTab = null;
                }
            }
        }

        function fetchData(url, params = {}, options = {}) {
            const fetchUrl = new URL(url, window.location.origin);
            Object.keys(params).forEach(key => {
                if (params[key]) fetchUrl.searchParams.set(key, params[key]);
            });

            const showLoading = options.loading === true;
            const loadingStarted = showLoading ? Date.now() : 0;

            if (showLoading) setTableLoading(true, options.tabEl || null);

            fetch(fetchUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                const finish = () => {
                    tableBody.innerHTML = data.table;
                    paginationContainer.innerHTML = data.pagination;
                    countText.textContent = data.count_text;

                    if (data.counts) {
                        Object.keys(data.counts).forEach(tabKey => {
                            const tabItem = document.querySelector(`.tab-item[data-tab="${tabKey}"]`);
                            if (tabItem) {
                                const badge = tabItem.querySelector('.tab-badge');
                                if (badge) badge.textContent = data.counts[tabKey];
                            }
                        });
                    }

                    if (typeof data.total_requisition !== 'undefined' && totalRequisitionEl) {
                        totalRequisitionEl.textContent = data.total_requisition;
                    }

                    if (data.attention_strip && attentionStripContainer) {
                        attentionStripContainer.innerHTML = data.attention_strip;
                    }

                    const activeFilters = [
                        document.getElementById('client_id').value,
                        document.getElementById('lead_recruiter_id').value
                    ].filter(v => v).length;

                    if (activeFilters > 0) {
                        filterBadge.textContent = activeFilters;
                        filterBadge.style.display = 'inline-flex';
                    } else {
                        filterBadge.style.display = 'none';
                    }

                    if (showLoading) setTableLoading(false);
                };

                if (showLoading) {
                    const elapsed = Date.now() - loadingStarted;
                    const minDelay = 280;
                    if (elapsed < minDelay) {
                        setTimeout(finish, minDelay - elapsed);
                    } else {
                        finish();
                    }
                } else {
                    finish();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (showLoading) setTableLoading(false);
            });
        }

        function getParams() {
            return {
                search: searchInput.value,
                month_id: toolbarMonthId.value,
                client_id: document.getElementById('client_id').value,
                lead_recruiter_id: document.getElementById('lead_recruiter_id').value,
                tab: currentTab
            };
        }

        // Export Modal Functions
        window.openExportModal = function() {
            const modal = document.getElementById('exportModal');
            const currentMonthId = toolbarMonthId.value;
            if (currentMonthId) {
                document.getElementById('export_month_id').value = currentMonthId;
            }
            modal.style.display = 'block';
        }

        window.closeExportModal = function() {
            document.getElementById('exportModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('exportModal');
            if (e.target === modal) {
                closeExportModal();
            }
        });

        // Tab switching
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                if (this.classList.contains('active') || this.classList.contains('is-loading')) return;
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentTab = this.dataset.tab;
                fetchData('{{ route('tracker.index') }}', getParams(), { loading: true, tabEl: this });
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchData('{{ route('tracker.index') }}', getParams(), { loading: true });
                }, 500);
            });
        }

        if (filterToggle) {
            filterToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                filterDropdown.classList.toggle('active');
            });
        }

        document.addEventListener('click', (e) => {
            if (filterDropdown && !filterDropdown.contains(e.target) && e.target !== filterToggle) {
                filterDropdown.classList.remove('active');
            }
        });

        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            fetchData('{{ route('tracker.index') }}', getParams());
            filterDropdown.classList.remove('active');
        });

        clearFilters.addEventListener('click', () => {
            filterForm.reset();
            fetchData('{{ route('tracker.index') }}', getParams());
            filterDropdown.classList.remove('active');
        });

        document.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('#paginationContainer a');
            if (paginationLink) {
                e.preventDefault();
                fetchData(paginationLink.getAttribute('href'), getParams());
            }
        });
    });
</script>
@endsection
