<style>
    :root,
    .crud-page,
    .crud-modal-overlay,
    .crud-form-card {
        --c-primary: #0a2d29;
        --c-primary-mid: #0f3d37;
        --c-accent: #f1cd86;
        --c-accent-dim: #c9a85c;
        --c-surface: #ffffff;
        --c-border: #e5e7eb;
        --c-muted: #6b7280;
        --c-text: #111827;
        --c-danger: #dc2626;
        --radius: 14px;
        --shadow: 0 4px 24px rgba(10, 45, 41, 0.08);
    }

    .crud-page {
        max-width: 1100px;
        margin: 0 auto;
        padding-bottom: 48px;
    }

    @keyframes crudFadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .crud-enter { animation: crudFadeUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) backwards; }
    .crud-enter-1 { animation-delay: 0.04s; }
    .crud-enter-2 { animation-delay: 0.1s; }
    .crud-enter-3 { animation-delay: 0.16s; }
    .crud-enter-4 { animation-delay: 0.22s; }

    /* ── Hero ── */
    .crud-hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }
    .crud-hero__eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--c-accent-dim);
        margin-bottom: 6px;
    }
    .crud-hero__title {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 800;
        color: var(--c-primary);
        letter-spacing: -0.02em;
        line-height: 1.15;
    }
    .crud-hero__sub {
        margin-top: 8px;
        font-size: 14px;
        color: var(--c-muted);
        max-width: 520px;
    }
    .crud-btn-add {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 22px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-primary-mid) 100%);
        color: var(--c-accent);
        font-family: inherit;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(10, 45, 41, 0.25);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .crud-btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(10, 45, 41, 0.3);
    }
    .crud-btn-add svg { width: 18px; height: 18px; }

    /* ── Stats ── */
    .crud-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }
    .crud-stat {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: var(--radius);
        padding: 16px 18px;
        box-shadow: var(--shadow);
        transition: transform 0.25s ease, border-color 0.25s ease;
    }
    .crud-stat:hover {
        transform: translateY(-2px);
        border-color: rgba(241, 205, 134, 0.5);
    }
    .crud-stat__num {
        font-size: 28px;
        font-weight: 800;
        color: var(--c-primary);
        line-height: 1;
    }
    .crud-stat__lbl {
        font-size: 12px;
        color: var(--c-muted);
        margin-top: 6px;
        font-weight: 500;
    }

    /* ── Tabs ── */
    .crud-tabs {
        display: inline-flex;
        gap: 4px;
        padding: 5px;
        background: #eef0f1;
        border-radius: 12px;
        margin-bottom: 18px;
    }
    .crud-tab {
        padding: 10px 22px;
        border: none;
        border-radius: 9px;
        background: transparent;
        font-family: inherit;
        font-size: 13px;
        font-weight: 700;
        color: var(--c-muted);
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .crud-tab:hover { color: var(--c-primary); }
    .crud-tab.is-active {
        background: var(--c-primary);
        color: var(--c-accent);
        box-shadow: 0 3px 10px rgba(10, 45, 41, 0.25);
    }
    .crud-tab__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        background: rgba(10, 45, 41, 0.08);
        color: inherit;
    }
    .crud-tab.is-active .crud-tab__count {
        background: rgba(241, 205, 134, 0.25);
    }

    /* ── Toolbar / Search ── */
    .crud-toolbar {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: var(--radius);
        padding: 16px 18px;
        margin-bottom: 18px;
        box-shadow: var(--shadow);
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        align-items: center;
    }
    .crud-search {
        flex: 1;
        min-width: 240px;
        position: relative;
    }
    .crud-search__icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: var(--c-muted);
        pointer-events: none;
    }
    .crud-search__input {
        width: 100%;
        padding: 12px 16px 12px 42px;
        border: 2px solid var(--c-border);
        border-radius: 12px;
        font-family: inherit;
        font-size: 14px;
        color: var(--c-text);
        background: #fafbfc;
        transition: border-color 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
    }
    .crud-search__input:focus {
        outline: none;
        border-color: var(--c-accent-dim);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(241, 205, 134, 0.2);
    }
    .crud-search-meta {
        font-size: 13px;
        color: var(--c-muted);
        white-space: nowrap;
    }
    .crud-search-meta strong { color: var(--c-primary); font-weight: 700; }

    /* ── Table ── */
    .crud-table-card {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    .crud-table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .crud-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .crud-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: linear-gradient(180deg, var(--c-primary) 0%, var(--c-primary-mid) 100%);
        color: #fff;
        padding: 14px 16px;
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }
    .crud-table thead th.is-left { text-align: left; }
    .crud-table tbody tr {
        border-bottom: 1px solid #f0f1f3;
        transition: background 0.2s ease;
        text-align: center;
    }
    .crud-table tbody tr:hover { background: #f8faf9; }
    .crud-table tbody tr.is-hidden { display: none; }
    .crud-table td {
        padding: 14px 16px;
        vertical-align: middle;
        text-align: center;
        color: #374151;
    }
    .crud-table td.is-left { text-align: left; }

    .crud-name {
        font-weight: 700;
        color: var(--c-primary);
        font-size: 14px;
    }
    .crud-index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 9px;
        background: #f3f4f6;
        color: var(--c-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .crud-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .crud-badge--gold {
        background: rgba(241, 205, 134, 0.22);
        color: #7c5e1e;
        border-color: rgba(201, 168, 92, 0.5);
    }
    .crud-badge--muted {
        background: #f3f4f6;
        color: var(--c-muted);
        border-color: #e5e7eb;
    }

    .crud-count-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        padding: 4px 10px;
        border-radius: 8px;
        background: var(--c-primary);
        color: var(--c-accent);
        font-size: 12px;
        font-weight: 800;
    }
    .crud-count-pill--zero {
        background: #f3f4f6;
        color: var(--c-muted);
    }

    .crud-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .crud-act {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid var(--c-border);
        background: #fff;
        color: var(--c-primary);
        cursor: pointer;
        font-family: inherit;
        transition: all 0.2s ease;
    }
    .crud-act:hover { background: #f3f4f6; }
    .crud-act--danger {
        color: var(--c-danger);
        border-color: #fecaca;
        background: #fef2f2;
    }
    .crud-act--danger:hover { background: #fee2e2; }

    /* ── Empty state ── */
    .crud-empty {
        text-align: center;
        padding: 56px 24px;
        color: var(--c-muted);
    }
    .crud-empty svg {
        width: 52px;
        height: 52px;
        opacity: 0.35;
        margin-bottom: 16px;
    }
    .crud-empty h3 {
        font-size: 18px;
        color: var(--c-primary);
        margin-bottom: 8px;
    }
    .crud-empty a { color: var(--c-accent-dim); font-weight: 700; }

    /* ── Pagination ── */
    .crud-pagination {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }
    .crud-pagination .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        gap: 6px;
    }
    .crud-pagination .pagination li a,
    .crud-pagination .pagination li span {
        display: inline-block;
        padding: 8px 13px;
        border: 1px solid var(--c-border);
        border-radius: 9px;
        color: var(--c-primary);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        background: #fff;
        transition: all 0.2s ease;
    }
    .crud-pagination .pagination li a:hover {
        background: rgba(241, 205, 134, 0.25);
        border-color: var(--c-accent-dim);
    }
    .crud-pagination .pagination li.active span {
        background: var(--c-primary);
        border-color: var(--c-primary);
        color: var(--c-accent);
    }
    .crud-pagination .pagination li.disabled span {
        color: #c0c4c9;
        cursor: not-allowed;
    }

    /* ── Modal ── */
    .crud-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 2000;
        background: rgba(10, 45, 41, 0.45);
        backdrop-filter: blur(6px);
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 60px 16px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .crud-modal-overlay.is-open { opacity: 1; visibility: visible; }
    .crud-modal {
        width: 100%;
        max-width: 520px;
        max-height: 85vh;
        overflow-y: auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2);
        transform: translateY(24px) scale(0.98);
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        color: #111827;
        color: var(--c-text);
    }
    .crud-modal-overlay.is-open .crud-modal { transform: translateY(0) scale(1); }
    .crud-modal__head {
        padding: 20px 24px;
        background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-primary-mid) 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    .crud-modal__head h2 { margin: 0; font-size: 19px; font-weight: 800; }
    .crud-modal__close {
        width: 34px;
        height: 34px;
        border: none;
        border-radius: 10px;
        background: rgba(255,255,255,0.15);
        color: #fff;
        font-size: 21px;
        cursor: pointer;
        line-height: 1;
        transition: background 0.2s ease;
    }
    .crud-modal__close:hover { background: rgba(255,255,255,0.28); }
    .crud-modal__body { padding: 24px; }
    .crud-modal__foot {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 18px;
    }

    /* ── Form fields ── */
    .crud-field { margin-bottom: 16px; min-width: 0; }
    .crud-field:last-child { margin-bottom: 0; }
    .crud-field label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: var(--c-primary);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .crud-field input,
    .crud-field select,
    .crud-field textarea {
        width: 100%;
        padding: 11px 13px;
        border: 1px solid #e5e7eb;
        border: 1px solid var(--c-border);
        border-radius: 9px;
        font-family: inherit;
        font-size: 13px;
        color: #111827;
        color: var(--c-text);
        background: #fff;
        box-sizing: border-box;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .crud-field input:focus,
    .crud-field select:focus,
    .crud-field textarea:focus {
        outline: none;
        border-color: var(--c-accent-dim);
        box-shadow: 0 0 0 3px rgba(241, 205, 134, 0.25);
    }
    .crud-field__error {
        color: var(--c-danger);
        font-size: 11px;
        margin-top: 4px;
    }
    .crud-field__hint {
        color: var(--c-muted);
        font-size: 11px;
        margin-top: 4px;
    }

    /* ── Segmented control (radio pills) ── */
    .crud-seg {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .crud-seg input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .crud-seg label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 11px 10px;
        margin: 0;
        border: 2px solid var(--c-border);
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        color: var(--c-muted);
        text-transform: none;
        letter-spacing: 0;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }
    .crud-seg input[type="radio"]:checked + label {
        border-color: var(--c-primary);
        background: var(--c-primary);
        color: var(--c-accent);
        box-shadow: 0 3px 10px rgba(10, 45, 41, 0.22);
    }
    .crud-seg input[type="radio"]:focus-visible + label {
        box-shadow: 0 0 0 3px rgba(241, 205, 134, 0.35);
    }

    /* ── Buttons ── */
    .crud-btn {
        padding: 10px 22px;
        border-radius: 10px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .crud-btn--primary {
        background: var(--c-primary);
        color: var(--c-accent);
    }
    .crud-btn--ghost {
        background: #f3f4f6;
        color: var(--c-text);
    }
    .crud-btn:hover { transform: translateY(-1px); }

    /* ── Standalone form card (create / edit pages) ── */
    .crud-form-card {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        max-width: 720px;
        margin: 0 auto;
    }
    .crud-form-card__head {
        padding: 22px 26px;
        background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-primary-mid) 100%);
        color: #fff;
    }
    .crud-form-card__head h2 { margin: 0; font-size: 19px; font-weight: 800; }
    .crud-form-card__head p { margin: 6px 0 0; font-size: 12px; color: rgba(255,255,255,0.75); }
    .crud-form-card__body { padding: 26px; }
    .crud-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    @media (max-width: 640px) { .crud-form-grid { grid-template-columns: 1fr; } }
    .crud-form-grid .span-2 { grid-column: 1 / -1; }
    .crud-form-grid .crud-field { margin-bottom: 0; }

    @media (prefers-reduced-motion: reduce) {
        .crud-enter, .crud-modal, .crud-modal-overlay {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
