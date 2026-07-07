@extends('layouts.app')

@section('title', 'Recruiterment Workspace')
@section('page_heading', 'Recruiterment Workspace')

@section('content')
<div class="dashboard-page">
<div class="dash-enter dash-enter-1">
<div class="dashboard-toolbar">
    <div class="year-picker" id="yearPicker">
        <label class="month-picker-label" for="toolbarYear">Year</label>
        <div class="year-picker-field">
            <select id="toolbarYear" class="year-picker-select">
                @foreach($years as $year)
                    <option value="{{ $year }}" @selected($selectedYear == $year)>{{ $year }}</option>
                @endforeach
            </select>
            <svg class="year-picker-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
    </div>

    <div class="month-picker" id="monthPicker">
        <label class="month-picker-label">Month</label>
        <div class="month-picker-field">
            <svg class="month-picker-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <input type="text" id="monthSearchInput" class="month-picker-input" placeholder="All months" value="{{ $selectedMonth->month ?? '' }}" autocomplete="off">
            <input type="hidden" id="toolbarMonthId" value="{{ $selectedMonthId ?? 'all' }}">
            <svg class="month-picker-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            <div class="month-picker-dropdown" id="monthPickerDropdown">
                <button type="button" class="month-picker-option month-picker-option--all {{ empty($selectedMonthId) ? 'selected' : '' }}" data-id="all" data-label="All months" data-year="">All months</button>
                @foreach($allMonths as $month)
                    @php $monthYear = \Illuminate\Support\Str::afterLast($month->month, ' '); @endphp
                    <button type="button" class="month-picker-option {{ $selectedMonthId == $month->id ? 'selected' : '' }} {{ $monthYear !== $selectedYear ? 'hidden' : '' }}" data-id="{{ $month->id }}" data-label="{{ $month->month }}" data-year="{{ $monthYear }}">{{ $month->month }}</button>
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
        <a href="{{ route('tracker.create') }}" class="toolbar-btn toolbar-btn--primary" title="Add New Position">
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
        flex-shrink: 0;
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

    .year-picker {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 120px;
        position: relative;
        z-index: 1;
    }

    .year-picker-field {
        position: relative;
        display: flex;
        align-items: center;
    }

    .year-picker-select {
        width: 100%;
        appearance: none;
        -webkit-appearance: none;
        padding: 10px 36px 10px 14px;
        border: 1px solid rgba(13, 148, 136, 0.22);
        border-radius: 10px;
        background: #fff;
        font-size: 14px;
        font-weight: 500;
        color: #0f172a;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .year-picker-select:hover,
    .year-picker-select:focus {
        outline: none;
        border-color: var(--dash-teal);
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.12);
    }

    .year-picker-chevron {
        position: absolute;
        right: 12px;
        width: 16px;
        height: 16px;
        color: var(--dash-teal);
        opacity: 0.55;
        pointer-events: none;
    }

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

    .month-picker-option--all {
        font-weight: 600;
        color: var(--dash-teal);
        border-bottom: 1px solid rgba(13, 148, 136, 0.12);
        margin-bottom: 4px;
        padding-bottom: 10px;
    }

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

    .loc-extra {
        display: inline-block;
        margin-left: 6px;
        padding: 1px 7px;
        border-radius: 999px;
        background: rgba(241, 205, 134, 0.25);
        color: var(--dash-teal);
        font-size: 11px;
        font-weight: 700;
        cursor: default;
    }

    .cand-mix {
        display: block;
        margin-top: 3px;
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
    }
    .cand-mix__placed { color: #059669; }
    .cand-mix__active { color: #2563eb; }
    .cand-mix__dot { margin: 0 4px; color: #9ca3af; }

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

    /* Pipeline stage tabs */
    .workspace-tabs-panel {
        background: #fff;
        padding: 0;
    }

    .workspace-tabs-nav {
        display: flex;
        align-items: flex-end;
        gap: 3px;
        padding: 8px 10px 0;
        overflow: hidden;
        background: linear-gradient(180deg, #e8eeec 0%, #dfe8e5 100%);
        border-bottom: 2px solid #cfd9d6;
    }

    .tab-item {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        flex: 1 1 0;
        min-width: 0;
        padding: 7px 5px 9px;
        margin-bottom: -2px;
        color: #4d615d;
        cursor: pointer;
        border-radius: 10px 10px 0 0;
        transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        white-space: nowrap;
        font-size: 10.5px;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.45);
        border: 1px solid rgba(10, 45, 41, 0.08);
        border-bottom: none;
        font-family: inherit;
        position: relative;
        z-index: 1;
        min-height: 36px;
        box-shadow: inset 0 -2px 0 rgba(10, 45, 41, 0.04);
    }

    .tab-item:hover:not(.active):not(.is-loading) {
        background: rgba(255, 255, 255, 0.82);
        color: var(--dash-teal);
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(10, 45, 41, 0.08);
    }

    .tab-item.active {
        background: linear-gradient(135deg, var(--dash-teal) 0%, #0f3d38 100%);
        color: #fff;
        font-weight: 700;
        border-color: var(--dash-teal);
        border-bottom-color: var(--dash-teal);
        box-shadow: 0 -3px 12px rgba(10, 45, 41, 0.22);
        z-index: 3;
        transform: translateY(-1px);
        padding-bottom: 11px;
    }

    .tab-item.active::before {
        content: '';
        position: absolute;
        top: 0;
        left: 8px;
        right: 8px;
        height: 3px;
        border-radius: 0 0 3px 3px;
        background: linear-gradient(90deg, var(--dash-gold) 0%, #ffe4a8 50%, var(--dash-gold) 100%);
    }

    .tab-icon-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 6px;
        background: rgba(10, 45, 41, 0.06);
        flex-shrink: 0;
        transition: background 0.2s ease;
    }

    .tab-item.active .tab-icon-wrap {
        background: rgba(255, 255, 255, 0.16);
    }

    .tab-icon {
        width: 12px;
        height: 12px;
        flex-shrink: 0;
        opacity: 0.8;
    }

    .tab-item.active .tab-icon {
        opacity: 1;
        color: #fff;
    }

    .tab-label {
        line-height: 1.1;
        letter-spacing: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 10.5px;
    }

    .tab-badge {
        font-size: 9px;
        font-weight: 800;
        padding: 2px 5px;
        border-radius: 999px;
        min-width: 18px;
        text-align: center;
        line-height: 1;
        border: 1px solid transparent;
        flex-shrink: 0;
    }

    .tab-item.active .tab-badge {
        background: var(--dash-gold);
        color: var(--dash-teal);
        border-color: var(--dash-gold);
    }

    .tab-item:not(.active) .tab-badge {
        background: #fff;
        color: #5f726e;
        border-color: #d5dfdc;
    }

    .tab-item--unserved:not(.active) {
        background: rgba(255, 247, 237, 0.75);
        border-color: rgba(249, 115, 22, 0.15);
        color: #9a3412;
    }

    .tab-item--unserved.active {
        background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
        color: #fff;
        border-color: #ea580c;
        border-bottom-color: #ea580c;
    }

    .tab-item--unserved.active::before {
        background: linear-gradient(90deg, #fdba74 0%, #fb923c 50%, #fdba74 100%);
    }

    .tab-item--unserved.active .tab-badge {
        background: #fff;
        color: #c2410c;
        border-color: #fff;
    }

    .tab-item.is-loading {
        opacity: 0.7;
        pointer-events: none;
    }

    @media (max-width: 1100px) {
        .tab-icon-wrap { display: none; }
        .tab-item { gap: 3px; padding: 7px 4px 9px; }
        .tab-label { font-size: 10px; }
    }

    @media (max-width: 768px) {
        .workspace-tabs-nav {
            flex-wrap: wrap;
            gap: 4px;
            padding: 8px 8px 0;
        }

        .tab-item {
            flex: 1 1 calc(33.333% - 4px);
            min-width: calc(33.333% - 4px);
            font-size: 10px;
        }
    }

    .action-remarks:hover,
    .action-remarks.has-remarks {
        background: rgba(99, 102, 241, 0.1);
        border-color: rgba(99, 102, 241, 0.25);
        color: #4f46e5;
    }

    .remarks-modal textarea {
        width: 100%;
        min-height: 140px;
        padding: 12px;
        border: 1px solid #dfe8e5;
        border-radius: 8px;
        font-family: inherit;
        font-size: 13px;
        resize: vertical;
    }

    .remarks-modal .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 16px;
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

    .attention-pill--clickable {
        cursor: pointer;
        font-family: inherit;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .attention-pill--clickable:hover:not(.attention-pill--muted) {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(10, 45, 41, 0.08);
    }

    .attention-pill--clickable:focus-visible {
        outline: 2px solid var(--dash-gold);
        outline-offset: 2px;
    }

    .attention-modal {
        position: fixed;
        inset: 0;
        z-index: 1003;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.28s ease, visibility 0.28s ease;
    }

    .attention-modal.is-open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .attention-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(10, 45, 41, 0.45);
        backdrop-filter: blur(4px);
        opacity: 0;
        transition: opacity 0.28s ease;
    }

    .attention-modal.is-open .attention-modal__backdrop {
        opacity: 1;
    }

    .attention-modal__panel {
        position: relative;
        z-index: 1;
        width: min(760px, 100%);
        max-height: min(82vh, 720px);
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 24px 64px rgba(10, 45, 41, 0.22);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transform: translateY(18px) scale(0.98);
        opacity: 0;
        transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.28s ease;
    }

    .attention-modal.is-open .attention-modal__panel {
        transform: none;
        opacity: 1;
    }

    .attention-modal__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        background: linear-gradient(135deg, var(--dash-teal) 0%, #0d3a33 100%);
        color: #fff;
    }

    .attention-modal__head h2 {
        margin: 0 0 4px;
        font-size: 18px;
        font-weight: 700;
        color: #fff;
    }

    .attention-modal__head p {
        margin: 0;
        font-size: 13px;
        opacity: 0.85;
    }

    .attention-modal__close {
        background: rgba(255, 255, 255, 0.12);
        border: none;
        color: #fff;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        font-size: 22px;
        cursor: pointer;
        line-height: 1;
        flex-shrink: 0;
        transition: background 0.15s ease;
    }

    .attention-modal__close:hover {
        background: rgba(255, 255, 255, 0.22);
    }

    .attention-modal__body {
        padding: 0;
        overflow-y: auto;
        flex: 1;
        background: #fafcfb;
    }

    .attention-modal__loading,
    .attention-modal__empty {
        padding: 48px 24px;
        text-align: center;
        color: #7a8e8a;
        font-size: 14px;
    }

    .attention-card-list {
        list-style: none;
        margin: 0;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .attention-card {
        background: #fff;
        border: 1px solid #e2ebe9;
        border-radius: 14px;
        padding: 16px 18px;
        box-shadow: 0 2px 8px rgba(10, 45, 41, 0.04);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .attention-card:hover {
        border-color: rgba(241, 205, 134, 0.55);
        box-shadow: 0 6px 18px rgba(10, 45, 41, 0.08);
    }

    .attention-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }

    .attention-card__title {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: var(--dash-teal);
        line-height: 1.35;
    }

    .attention-card__title a {
        color: inherit;
        text-decoration: none;
    }

    .attention-card__title a:hover {
        text-decoration: underline;
    }

    .attention-card__id {
        font-size: 11px;
        font-weight: 700;
        color: #7a8e8a;
        white-space: nowrap;
    }

    .attention-card__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 14px;
        font-size: 12px;
        color: #5a6e6a;
        margin-bottom: 10px;
    }

    .attention-card__meta strong {
        color: #374151;
        font-weight: 600;
    }

    .attention-card__detail {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 10px;
        background: rgba(241, 205, 134, 0.15);
        color: #8a6d1f;
        border: 1px solid rgba(241, 205, 134, 0.35);
    }

    .attention-card--overdue .attention-card__detail {
        background: rgba(180, 35, 24, 0.08);
        color: #b42318;
        border-color: rgba(180, 35, 24, 0.18);
    }

    .attention-card--due_soon .attention-card__detail {
        background: rgba(181, 71, 8, 0.08);
        color: #b54708;
        border-color: rgba(181, 71, 8, 0.18);
    }

    .attention-card__desc {
        margin: 0;
        font-size: 13px;
        line-height: 1.55;
        color: #4b5c58;
        white-space: pre-wrap;
    }

    .attention-card__actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 12px;
    }

    .attention-card__link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        color: var(--dash-teal);
        background: rgba(10, 45, 41, 0.06);
        border: 1px solid rgba(10, 45, 41, 0.1);
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .attention-card__link:hover {
        background: rgba(241, 205, 134, 0.2);
        transform: translateY(-1px);
    }

    @media (prefers-reduced-motion: reduce) {
        .attention-modal,
        .attention-modal__backdrop,
        .attention-modal__panel,
        .attention-pill--clickable {
            transition: none;
        }
    }

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

<!-- Tabs -->
<div class="workspace-tabs-panel">
    <div class="workspace-tabs-nav" id="tabsContainer" role="tablist" aria-label="Pipeline stages">
    <div class="tab-item {{ request('tab', 'demand_raised') == 'demand_raised' ? 'active' : '' }}" data-tab="demand_raised" role="tab" aria-selected="{{ request('tab', 'demand_raised') == 'demand_raised' ? 'true' : 'false' }}" title="Demand Raised">
        <span class="tab-icon-wrap"><svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg></span>
        <span class="tab-label">Demand</span>
        <span class="tab-badge">{{ $counts['demand_raised'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'identified' ? 'active' : '' }}" data-tab="identified" role="tab" aria-selected="{{ request('tab') == 'identified' ? 'true' : 'false' }}">
        <span class="tab-icon-wrap"><svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></span>
        <span class="tab-label">Identified</span>
        <span class="tab-badge">{{ $counts['identified'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'screening' ? 'active' : '' }}" data-tab="screening" role="tab" aria-selected="{{ request('tab') == 'screening' ? 'true' : 'false' }}" title="Initial Screening">
        <span class="tab-icon-wrap"><svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg></span>
        <span class="tab-label">Screening</span>
        <span class="tab-badge">{{ $counts['screening'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'submission' ? 'active' : '' }}" data-tab="submission" role="tab" aria-selected="{{ request('tab') == 'submission' ? 'true' : 'false' }}">
        <span class="tab-icon-wrap"><svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg></span>
        <span class="tab-label">Submission</span>
        <span class="tab-badge">{{ $counts['submission'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'interview' ? 'active' : '' }}" data-tab="interview" role="tab" aria-selected="{{ request('tab') == 'interview' ? 'true' : 'false' }}">
        <span class="tab-icon-wrap"><svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg></span>
        <span class="tab-label">Interview</span>
        <span class="tab-badge">{{ $counts['interview'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'decision' ? 'active' : '' }}" data-tab="decision" role="tab" aria-selected="{{ request('tab') == 'decision' ? 'true' : 'false' }}">
        <span class="tab-icon-wrap"><svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg></span>
        <span class="tab-label">Decision</span>
        <span class="tab-badge">{{ $counts['decision'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'accepted' ? 'active' : '' }}" data-tab="accepted" role="tab" aria-selected="{{ request('tab') == 'accepted' ? 'true' : 'false' }}">
        <span class="tab-icon-wrap"><svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
        <span class="tab-label">Accepted</span>
        <span class="tab-badge">{{ $counts['accepted'] ?? 0 }}</span>
    </div>
    <div class="tab-item {{ request('tab') == 'rejected' ? 'active' : '' }}" data-tab="rejected" role="tab" aria-selected="{{ request('tab') == 'rejected' ? 'true' : 'false' }}">
        <span class="tab-icon-wrap"><svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
        <span class="tab-label">Rejected</span>
        <span class="tab-badge">{{ $counts['rejected'] ?? 0 }}</span>
    </div>
    <div class="tab-item tab-item--unserved {{ request('tab') == 'unserved' ? 'active' : '' }}" data-tab="unserved" role="tab" aria-selected="{{ request('tab') == 'unserved' ? 'true' : 'false' }}">
        <span class="tab-icon-wrap"><svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg></span>
        <span class="tab-label">Unserved</span>
        <span class="tab-badge">{{ $counts['unserved'] ?? 0 }}</span>
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
                <th>Position Name</th>
                <th>Location</th>
                <th>Client</th>
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

<!-- Attention Modal -->
<div class="attention-modal" id="attentionModal" aria-hidden="true">
    <div class="attention-modal__backdrop" data-close-attention></div>
    <div class="attention-modal__panel" role="dialog" aria-labelledby="attentionModalTitle" aria-modal="true">
        <div class="attention-modal__head">
            <div>
                <h2 id="attentionModalTitle">Needs Attention</h2>
                <p id="attentionModalSubtitle"></p>
            </div>
            <button type="button" class="attention-modal__close" data-close-attention aria-label="Close">&times;</button>
        </div>
        <div class="attention-modal__body" id="attentionModalBody">
            <div class="attention-modal__loading" id="attentionModalLoading">Loading...</div>
        </div>
    </div>
</div>

<!-- Remarks Modal -->
<div id="remarksModal" class="modal remarks-modal" style="display: none; position: fixed; z-index: 1002; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
    <div class="modal-content" style="background-color: #fefefe; margin: 12% auto; padding: 20px; border: 1px solid #dfe8e5; width: min(480px, 92vw); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
        <h2 id="remarksModalTitle" style="font-size: 18px; margin: 0 0 6px; color: #0a2d29;">Remarks</h2>
        <p id="remarksModalSubtitle" style="font-size: 12px; color: #6b7280; margin: 0 0 14px;"></p>
        <textarea id="remarksTextarea" placeholder="Add remarks for this position..."></textarea>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeRemarksModal()" style="padding: 8px 16px; font-size: 12px;">Cancel</button>
            <button type="button" class="btn btn-primary" id="remarksSaveBtn" style="padding: 8px 16px; font-size: 12px;">Save Remarks</button>
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
        const toolbarYear = document.getElementById('toolbarYear');
        const monthPickerDropdown = document.getElementById('monthPickerDropdown');
        const monthOptions = document.querySelectorAll('.month-picker-option');
        const totalRequisitionEl = document.getElementById('totalRequisition');
        const attentionStripContainer = document.getElementById('attentionStripContainer');
        const attentionModal = document.getElementById('attentionModal');
        const attentionModalTitle = document.getElementById('attentionModalTitle');
        const attentionModalSubtitle = document.getElementById('attentionModalSubtitle');
        const attentionModalBody = document.getElementById('attentionModalBody');
        const activeTabEl = document.querySelector('.tab-item.active');
        let currentTab = activeTabEl ? activeTabEl.dataset.tab : 'demand_raised';
        let debounceTimer;
        let loadingTab = null;

        function filterMonthOptions(query) {
            const q = query.trim().toLowerCase();
            const year = toolbarYear ? toolbarYear.value : '';
            monthOptions.forEach(opt => {
                const label = (opt.dataset.label || opt.textContent).toLowerCase();
                const matchesYear = opt.classList.contains('month-picker-option--all') || opt.dataset.year === year;
                const matchesSearch = q === '' || label.includes(q);
                opt.classList.toggle('hidden', !matchesYear || !matchesSearch);
            });
        }

        function syncMonthOptionsForYear() {
            const year = toolbarYear ? toolbarYear.value : '';
            monthOptions.forEach(opt => {
                if (opt.classList.contains('month-picker-option--all')) {
                    return;
                }
                opt.classList.toggle('hidden', opt.dataset.year !== year);
            });
        }

        function clearMonthSelection() {
            toolbarMonthId.value = 'all';
            monthSearchInput.value = '';
            monthOptions.forEach(opt => {
                opt.classList.toggle('selected', opt.classList.contains('month-picker-option--all'));
            });
        }

        let monthDropdownAnchor = null;

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
            const monthId = id ? String(id) : 'all';
            toolbarMonthId.value = monthId;
            monthSearchInput.value = monthId === 'all' ? '' : label;
            monthOptions.forEach(opt => {
                const optId = opt.dataset.id || 'all';
                opt.classList.toggle('selected', optId === monthId);
            });
            closeMonthPicker();
            fetchData('{{ route('tracker.index') }}', getParams(), { loading: true });
        }

        if (toolbarYear) {
            toolbarYear.addEventListener('change', () => {
                clearMonthSelection();
                syncMonthOptionsForYear();
                fetchData('{{ route('tracker.index') }}', getParams(), { loading: true });
            });
        }

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
                    if (toolbarMonthId.value) {
                        const selected = document.querySelector('.month-picker-option.selected');
                        if (selected) {
                            monthSearchInput.value = selected.dataset.label;
                        }
                    } else {
                        monthSearchInput.value = '';
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
                if (key === 'month_id' || params[key]) {
                    fetchUrl.searchParams.set(key, params[key]);
                }
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
                year: toolbarYear ? toolbarYear.value : '',
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
            if (currentMonthId && currentMonthId !== 'all') {
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

        // Remarks modal
        const remarksModal = document.getElementById('remarksModal');
        const remarksTextarea = document.getElementById('remarksTextarea');
        const remarksSaveBtn = document.getElementById('remarksSaveBtn');
        let activeRemarksId = null;

        window.closeRemarksModal = function() {
            if (remarksModal) remarksModal.style.display = 'none';
            activeRemarksId = null;
        };

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-remarks-btn]');
            if (btn) {
                activeRemarksId = btn.dataset.id;
                document.getElementById('remarksModalSubtitle').textContent = '#' + btn.dataset.id + ' — ' + btn.dataset.position;
                remarksTextarea.value = btn.dataset.remarks || '';
                remarksModal.style.display = 'block';
                remarksTextarea.focus();
                return;
            }
            if (e.target === remarksModal) {
                closeRemarksModal();
            }
        });

        if (remarksSaveBtn) {
            remarksSaveBtn.addEventListener('click', function() {
                if (!activeRemarksId) return;
                remarksSaveBtn.disabled = true;
                fetch('/tracker/info/' + activeRemarksId + '/remarks', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ remarks: remarksTextarea.value }),
                })
                .then(res => res.json())
                .then(() => {
                    closeRemarksModal();
                    fetchData('{{ route('tracker.index') }}', getParams(), { loading: false });
                })
                .finally(() => { remarksSaveBtn.disabled = false; });
            });
        }

        // Attention modal
        function escapeHtml(str) {
            return String(str == null ? '' : str).replace(/[&<>"']/g, function (m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
            });
        }

        function closeAttentionModal() {
            if (!attentionModal) return;
            attentionModal.classList.remove('is-open');
            attentionModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function renderAttentionItems(data) {
            if (!data.items || !data.items.length) {
                attentionModalBody.innerHTML = '<div class="attention-modal__empty">No matching demands for the current filters.</div>';
                return;
            }

            attentionModalBody.innerHTML = '<ul class="attention-card-list">' + data.items.map(function (item) {
                return '<li class="attention-card attention-card--' + escapeHtml(data.type) + '">'
                    + '<div class="attention-card__top">'
                    + '<h3 class="attention-card__title"><a href="' + escapeHtml(item.url) + '">' + escapeHtml(item.position) + '</a></h3>'
                    + '<span class="attention-card__id">#' + escapeHtml(item.id) + '</span>'
                    + '</div>'
                    + '<div class="attention-card__meta">'
                    + '<span><strong>Client:</strong> ' + escapeHtml(item.client) + '</span>'
                    + '<span><strong>Recruiter:</strong> ' + escapeHtml(item.recruiter) + '</span>'
                    + '<span><strong>Month:</strong> ' + escapeHtml(item.month) + '</span>'
                    + '<span><strong>Deadline:</strong> ' + escapeHtml(item.deadline) + '</span>'
                    + '</div>'
                    + '<div class="attention-card__detail">' + escapeHtml(item.detail) + '</div>'
                    + '<p class="attention-card__desc">' + escapeHtml(item.job_description) + '</p>'
                    + '<div class="attention-card__actions">'
                    + '<a class="attention-card__link" href="' + escapeHtml(item.url) + '">View position</a>'
                    + '</div>'
                    + '</li>';
            }).join('') + '</ul>';
        }

        function openAttentionModal(type) {
            if (!attentionModal) return;

            attentionModalTitle.textContent = 'Loading...';
            attentionModalSubtitle.textContent = '';
            attentionModalBody.innerHTML = '<div class="attention-modal__loading">Loading demands...</div>';
            attentionModal.classList.add('is-open');
            attentionModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            const params = getParams();
            const url = new URL('{{ url('/tracker/attention') }}/' + type, window.location.origin);
            Object.keys(params).forEach(key => {
                if (key !== 'tab' && (key === 'month_id' || params[key])) {
                    url.searchParams.set(key, params[key]);
                }
            });

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                attentionModalTitle.textContent = data.title + ' (' + data.count + ')';
                attentionModalSubtitle.textContent = data.subtitle || '';
                renderAttentionItems(data);
            })
            .catch(() => {
                attentionModalBody.innerHTML = '<div class="attention-modal__empty">Could not load demands. Please try again.</div>';
            });
        }

        if (attentionStripContainer) {
            attentionStripContainer.addEventListener('click', function (e) {
                const pill = e.target.closest('[data-attention-type]');
                if (!pill) return;
                openAttentionModal(pill.dataset.attentionType);
            });
        }

        if (attentionModal) {
            attentionModal.querySelectorAll('[data-close-attention]').forEach(function (el) {
                el.addEventListener('click', closeAttentionModal);
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && attentionModal.classList.contains('is-open')) {
                    closeAttentionModal();
                }
            });
        }

        // Tab switching
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                if (this.classList.contains('active') || this.classList.contains('is-loading')) return;
                tabs.forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');
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
</div>
@endsection
