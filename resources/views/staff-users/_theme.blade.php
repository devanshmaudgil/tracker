<style>
    .users-page {
        opacity: 0;
        animation: usersPageIn 0.35s ease forwards;
    }
    .users-enter {
        opacity: 0;
        transform: translateY(14px);
        animation: usersSlideUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }
    .users-enter-1 { animation-delay: 0.1s; }
    .users-enter-2 { animation-delay: 0.22s; }
    @keyframes usersPageIn { to { opacity: 1; } }
    @keyframes usersSlideUp { to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) {
        .users-page, .users-enter { animation: none; opacity: 1; transform: none; }
    }

    :root {
        --u-teal: #0a2d29;
        --u-gold: #f1cd86;
        --u-border: #e5e7eb;
        --u-muted: #6b7280;
        --u-surface: #fff;
        --u-danger: #dc2626;
        --u-success: #059669;
    }

    .users-toolbar {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        padding: 16px 20px;
        background: var(--u-surface);
        border: 1px solid var(--u-border);
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(10, 45, 41, 0.06);
        margin-bottom: 20px;
    }
    .users-toolbar__title {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 180px;
    }
    .users-toolbar__title span:first-child {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--u-muted);
    }
    .users-toolbar__title span:last-child {
        font-size: 15px;
        font-weight: 700;
        color: var(--u-teal);
    }
    .users-toolbar__divider {
        width: 1px;
        height: 36px;
        background: var(--u-border);
        flex-shrink: 0;
    }
    .users-stat-pill {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 4px 0;
    }
    .users-stat-pill__label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--u-muted);
    }
    .users-stat-pill__value {
        font-size: 22px;
        font-weight: 800;
        color: var(--u-teal);
        line-height: 1;
    }
    .users-toolbar__spacer { flex: 1; }

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
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
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
    }
    .toolbar-btn__icon svg { width: 15px; height: 15px; }
    .toolbar-btn--ghost {
        background: linear-gradient(135deg, #f6faf9 0%, #eef4f3 100%);
        color: var(--u-teal);
        box-shadow: inset 0 0 0 1px rgba(10, 45, 41, 0.08);
    }
    .toolbar-btn--ghost .toolbar-btn__icon { background: rgba(10, 45, 41, 0.06); }
    .toolbar-btn--ghost:hover {
        transform: translateY(-1px);
        box-shadow: inset 0 0 0 1px rgba(241, 205, 134, 0.45), 0 4px 12px rgba(10, 45, 41, 0.08);
    }
    .toolbar-btn--primary {
        background: linear-gradient(135deg, var(--u-teal) 0%, #0f3d38 100%);
        color: #fff;
        box-shadow: 0 4px 14px rgba(10, 45, 41, 0.25);
    }
    .toolbar-btn--primary .toolbar-btn__icon {
        background: linear-gradient(135deg, var(--u-gold) 0%, #e8c078 100%);
        color: var(--u-teal);
    }
    .toolbar-btn--primary:hover { transform: translateY(-1px); }

    .users-card {
        background: var(--u-surface);
        border: 1px solid var(--u-border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(10, 45, 41, 0.06);
    }
    .users-card__header {
        padding: 14px 20px;
        background: linear-gradient(135deg, var(--u-teal) 0%, #0d3d38 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .users-card__header h2 {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .users-card__header h2 svg { width: 16px; height: 16px; opacity: 0.9; }

    .users-table-wrap { overflow-x: auto; }
    .users-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .users-table th {
        padding: 12px 16px;
        text-align: center;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--u-muted);
        background: #f9fafb;
        border-bottom: 1px solid var(--u-border);
        white-space: nowrap;
    }
    .users-table td {
        padding: 12px 16px;
        text-align: center;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
        vertical-align: middle;
    }
    .users-table tbody tr:hover { background: #fafcfb; }
    .users-table tbody tr:last-child td { border-bottom: none; }

    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--u-gold);
        display: inline-block;
    }
    .user-avatar--placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e5e7eb;
        color: var(--u-muted);
        font-size: 13px;
        font-weight: 700;
    }
    .username-cell {
        font-weight: 700;
        color: var(--u-teal);
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }
    .status-pill--active { background: #ecfdf5; color: var(--u-success); }
    .status-pill--inactive { background: #fef2f2; color: var(--u-danger); }
    .status-pill__dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .row-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .row-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid var(--u-border);
        background: #fff;
        color: var(--u-teal);
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s;
    }
    .row-btn svg { width: 15px; height: 15px; }
    .row-btn:hover { background: #f0f7f6; border-color: var(--u-gold); }
    .row-btn--danger { color: var(--u-danger); }
    .row-btn--danger:hover { background: #fef2f2; border-color: #fecaca; }

    .users-empty {
        padding: 48px 24px;
        text-align: center;
        color: var(--u-muted);
    }
    .users-empty svg { width: 48px; height: 48px; opacity: 0.35; margin-bottom: 12px; }
    .users-empty p { margin: 0 0 16px; font-size: 14px; }

    /* Form layout */
    .users-form-page { max-width: 820px; margin: 0 auto; }
    .form-section {
        padding: 20px;
        border-bottom: 1px solid #f3f4f6;
    }
    .form-section:last-child { border-bottom: none; }
    .form-section__title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--u-teal);
        margin: 0 0 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-section__title svg { width: 16px; height: 16px; opacity: 0.8; }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    @media (max-width: 640px) {
        .form-grid { grid-template-columns: 1fr; }
    }
    .form-field { display: flex; flex-direction: column; gap: 6px; }
    .form-field--full { grid-column: 1 / -1; }
    .form-field label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
    }
    .form-field label .req { color: var(--u-danger); }
    .form-field input[type="text"],
    .form-field input[type="password"],
    .form-field input[type="date"],
    .form-field input[type="file"],
    .form-field textarea {
        padding: 10px 12px;
        border: 1px solid var(--u-border);
        border-radius: 8px;
        font-size: 13px;
        font-family: inherit;
        transition: border-color 0.15s, box-shadow 0.15s;
        background: #fff;
    }
    .form-field input:focus,
    .form-field textarea:focus {
        outline: none;
        border-color: var(--u-teal);
        box-shadow: 0 0 0 3px rgba(10, 45, 41, 0.1);
    }
    .form-field textarea { min-height: 90px; resize: vertical; }
    .form-hint {
        font-size: 11.5px;
        color: var(--u-muted);
        margin-top: 2px;
    }
    .form-error {
        font-size: 12px;
        color: var(--u-danger);
        margin-top: 2px;
    }
    .form-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        padding: 20px;
        background: #f9fafb;
        border-top: 1px solid var(--u-border);
    }
    .btn-form {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 20px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
        transition: all 0.15s;
    }
    .btn-form svg { width: 15px; height: 15px; }
    .btn-form--primary {
        background: linear-gradient(135deg, var(--u-teal) 0%, #0f3d38 100%);
        color: #fff;
    }
    .btn-form--primary:hover { box-shadow: 0 4px 12px rgba(10, 45, 41, 0.2); }
    .btn-form--ghost {
        background: #fff;
        color: var(--u-teal);
        border: 1px solid var(--u-border);
    }
    .btn-form--ghost:hover { background: #f3f4f6; }

    /* Show page */
    .profile-hero {
        display: grid;
        grid-template-columns: 160px 1fr;
        gap: 24px;
        padding: 24px;
        align-items: start;
    }
    @media (max-width: 560px) {
        .profile-hero { grid-template-columns: 1fr; justify-items: center; text-align: center; }
    }
    .profile-photo-lg {
        width: 140px;
        height: 140px;
        border-radius: 16px;
        object-fit: cover;
        border: 3px solid var(--u-gold);
        box-shadow: 0 4px 16px rgba(10, 45, 41, 0.12);
    }
    .profile-photo-lg--placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e5e7eb;
        color: var(--u-muted);
        font-size: 14px;
        font-weight: 600;
    }
    .profile-name {
        font-size: 22px;
        font-weight: 800;
        color: var(--u-teal);
        margin: 0 0 6px;
    }
    .profile-meta {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 14px;
        padding: 0 24px 24px;
    }
    .detail-item { display: flex; flex-direction: column; gap: 3px; }
    .detail-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--u-muted);
    }
    .detail-value { font-size: 14px; font-weight: 600; color: #111827; }
    .detail-value.muted { font-weight: 400; color: var(--u-muted); }

    /* Toast */
    .users-toast {
        position: fixed;
        top: 80px;
        right: 24px;
        z-index: 9999;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 18px;
        background: #fff;
        border: 1px solid var(--u-border);
        border-left: 4px solid var(--u-success);
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        max-width: 380px;
        transform: translateX(calc(100% + 40px));
        opacity: 0;
        transition: transform 0.35s cubic-bezier(0.22,1,0.36,1), opacity 0.35s ease;
    }
    .users-toast.is-visible { transform: translateX(0); opacity: 1; }
    .users-toast.is-hiding { transform: translateX(calc(100% + 40px)); opacity: 0; }
    .users-toast__icon { color: var(--u-success); flex-shrink: 0; }
    .users-toast__icon svg { width: 20px; height: 20px; }
    .users-toast__body { flex: 1; font-size: 13px; color: #374151; line-height: 1.45; }
    .users-toast__close {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--u-muted);
        padding: 0;
        line-height: 1;
        font-size: 18px;
    }

    /* Password policy alert */
    .pwd-policy-alert {
        margin: 0 0 16px;
        padding: 14px 16px;
        border-radius: 10px;
        border: 1px solid #fde68a;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }
    .pwd-policy-alert__icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(217, 119, 6, 0.12);
        color: #b45309;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .pwd-policy-alert__icon svg { width: 18px; height: 18px; }
    .pwd-policy-alert__title {
        margin: 0 0 4px;
        font-size: 13px;
        font-weight: 800;
        color: #92400e;
    }
    .pwd-policy-alert__text {
        margin: 0;
        font-size: 12.5px;
        line-height: 1.5;
        color: #78350f;
    }

    /* Password strength meter */
    .pwd-strength {
        margin-top: 10px;
        padding: 12px 14px;
        border: 1px solid #e8ecef;
        border-radius: 10px;
        background: #fafbfc;
    }
    .pwd-strength__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .pwd-strength__label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--u-muted);
    }
    .pwd-strength__value {
        font-size: 12px;
        font-weight: 800;
        color: #6b7280;
    }
    .pwd-strength__value[data-level="weak"] { color: #dc2626; }
    .pwd-strength__value[data-level="fair"] { color: #d97706; }
    .pwd-strength__value[data-level="good"] { color: #2563eb; }
    .pwd-strength__value[data-level="strong"] { color: #059669; }

    .pwd-strength__bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 5px;
        margin-bottom: 10px;
    }
    .pwd-strength__segment {
        height: 5px;
        border-radius: 999px;
        background: #e5e7eb;
        transition: background 0.2s ease, transform 0.2s ease;
    }
    .pwd-strength__bar[data-level="weak"] .pwd-strength__segment.is-active { background: #ef4444; }
    .pwd-strength__bar[data-level="fair"] .pwd-strength__segment.is-active { background: #f59e0b; }
    .pwd-strength__bar[data-level="good"] .pwd-strength__segment.is-active { background: #3b82f6; }
    .pwd-strength__bar[data-level="strong"] .pwd-strength__segment.is-active { background: #10b981; }

    .pwd-strength__checks {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px 12px;
    }
    @media (max-width: 520px) {
        .pwd-strength__checks { grid-template-columns: 1fr; }
    }
    .pwd-strength__checks li {
        font-size: 11px;
        color: #9ca3af;
        padding-left: 18px;
        position: relative;
        line-height: 1.4;
        transition: color 0.15s ease;
    }
    .pwd-strength__checks li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 3px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 1.5px solid #d1d5db;
        background: #fff;
        transition: all 0.15s ease;
    }
    .pwd-strength__checks li.is-met {
        color: #065f46;
        font-weight: 600;
    }
    .pwd-strength__checks li.is-met::before {
        border-color: #10b981;
        background: #10b981;
        box-shadow: inset 0 0 0 2px #fff;
    }
</style>
