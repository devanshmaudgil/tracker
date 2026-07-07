<style>
    :root,
    .cand-page,
    .cand-modal-overlay,
    .cand-detail-overlay {
        --c-primary: #0a2d29;
        --c-primary-mid: #0f3d37;
        --c-accent: #f1cd86;
        --c-accent-dim: #c9a85c;
        --c-surface: #ffffff;
        --c-border: #e5e7eb;
        --c-muted: #6b7280;
        --c-text: #111827;
        --c-danger: #dc2626;
        --c-success: #059669;
        --radius: 14px;
        --shadow: 0 4px 24px rgba(10, 45, 41, 0.08);
    }

    .cand-page {
        max-width: 1400px;
        margin: 0 auto;
        padding-bottom: 48px;
    }

    /* ── Entrance animations ── */
    @keyframes candFadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes candPulseRing {
        0%, 100% { box-shadow: 0 0 0 0 rgba(241, 205, 134, 0.45); }
        50%      { box-shadow: 0 0 0 6px rgba(241, 205, 134, 0); }
    }
    @keyframes candHlPop {
        from { background: rgba(241, 205, 134, 0.95); transform: scale(1.08); }
        to   { background: rgba(241, 205, 134, 0.55); transform: scale(1); }
    }
    @keyframes candShimmer {
        0%   { background-position: -200% center; }
        100% { background-position: 200% center; }
    }

    .cand-enter {
        animation: candFadeUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) backwards;
    }
    .cand-enter-1 { animation-delay: 0.04s; }
    .cand-enter-2 { animation-delay: 0.1s; }
    .cand-enter-3 { animation-delay: 0.16s; }
    .cand-enter-4 { animation-delay: 0.22s; }

    /* ── Hero ── */
    .cand-hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }
    .cand-hero__eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--c-accent-dim);
        margin-bottom: 6px;
    }
    .cand-hero__title {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 800;
        color: var(--c-primary);
        letter-spacing: -0.02em;
        line-height: 1.15;
    }
    .cand-hero__sub {
        margin-top: 8px;
        font-size: 14px;
        color: var(--c-muted);
        max-width: 520px;
    }
    .cand-btn-add {
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
        box-shadow: 0 4px 14px rgba(10, 45, 41, 0.25);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .cand-btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(10, 45, 41, 0.3);
    }
    .cand-btn-add svg { width: 18px; height: 18px; }

    /* ── Stats ── */
    .cand-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }
    @media (max-width: 900px) { .cand-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .cand-stats { grid-template-columns: 1fr; } }

    .cand-stat {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: var(--radius);
        padding: 16px 18px;
        box-shadow: var(--shadow);
        transition: transform 0.25s ease, border-color 0.25s ease;
    }
    .cand-stat:hover {
        transform: translateY(-2px);
        border-color: rgba(241, 205, 134, 0.5);
    }
    .cand-stat__num {
        font-size: 28px;
        font-weight: 800;
        color: var(--c-primary);
        line-height: 1;
    }
    .cand-stat__lbl {
        font-size: 12px;
        color: var(--c-muted);
        margin-top: 6px;
        font-weight: 500;
    }

    /* ── Search toolbar ── */
    .cand-toolbar {
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

    .cand-search {
        flex: 1;
        min-width: 260px;
        position: relative;
    }
    .cand-search__icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        color: var(--c-muted);
        pointer-events: none;
        transition: color 0.2s ease;
    }
    .cand-search.is-active .cand-search__icon { color: var(--c-primary); }
    .cand-search__input {
        width: 100%;
        padding: 14px 44px 14px 44px;
        border: 2px solid var(--c-border);
        border-radius: 12px;
        font-family: inherit;
        font-size: 15px;
        color: var(--c-text);
        background: #fafbfc;
        transition: border-color 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
    }
    .cand-search__input:focus {
        outline: none;
        border-color: var(--c-accent-dim);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(241, 205, 134, 0.2);
    }
    .cand-search.is-active .cand-search__input {
        animation: candPulseRing 2s ease infinite;
    }
    .cand-search__clear {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%) scale(0.8);
        opacity: 0;
        pointer-events: none;
        width: 28px;
        height: 28px;
        border: none;
        border-radius: 8px;
        background: #f3f4f6;
        color: var(--c-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.2s ease, transform 0.2s ease, background 0.2s ease;
    }
    .cand-search__clear.is-visible {
        opacity: 1;
        transform: translateY(-50%) scale(1);
        pointer-events: auto;
    }
    .cand-search__clear:hover { background: #e5e7eb; color: var(--c-text); }

    .cand-search-meta {
        font-size: 13px;
        color: var(--c-muted);
        white-space: nowrap;
    }
    .cand-search-meta strong {
        color: var(--c-primary);
        font-weight: 700;
    }

    .cand-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .cand-chip {
        padding: 7px 14px;
        border-radius: 999px;
        border: 1px solid var(--c-border);
        background: #f9fafb;
        font-size: 12px;
        font-weight: 600;
        color: var(--c-muted);
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: inherit;
    }
    .cand-chip:hover {
        border-color: var(--c-accent-dim);
        color: var(--c-primary);
    }
    .cand-chip.is-active {
        background: var(--c-primary);
        border-color: var(--c-primary);
        color: var(--c-accent);
    }

    /* ── Character highlight marks ── */
    mark.cand-hl,
    .cand-hl {
        background: linear-gradient(120deg, rgba(241, 205, 134, 0.7) 0%, rgba(255, 228, 168, 0.9) 100%);
        color: var(--c-primary);
        font-weight: 700;
        padding: 0 1px;
        border-radius: 3px;
        animation: candHlPop 0.35s cubic-bezier(0.22, 1, 0.36, 1) backwards;
        animation-delay: calc(var(--hi, 0) * 28ms);
        box-decoration-break: clone;
        -webkit-box-decoration-break: clone;
    }

    /* ── Table card ── */
    .cand-table-card {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    .cand-table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .cand-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .cand-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: linear-gradient(180deg, var(--c-primary) 0%, var(--c-primary-mid) 100%);
        color: #fff;
        padding: 14px 12px;
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }
    .cand-table tbody tr {
        border-bottom: 1px solid #f0f1f3;
        transition: opacity 0.3s ease, transform 0.3s ease, background 0.2s ease, max-height 0.3s ease;
        text-align: center;
    }
    .cand-table tbody tr:hover { background: #f8faf9; }
    .cand-table tbody tr.is-hidden {
        display: none;
    }
    .cand-table tbody tr.is-filtered-out {
        opacity: 0;
        transform: translateY(-6px);
        pointer-events: none;
        position: absolute;
        visibility: hidden;
    }
    .cand-table td {
        padding: 14px 12px;
        vertical-align: middle;
        text-align: center;
        color: #374151;
    }

    .cand-col-person,
    .cand-table thead th.cand-col-person {
        text-align: left;
    }

    .cand-person {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 12px;
        min-width: 180px;
        text-align: left;
    }
    .cand-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-primary-mid) 100%);
        color: var(--c-accent);
        font-size: 13px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(10, 45, 41, 0.2);
    }
    .cand-person__name {
        font-weight: 700;
        color: var(--c-primary);
        font-size: 14px;
        line-height: 1.3;
        text-align: left;
    }
    .cand-person__email {
        font-size: 11px;
        color: var(--c-muted);
        margin-top: 2px;
        word-break: break-all;
        text-align: left;
    }

    .cand-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .cand-badge--muted {
        background: #f3f4f6;
        color: var(--c-muted);
        border-color: #e5e7eb;
    }

    .cand-job-link {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        margin: 2px;
        border-radius: 6px;
        background: var(--c-primary);
        color: var(--c-accent);
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.15s ease, background 0.15s ease;
    }
    .cand-job-link:hover {
        transform: scale(1.05);
        background: var(--c-primary-mid);
    }

    .cand-resume-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        border-radius: 8px;
        background: rgba(241, 205, 134, 0.25);
        color: var(--c-primary);
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid rgba(241, 205, 134, 0.5);
        transition: background 0.2s ease;
    }
    .cand-resume-btn:hover { background: rgba(241, 205, 134, 0.45); }

    .cand-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .cand-act {
        padding: 6px 12px;
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
    .cand-act:hover { background: #f3f4f6; }
    .cand-act--danger {
        color: var(--c-danger);
        border-color: #fecaca;
        background: #fef2f2;
    }
    .cand-act--danger:hover { background: #fee2e2; }

    .cand-view-btn {
        width: 32px;
        height: 32px;
        border: 1px solid var(--c-border);
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--c-primary);
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
    }
    .cand-view-btn svg { width: 16px; height: 16px; }
    .cand-view-btn:hover {
        background: rgba(241, 205, 134, 0.2);
        border-color: var(--c-accent-dim);
        transform: scale(1.05);
    }

    /* ── Candidate detail card modal ── */
    .cand-detail-overlay {
        position: fixed;
        inset: 0;
        z-index: 2100;
        background: rgba(10, 45, 41, 0.45);
        backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 16px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .cand-detail-overlay.is-open { opacity: 1; visibility: visible; }
    .cand-detail-card {
        width: 100%;
        max-width: 640px;
        max-height: 90vh;
        overflow-y: auto;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.22);
        transform: translateY(20px) scale(0.98);
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .cand-detail-overlay.is-open .cand-detail-card {
        transform: translateY(0) scale(1);
    }
    .cand-detail-card__head {
        padding: 24px;
        background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-primary-mid) 100%);
        color: #fff;
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
    }
    .cand-detail-card__avatar {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: rgba(255,255,255,0.12);
        color: var(--c-accent);
        font-size: 18px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .cand-detail-card__name {
        font-size: 20px;
        font-weight: 800;
        line-height: 1.2;
    }
    .cand-detail-card__email {
        font-size: 13px;
        opacity: 0.85;
        margin-top: 4px;
        word-break: break-all;
    }
    .cand-detail-card__close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 34px;
        height: 34px;
        border: none;
        border-radius: 10px;
        background: rgba(255,255,255,0.15);
        color: #fff;
        font-size: 21px;
        cursor: pointer;
        line-height: 1;
    }
    .cand-detail-card__close:hover { background: rgba(255,255,255,0.28); }
    .cand-detail-card__body { padding: 22px 24px 24px; }
    .cand-detail-card__grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px 20px;
    }
    @media (max-width: 560px) { .cand-detail-card__grid { grid-template-columns: 1fr; } }
    .cand-detail-card__item label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--c-muted);
        margin-bottom: 4px;
    }
    .cand-detail-card__item span,
    .cand-detail-card__item a {
        font-size: 14px;
        color: var(--c-text);
        line-height: 1.45;
        word-break: break-word;
    }
    .cand-detail-card__item.span-2 { grid-column: 1 / -1; }
    .cand-detail-card__foot {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 0 24px 24px;
    }

    .cand-detail-card__item .cand-job-link {
        color: #fff;
    }
    .cand-detail-card__item .cand-job-link:hover {
        color: #fff;
        background: var(--c-primary-mid);
    }

    .cand-empty {
        text-align: center;
        padding: 56px 24px;
        color: var(--c-muted);
    }
    .cand-empty svg {
        width: 56px;
        height: 56px;
        opacity: 0.35;
        margin-bottom: 16px;
    }
    .cand-empty h3 {
        font-size: 18px;
        color: var(--c-primary);
        margin-bottom: 8px;
    }
    .cand-empty a { color: var(--c-accent-dim); font-weight: 700; }

    /* ── Pagination ── */
    .cand-pagination {
        margin-top: 20px;
        padding: 14px 18px;
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }
    .cand-pagination__info {
        font-size: 13px;
        color: var(--c-muted);
        line-height: 1.4;
    }
    .cand-pagination__info strong {
        color: var(--c-primary);
        font-weight: 700;
    }
    .cand-pagination .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 6px;
        flex-wrap: wrap;
    }
    .cand-pagination .pagination li a,
    .cand-pagination .pagination li span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0 12px;
        border: 1px solid var(--c-border);
        border-radius: 10px;
        color: var(--c-primary);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        background: #fff;
        transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.15s ease;
    }
    .cand-pagination .pagination li a:hover {
        background: rgba(241, 205, 134, 0.22);
        border-color: var(--c-accent-dim);
        transform: translateY(-1px);
    }
    .cand-pagination .pagination li.active span {
        background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-primary-mid) 100%);
        border-color: var(--c-primary);
        color: var(--c-accent);
        box-shadow: 0 4px 14px rgba(10, 45, 41, 0.18);
    }
    .cand-pagination .pagination li.disabled span {
        color: #c0c4c9;
        background: #f9fafb;
        cursor: not-allowed;
    }
    .cand-table-card.is-loading {
        opacity: 0.55;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }

    @media (max-width: 640px) {
        .cand-pagination {
            justify-content: center;
        }
        .cand-pagination__info {
            width: 100%;
            text-align: center;
        }
        .cand-pagination .pagination {
            justify-content: center;
            width: 100%;
        }
    }

    /* ── Modal ── */
    .cand-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 2000;
        background: rgba(10, 45, 41, 0.45);
        backdrop-filter: blur(6px);
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 40px 16px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .cand-modal-overlay.is-open {
        opacity: 1;
        visibility: visible;
    }
    .cand-modal {
        width: 100%;
        max-width: 720px;
        max-height: 90vh;
        overflow-y: auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2);
        transform: translateY(24px) scale(0.98);
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        color: #111827;
        color: var(--c-text);
    }
    .cand-modal-overlay.is-open .cand-modal {
        transform: translateY(0) scale(1);
    }
    .cand-modal__head {
        padding: 22px 24px;
        background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-primary-mid) 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    .cand-modal__head h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
    }
    .cand-modal__close {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 10px;
        background: rgba(255,255,255,0.15);
        color: #fff;
        font-size: 22px;
        cursor: pointer;
        line-height: 1;
        transition: background 0.2s ease;
    }
    .cand-modal__close:hover { background: rgba(255,255,255,0.28); }
    .cand-modal__body { padding: 24px; }
    .cand-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }
    @media (max-width: 600px) { .cand-form-grid { grid-template-columns: 1fr; } }
    .cand-form-grid .span-2 { grid-column: span 2; }
    @media (max-width: 600px) { .cand-form-grid .span-2 { grid-column: span 1; } }

    .cand-field label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: var(--c-primary);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .cand-field { min-width: 0; }
    .cand-field input,
    .cand-field select,
    .cand-field textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border: 1px solid var(--c-border);
        border-radius: 8px;
        font-family: inherit;
        font-size: 13px;
        color: #111827;
        color: var(--c-text);
        background: #fff;
        box-sizing: border-box;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .cand-field input:focus,
    .cand-field select:focus,
    .cand-field textarea:focus {
        outline: none;
        border-color: var(--c-accent-dim);
        box-shadow: 0 0 0 3px rgba(241, 205, 134, 0.25);
    }
    .cand-modal__foot {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 8px;
    }
    .cand-btn {
        padding: 10px 20px;
        border-radius: 10px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .cand-btn--primary {
        background: var(--c-primary);
        color: var(--c-accent);
    }
    .cand-btn--ghost {
        background: #f3f4f6;
        color: var(--c-text);
    }
    .cand-btn:hover { transform: translateY(-1px); }

    @media (prefers-reduced-motion: reduce) {
        .cand-enter, .cand-hl, .cand-table tbody tr, .cand-modal, .cand-modal-overlay {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
