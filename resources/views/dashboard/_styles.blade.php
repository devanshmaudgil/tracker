<style>
    :root {
        --d2-teal: #0a2d29;
        --d2-teal-2: #0f3d38;
        --d2-teal-3: #133f39;
        --d2-teal-glow: #1f6f60;
        --d2-gold: #f1cd86;
        --d2-gold-d: #e8c078;
        --d2-gold-deep: #a9711b;
        --d2-gold-ink: #b8860b;
        --d2-slate-teal: #5f7d75;
        --d2-border: #e6e9ec;
        --d2-muted: #6b7280;
        --d2-surface: #ffffff;
        --d2-bg-soft: #f6faf9;
    }

    .dash2-page { opacity: 0; animation: d2PageIn .35s ease forwards; }
    .dash2-enter { opacity: 0; transform: translateY(16px); animation: d2Up .6s cubic-bezier(.22,1,.36,1) forwards; }
    .dash2-enter-1 { animation-delay: .05s; }
    .dash2-enter-2 { animation-delay: .15s; }
    .dash2-enter-3 { animation-delay: .25s; }
    .dash2-enter-4 { animation-delay: .35s; }
    @keyframes d2PageIn { to { opacity: 1; } }
    @keyframes d2Up { to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) {
        .dash2-page, .dash2-enter { animation: none; opacity: 1; transform: none; }
    }

    /* ── Toolbar ── */
    .dash2-toolbar-wrap { margin-bottom: 22px; }
    .dash2-toolbar {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 13px 16px;
        background: var(--d2-surface);
        border: 1px solid var(--d2-border);
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(10,45,41,.06);
    }
    .dash2-toolbar__search {
        position: relative;
        display: flex;
        align-items: center;
        flex: 1;
        max-width: 420px;
        min-width: 180px;
    }
    .dash2-toolbar__search svg {
        position: absolute; left: 12px; width: 16px; height: 16px; color: var(--d2-muted); pointer-events: none;
    }
    .dash2-toolbar__search input {
        width: 100%;
        padding: 10px 12px 10px 36px;
        border: 1px solid var(--d2-border);
        border-radius: 10px;
        font-size: 13.5px;
        font-family: inherit;
        color: var(--d2-teal);
        background: var(--d2-bg-soft);
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .dash2-toolbar__search input:focus {
        outline: none;
        background: #fff;
        border-color: var(--d2-teal);
        box-shadow: 0 0 0 3px rgba(10,45,41,.08);
    }
    .dash2-toolbar__actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

    .dash2-filter-toggle { position: relative; }
    .dash2-filter-toggle__chevron { transition: transform .2s ease; }
    .dash2-filter-toggle.is-active .dash2-filter-toggle__chevron { transform: rotate(180deg); }
    .dash2-filter-toggle.is-active { background: var(--d2-bg-soft); border-color: var(--d2-gold); }
    .dash2-filter-count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 18px; height: 18px; padding: 0 5px;
        border-radius: 999px; background: var(--d2-teal); color: #fff;
        font-size: 10.5px; font-weight: 800;
    }

    .dash2-filter-panel {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 14px;
        margin-top: 10px;
        padding: 18px 20px;
        background: var(--d2-surface);
        border: 1px solid var(--d2-border);
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(10,45,41,.06);
        animation: d2SlideDown .22s ease;
    }
    .dash2-filter-panel[hidden] { display: none; }
    @keyframes d2SlideDown { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }

    .dash2-filter { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
    .dash2-filter label {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: var(--d2-muted);
    }
    .dash2-filter select {
        width: 100%;
        padding: 9px 11px;
        border: 1px solid var(--d2-border);
        border-radius: 9px;
        font-size: 13px;
        font-family: inherit;
        color: var(--d2-teal);
        background: #fff;
        transition: border-color .15s, box-shadow .15s;
        cursor: pointer;
    }
    .dash2-filter select:focus {
        outline: none;
        border-color: var(--d2-teal);
        box-shadow: 0 0 0 3px rgba(10,45,41,.1);
    }
    .dash2-filter-panel__actions {
        grid-column: 1 / -1;
        display: flex; gap: 8px; flex-wrap: wrap;
        padding-top: 6px; margin-top: 4px;
        border-top: 1px solid #f1f3f4;
    }

    .dash2-chips {
        display: flex; flex-wrap: wrap; gap: 8px;
        margin-top: 10px;
    }
    .dash2-chips[hidden] { display: none; }
    .dash2-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 6px 5px 12px;
        border-radius: 999px;
        background: var(--d2-bg-soft);
        border: 1px solid rgba(10,45,41,.12);
        font-size: 12px; color: var(--d2-muted);
    }
    .dash2-chip strong { color: var(--d2-teal); font-weight: 700; }
    .dash2-chip__remove {
        display: inline-flex; align-items: center; justify-content: center;
        width: 18px; height: 18px; border-radius: 50%;
        border: none; background: rgba(10,45,41,.08); color: var(--d2-teal);
        cursor: pointer; font-size: 13px; line-height: 1; padding: 0;
        transition: background .15s;
    }
    .dash2-chip__remove:hover { background: var(--d2-teal); color: #fff; }

    .dash2-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        padding: 9px 16px; border-radius: 9px; font-size: 13px; font-weight: 700;
        border: none; cursor: pointer; font-family: inherit; transition: all .15s; white-space: nowrap;
    }
    .dash2-btn svg { width: 15px; height: 15px; }
    .dash2-btn--sm { padding: 7px 13px; font-size: 12.5px; }
    .dash2-btn--primary { background: linear-gradient(135deg, var(--d2-teal), var(--d2-teal-2)); color: #fff; box-shadow: 0 4px 12px rgba(10,45,41,.22); }
    .dash2-btn--primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(10,45,41,.28); }
    .dash2-btn--ghost { background: #fff; color: var(--d2-teal); border: 1px solid var(--d2-border); }
    .dash2-btn--ghost:hover { background: var(--d2-bg-soft); border-color: var(--d2-gold); }
    .dash2-btn--accent {
        background: linear-gradient(135deg, var(--d2-gold) 0%, var(--d2-gold-d) 100%);
        color: var(--d2-teal);
        box-shadow: 0 4px 12px rgba(241,205,134,.35);
        text-decoration: none;
    }
    .dash2-btn--accent:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(241,205,134,.45); }

    .dash2-loading {
        width: 16px; height: 16px; border-radius: 50%;
        border: 2px solid var(--d2-gold); border-top-color: transparent;
        opacity: 0; transition: opacity .2s; flex-shrink: 0;
    }
    .dash2-loading.is-active { opacity: 1; animation: d2Spin .7s linear infinite; }
    @keyframes d2Spin { to { transform: rotate(360deg); } }

    /* ── KPI cards ── */
    .dash2-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 16px;
        margin-bottom: 26px;
    }
    .dash2-kpi {
        position: relative;
        background: linear-gradient(155deg, #ffffff 0%, var(--kpi-wash, #ffffff) 100%);
        border: 1px solid var(--d2-border);
        border-radius: 16px;
        padding: 18px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(10,45,41,.06);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .dash2-kpi:hover { transform: translateY(-3px); box-shadow: 0 10px 26px rgba(10,45,41,.12); }
    .dash2-kpi--clickable { cursor: pointer; }
    .dash2-kpi--clickable:focus-visible { outline: 2px solid var(--d2-gold); outline-offset: 2px; }
    .dash2-kpi--clickable::after {
        content: '';
        position: absolute; right: 14px; top: 14px;
        width: 8px; height: 8px; border-radius: 50%;
        background: var(--kpi-color, var(--d2-teal));
        opacity: 0; transform: scale(.5);
        transition: opacity .2s, transform .2s;
    }
    .dash2-kpi--clickable:hover::after { opacity: .4; transform: scale(1); }
    .dash2-kpi::before {
        content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
        background: linear-gradient(180deg, var(--kpi-color, var(--d2-teal)), var(--kpi-color-2, var(--kpi-color, var(--d2-teal))));
    }
    .dash2-kpi__top { display: flex; align-items: center; gap: 9px; margin-bottom: 12px; }
    .dash2-kpi__icon {
        display: flex; align-items: center; justify-content: center;
        width: 34px; height: 34px; border-radius: 10px;
        background: var(--kpi-soft, rgba(10,45,41,.08));
        color: var(--kpi-color, var(--d2-teal));
        flex-shrink: 0;
    }
    .dash2-kpi__icon svg { width: 18px; height: 18px; }
    .dash2-kpi__label {
        font-size: 11.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: var(--d2-muted);
    }
    .dash2-kpi__value { font-size: 32px; font-weight: 800; color: var(--d2-teal); line-height: 1; letter-spacing: -.02em; }
    .dash2-kpi__foot { margin-top: 8px; font-size: 12px; color: var(--d2-muted); font-weight: 500; }
    .dash2-kpi__foot span { font-weight: 700; color: var(--d2-teal); }

    .dash2-kpi[data-accent="teal"]     { --kpi-color: var(--d2-teal); --kpi-color-2: var(--d2-teal-glow); --kpi-soft: rgba(10,45,41,.08); --kpi-wash: rgba(10,45,41,.05); }
    .dash2-kpi[data-accent="tealglow"] { --kpi-color: var(--d2-teal-glow); --kpi-color-2: var(--d2-teal); --kpi-soft: rgba(31,111,96,.12); --kpi-wash: rgba(31,111,96,.07); }
    .dash2-kpi[data-accent="gold"]     { --kpi-color: var(--d2-gold-ink); --kpi-color-2: var(--d2-gold-d); --kpi-soft: rgba(241,205,134,.3); --kpi-wash: rgba(241,205,134,.18); }
    .dash2-kpi[data-accent="goldd"]    { --kpi-color: var(--d2-gold-deep); --kpi-color-2: var(--d2-gold-ink); --kpi-soft: rgba(200,146,47,.22); --kpi-wash: rgba(200,146,47,.14); }
    .dash2-kpi[data-accent="slate"]    { --kpi-color: var(--d2-slate-teal); --kpi-color-2: var(--d2-teal); --kpi-soft: rgba(95,125,117,.14); --kpi-wash: rgba(95,125,117,.08); }

    /* ── Hero KPI cards (Total + Attention): full teal/gold treatment ── */
    .dash2-kpi--hero {
        border: none;
        color: #fff;
        box-shadow: 0 10px 28px rgba(10,45,41,.18);
    }
    .dash2-kpi--hero::before { display: none; }
    .dash2-kpi--hero::after { background: rgba(255,255,255,.9); }
    .dash2-kpi--hero .dash2-kpi__label { color: rgba(255,255,255,.72); }
    .dash2-kpi--hero .dash2-kpi__value { color: #fff; }
    .dash2-kpi--hero .dash2-kpi__foot { color: rgba(255,255,255,.75); }
    .dash2-kpi--hero .dash2-kpi__foot span { color: var(--d2-gold); }
    .dash2-kpi--hero .dash2-kpi__icon { background: rgba(255,255,255,.16); color: var(--d2-gold); }
    .dash2-kpi--hero[data-accent="teal"] {
        background: radial-gradient(120% 140% at 100% -10%, rgba(241,205,134,.16), transparent 55%), linear-gradient(135deg, var(--d2-teal) 0%, var(--d2-teal-2) 55%, var(--d2-teal-3) 100%);
    }
    .dash2-kpi--hero[data-accent="gold"] {
        background: linear-gradient(135deg, #fbeecb 0%, #f3d68a 50%, #e8c078 100%);
        border: 1px solid rgba(201,147,47,.4);
        box-shadow: 0 8px 22px rgba(241,205,134,.4);
    }
    .dash2-kpi--hero[data-accent="gold"] .dash2-kpi__label { color: var(--d2-teal); opacity: .75; }
    .dash2-kpi--hero[data-accent="gold"] .dash2-kpi__value { color: var(--d2-teal); }
    .dash2-kpi--hero[data-accent="gold"] .dash2-kpi__foot { color: var(--d2-teal); opacity: .8; }
    .dash2-kpi--hero[data-accent="gold"] .dash2-kpi__foot span { color: var(--d2-teal); font-weight: 800; }
    .dash2-kpi--hero[data-accent="gold"] .dash2-kpi__icon { background: rgba(255,255,255,.6); color: var(--d2-gold-deep); }
    .dash2-kpi--hero[data-accent="gold"] .dash2-kpi--clickable::after,
    .dash2-kpi--hero[data-accent="gold"]::after { background: var(--d2-teal); }

    /* ── Section headers ── */
    .dash2-section { margin-bottom: 28px; }
    .dash2-section:last-child { margin-bottom: 0; }
    .dash2-section__head {
        display: flex; align-items: center; gap: 10px;
        margin-bottom: 14px;
    }
    .dash2-section__icon {
        display: flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; border-radius: 9px;
        background: rgba(10,45,41,.07); color: var(--d2-teal);
        flex-shrink: 0;
    }
    .dash2-section__icon svg { width: 15px; height: 15px; }
    .dash2-section__title {
        font-size: 12.5px; font-weight: 800; text-transform: uppercase;
        letter-spacing: .07em; color: var(--d2-teal); margin: 0;
        white-space: nowrap;
    }
    .dash2-section__head::after { content: ''; flex: 1; height: 1px; background: var(--d2-border); }

    /* ── Charts grid ── */
    .dash2-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }
    .dash2-grid--3col { grid-template-columns: repeat(3, 1fr); }
    .dash2-card {
        background: linear-gradient(160deg, #ffffff 0%, var(--card-wash, #ffffff) 100%);
        border: 1px solid var(--d2-border);
        border-top: 3px solid var(--card-color, var(--d2-teal));
        border-radius: 16px;
        padding: 18px 20px 20px;
        box-shadow: 0 1px 3px rgba(10,45,41,.06);
        transition: box-shadow .2s ease, transform .2s ease;
    }
    .dash2-card:hover { box-shadow: 0 10px 26px rgba(10,45,41,.09); transform: translateY(-2px); }
    .dash2-card--wide { grid-column: span 2; }
    .dash2-card__head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
    .dash2-card__icon {
        display: flex; align-items: center; justify-content: center;
        width: 34px; height: 34px; border-radius: 10px;
        background: var(--accent-soft, rgba(10,45,41,.08));
        color: var(--accent-color, var(--d2-teal));
        flex-shrink: 0;
    }
    .dash2-card__icon svg { width: 17px; height: 17px; }
    .dash2-card__head h3 { margin: 0 0 2px; font-size: 14.5px; font-weight: 700; color: var(--d2-teal); }
    .dash2-card__sub { font-size: 11.5px; color: var(--d2-muted); }
    .dash2-chart { position: relative; height: 250px; }
    .dash2-chart--tall { height: 290px; }

    .dash2-card[data-accent="teal"]     { --card-color: var(--d2-teal); --card-wash: rgba(10,45,41,.045); }
    .dash2-card[data-accent="tealglow"] { --card-color: var(--d2-teal-glow); --card-wash: rgba(31,111,96,.06); }
    .dash2-card[data-accent="gold"]     { --card-color: var(--d2-gold-d); --card-wash: rgba(241,205,134,.14); }
    .dash2-card[data-accent="goldd"]    { --card-color: var(--d2-gold-deep); --card-wash: rgba(200,146,47,.12); }
    .dash2-card[data-accent="slate"]    { --card-color: var(--d2-slate-teal); --card-wash: rgba(95,125,117,.07); }

    .dash2-card__icon[data-accent="teal"]     { --accent-color: var(--d2-teal); --accent-soft: rgba(10,45,41,.08); }
    .dash2-card__icon[data-accent="tealglow"] { --accent-color: var(--d2-teal-glow); --accent-soft: rgba(31,111,96,.12); }
    .dash2-card__icon[data-accent="gold"]     { --accent-color: var(--d2-gold-ink); --accent-soft: rgba(241,205,134,.3); }
    .dash2-card__icon[data-accent="goldd"]    { --accent-color: var(--d2-gold-deep); --accent-soft: rgba(200,146,47,.22); }
    .dash2-card__icon[data-accent="slate"]    { --accent-color: var(--d2-slate-teal); --accent-soft: rgba(95,125,117,.14); }

    @media (max-width: 1100px) {
        .dash2-grid--3col { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 900px) {
        .dash2-grid { grid-template-columns: 1fr; }
        .dash2-grid--3col { grid-template-columns: 1fr; }
        .dash2-card--wide { grid-column: span 1; }
        .dash2-toolbar { flex-wrap: wrap; }
        .dash2-toolbar__search { max-width: none; width: 100%; order: 1; }
        .dash2-toolbar__actions { order: 2; width: 100%; justify-content: space-between; }
    }

    /* ── Recent table ── */
    .dash2-table-wrap { overflow-x: auto; }
    .dash2-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: center; }
    .dash2-table th {
        padding: 11px 14px; text-align: center;
        font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
        color: var(--d2-muted); background: var(--d2-bg-soft);
        border-bottom: 1px solid var(--d2-border); white-space: nowrap;
    }
    .dash2-table td { padding: 11px 14px; border-bottom: 1px solid #f1f3f4; color: #374151; vertical-align: middle; text-align: center; }
    .dash2-table tbody tr { transition: background .12s; }
    .dash2-table tbody tr:hover { background: var(--d2-bg-soft); }
    .dash2-table tbody tr:last-child td { border-bottom: none; }
    .dash2-pos-link { color: var(--d2-teal); font-weight: 700; text-decoration: none; }
    .dash2-pos-link:hover { text-decoration: underline; }
    .dash2-table .dash2-badge,
    .dash2-table .dash2-prio { justify-content: center; }

    .dash2-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap;
    }
    .dash2-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .dash2-badge--open { background: rgba(37,99,235,.1); color: #2563eb; }
    .dash2-badge--in_progress { background: rgba(217,119,6,.12); color: #b45309; }
    .dash2-badge--placed { background: rgba(5,150,105,.12); color: #059669; }
    .dash2-badge--rejected { background: rgba(220,38,38,.1); color: #dc2626; }

    .dash2-prio { font-weight: 600; }
    .dash2-prio--Urgent { color: #dc2626; }
    .dash2-prio--High { color: #d97706; }
    .dash2-prio--Medium { color: #2563eb; }
    .dash2-prio--Low { color: var(--d2-muted); }

    .dash2-empty { padding: 36px 16px; text-align: center; color: var(--d2-muted); font-size: 14px; }

    /* ── KPI Detail Modal ── */
    .dash2-modal {
        position: fixed; inset: 0; z-index: 10000;
        display: flex; align-items: center; justify-content: center;
        padding: 24px;
    }
    .dash2-modal[hidden] { display: none; }
    .dash2-modal__backdrop {
        position: absolute; inset: 0;
        background: rgba(10,45,41,.45);
        backdrop-filter: blur(4px);
        animation: d2FadeIn .25s ease;
    }
    .dash2-modal__panel {
        position: relative; z-index: 1;
        width: min(920px, 100%);
        max-height: min(85vh, 720px);
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 24px 64px rgba(10,45,41,.28);
        display: flex; flex-direction: column;
        overflow: hidden;
        animation: d2ModalIn .35s cubic-bezier(.22,1,.36,1);
    }
    @keyframes d2FadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes d2ModalIn { from { opacity: 0; transform: translateY(20px) scale(.97); } to { opacity: 1; transform: none; } }
    .dash2-modal__head {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;
        padding: 20px 24px;
        background: linear-gradient(135deg, var(--d2-teal) 0%, var(--d2-teal-2) 100%);
        color: #fff;
    }
    .dash2-modal__head h2 { margin: 0 0 4px; font-size: 18px; font-weight: 700; color: #fff; }
    .dash2-modal__head p { margin: 0; font-size: 13px; opacity: .85; }
    .dash2-modal__close {
        background: rgba(255,255,255,.12); border: none; color: #fff;
        width: 36px; height: 36px; border-radius: 10px; font-size: 22px;
        cursor: pointer; line-height: 1; flex-shrink: 0;
        transition: background .15s;
    }
    .dash2-modal__close:hover { background: rgba(255,255,255,.22); }
    .dash2-modal__body { padding: 0; overflow-y: auto; flex: 1; }
    .dash2-modal__loading { padding: 48px; text-align: center; color: var(--d2-muted); }

    .dash2-detail-list { list-style: none; margin: 0; padding: 0; }
    .dash2-detail-item {
        padding: 16px 24px;
        border-bottom: 1px solid #f1f3f4;
        transition: background .12s;
    }
    .dash2-detail-item:hover { background: var(--d2-bg-soft); }
    .dash2-detail-item:last-child { border-bottom: none; }
    .dash2-detail-item__top {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap;
        margin-bottom: 8px;
    }
    .dash2-detail-item__title { font-size: 14px; font-weight: 700; color: var(--d2-teal); text-decoration: none; }
    .dash2-detail-item__title:hover { text-decoration: underline; }
    .dash2-detail-item__meta {
        display: flex; flex-wrap: wrap; gap: 8px 16px;
        font-size: 12px; color: var(--d2-muted); margin-bottom: 10px;
    }
    .dash2-detail-item__meta span { display: inline-flex; align-items: center; gap: 4px; }
    .dash2-concerns { display: flex; flex-direction: column; gap: 6px; }
    .dash2-concern {
        display: flex; align-items: flex-start; gap: 8px;
        padding: 8px 12px; border-radius: 8px; font-size: 12.5px; line-height: 1.45;
        background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;
    }
    .dash2-concern--info { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }
    .dash2-concern--warn { background: #fffbeb; color: #92400e; border-color: #fde68a; }
    .dash2-concern svg { width: 14px; height: 14px; flex-shrink: 0; margin-top: 1px; }
    .dash2-modal__empty { padding: 48px 24px; text-align: center; color: var(--d2-muted); }
</style>
