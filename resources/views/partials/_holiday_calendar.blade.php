{{-- Holiday Calendar Modal (topbar) --}}
<div id="holidayCalOverlay" class="hcal-overlay" aria-hidden="true">
    <div class="hcal-box" role="dialog" aria-modal="true" aria-label="Holiday calendar" onclick="event.stopPropagation()">
        <div class="hcal-head">
            <div class="hcal-head__left">
                <span class="hcal-head__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </span>
                <div>
                    <div class="hcal-head__title">Holiday Calendar</div>
                    <div class="hcal-head__sub" id="hcalHeadSub">Public holidays &amp; observances</div>
                </div>
            </div>
            <button type="button" class="hcal-close" onclick="closeHolidayCalendar()" aria-label="Close calendar">×</button>
        </div>

        <div class="hcal-countries" role="tablist" aria-label="Choose country">
            <button type="button" class="hcal-country" data-country="IN" role="tab">
                <span class="hcal-country__flag">🇮🇳</span> India
            </button>
            <button type="button" class="hcal-country" data-country="US" role="tab">
                <span class="hcal-country__flag">🇺🇸</span> USA
            </button>
            <button type="button" class="hcal-country" data-country="CA" role="tab">
                <span class="hcal-country__flag">🇨🇦</span> Canada
            </button>
            <span class="hcal-country-ink" id="hcalCountryInk"></span>
        </div>

        <div class="hcal-body">
            <div class="hcal-grid-side">
                <div class="hcal-nav">
                    <button type="button" class="hcal-nav-btn" id="hcalPrev" aria-label="Previous month">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <div class="hcal-nav-label">
                        <span class="hcal-nav-month" id="hcalMonthLabel">—</span>
                        <span class="hcal-nav-year" id="hcalYearLabel">—</span>
                    </div>
                    <button type="button" class="hcal-nav-btn" id="hcalNext" aria-label="Next month">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                    <button type="button" class="hcal-today-btn" id="hcalToday">Today</button>
                </div>

                <div class="hcal-weekdays">
                    <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                </div>
                <div class="hcal-days" id="hcalDays"></div>

                <div class="hcal-legend">
                    <span class="hcal-legend__item"><span class="hcal-dot hcal-dot--public"></span> Public holiday</span>
                    <span class="hcal-legend__item"><span class="hcal-dot hcal-dot--obs"></span> Observance</span>
                </div>

                <div class="hcal-day-action" id="hcalDayAction" hidden>
                    <div class="hcal-day-action__info">
                        <span class="hcal-day-action__date" id="hcalSelDate">—</span>
                        <span class="hcal-day-action__name" id="hcalSelName">—</span>
                    </div>
                    <button type="button" class="hcal-generate-btn" id="hcalGenerateBtn" title="Generate LinkedIn poster in ChatGPT">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/><path d="M5 17l.75 2.25L8 20l-2.25.75L5 23l-.75-2.25L2 20l2.25-.75L5 17z"/><path d="M19 13l.75 2.25L22 16l-2.25.75L19 19l-.75-2.25L16 16l2.25-.75L19 13z"/></svg>
                        Generate Poster
                    </button>
                </div>
            </div>

            <div class="hcal-list-side">
                <div class="hcal-list-title" id="hcalListTitle">This month</div>
                <div class="hcal-list" id="hcalList">
                    <div class="hcal-list-empty">Loading…</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="hcal-toast" id="hcalToast" role="status" aria-live="polite"></div>

<style>
    .hcal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(6, 26, 23, 0.55);
        backdrop-filter: blur(4px);
        z-index: 9500;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        visibility: hidden;
        transition: opacity .28s ease, visibility .28s;
    }
    .hcal-overlay.open {
        opacity: 1;
        visibility: visible;
    }
    .hcal-box {
        width: 100%;
        max-width: 820px;
        max-height: 92vh;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 24px 80px rgba(0,0,0,.35);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transform: translateY(16px) scale(.97);
        opacity: 0;
        transition: transform .32s cubic-bezier(.22,1,.36,1), opacity .28s ease;
    }
    .hcal-overlay.open .hcal-box {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    /* Head */
    .hcal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 22px;
        background: #0a2d29;
        color: #fff;
    }
    .hcal-head__left {
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 0;
    }
    .hcal-head__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(241, 205, 134, .14);
        border: 1px solid rgba(241, 205, 134, .3);
        color: #f1cd86;
        flex-shrink: 0;
    }
    .hcal-head__icon svg { width: 19px; height: 19px; }
    .hcal-head__title {
        font-size: 16px;
        font-weight: 700;
        line-height: 1.25;
    }
    .hcal-head__sub {
        font-size: 12px;
        color: rgba(255,255,255,.66);
        line-height: 1.3;
    }
    .hcal-close {
        background: rgba(255,255,255,.12);
        border: none;
        color: #fff;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
        line-height: 1;
        flex-shrink: 0;
        transition: background .15s;
    }
    .hcal-close:hover { background: rgba(255,255,255,.24); }

    /* Country tabs */
    .hcal-countries {
        position: relative;
        display: flex;
        gap: 4px;
        padding: 10px 22px;
        background: #0d3d38;
        border-bottom: 1px solid rgba(241, 205, 134, .18);
    }
    .hcal-country {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 16px;
        border: none;
        background: transparent;
        color: rgba(255,255,255,.72);
        font-size: 13px;
        font-weight: 600;
        font-family: inherit;
        border-radius: 999px;
        cursor: pointer;
        transition: color .2s;
    }
    .hcal-country:hover { color: #fff; }
    .hcal-country.active { color: #0a2d29; }
    .hcal-country__flag { font-size: 15px; line-height: 1; }
    .hcal-country-ink {
        position: absolute;
        top: 10px;
        left: 0;
        height: calc(100% - 20px);
        background: linear-gradient(135deg, #f1cd86 0%, #e2b968 100%);
        border-radius: 999px;
        transition: left .3s cubic-bezier(.22,1,.36,1), width .3s cubic-bezier(.22,1,.36,1);
        box-shadow: 0 2px 10px rgba(241, 205, 134, .35);
    }

    /* Body layout */
    .hcal-body {
        display: grid;
        grid-template-columns: 1.35fr 1fr;
        min-height: 0;
        flex: 1;
        overflow: hidden;
    }
    .hcal-grid-side {
        padding: 18px 20px 16px;
        border-right: 1px solid #eef0f2;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
    }
    .hcal-list-side {
        padding: 18px 20px;
        background: #fafbfc;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    /* Month nav */
    .hcal-nav {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
    }
    .hcal-nav-btn {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 8px;
        color: #0a2d29;
        cursor: pointer;
        transition: background .15s, border-color .15s, transform .1s;
    }
    .hcal-nav-btn:hover { background: #f1cd86; border-color: #e2b968; }
    .hcal-nav-btn:active { transform: scale(.92); }
    .hcal-nav-btn svg { width: 15px; height: 15px; }
    .hcal-nav-label {
        flex: 1;
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: 7px;
    }
    .hcal-nav-month {
        font-size: 17px;
        font-weight: 700;
        color: #0a2d29;
    }
    .hcal-nav-year {
        font-size: 13px;
        font-weight: 600;
        color: #c9a84c;
    }
    .hcal-today-btn {
        padding: 6px 12px;
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
        color: #0a2d29;
        cursor: pointer;
        font-family: inherit;
        transition: background .15s, border-color .15s;
    }
    .hcal-today-btn:hover { background: #ecfdf5; border-color: #a7f3d0; }

    /* Week header + days grid */
    .hcal-weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        margin-bottom: 4px;
    }
    .hcal-weekdays span {
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #9ca3af;
        padding: 6px 0;
    }
    .hcal-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 3px;
    }
    .hcal-day {
        position: relative;
        aspect-ratio: 1 / .82;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        cursor: default;
        opacity: 0;
        transform: scale(.85);
        animation: hcalDayIn .3s ease forwards;
        transition: background .15s;
    }
    @keyframes hcalDayIn {
        to { opacity: 1; transform: scale(1); }
    }
    .hcal-day--pad {
        animation: none;
        opacity: 1;
        transform: none;
        cursor: default;
    }
    .hcal-day--muted { color: #d1d5db; font-weight: 500; }
    .hcal-day--holiday { background: #fdf6e5; color: #92610e; cursor: pointer; }
    .hcal-day--holiday:hover { background: #f9ebc8; }
    .hcal-day--obs { background: #f0fdf9; color: #0f766e; cursor: pointer; }
    .hcal-day--obs:hover { background: #d8f7ee; }
    .hcal-day--today {
        background: #0a2d29;
        color: #fff;
        box-shadow: 0 4px 14px rgba(10, 45, 41, .35);
    }
    .hcal-day--today.hcal-day--holiday,
    .hcal-day--today.hcal-day--obs {
        background: linear-gradient(135deg, #0a2d29 0%, #14554d 100%);
        color: #f1cd86;
    }
    .hcal-day--selected {
        outline: 2px solid #f1cd86;
        outline-offset: 2px;
        box-shadow: 0 0 0 3px rgba(241, 205, 134, .25);
    }
    .hcal-day--holiday.hcal-day--selected,
    .hcal-day--obs.hcal-day--selected {
        transform: scale(1.04);
    }
    .hcal-day__dots {
        display: flex;
        gap: 3px;
        height: 5px;
    }
    .hcal-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        display: inline-block;
    }
    .hcal-dot--public { background: #d9a62e; }
    .hcal-dot--obs { background: #14b8a6; }
    .hcal-day--today .hcal-dot--public { background: #f1cd86; }
    .hcal-day--today .hcal-dot--obs { background: #5eead4; }

    /* Day tooltip */
    .hcal-day[data-tip]:hover::after {
        content: attr(data-tip);
        position: absolute;
        bottom: calc(100% + 6px);
        left: 50%;
        transform: translateX(-50%);
        background: #0a2d29;
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
        padding: 5px 10px;
        border-radius: 7px;
        box-shadow: 0 6px 18px rgba(0,0,0,.25);
        z-index: 5;
        pointer-events: none;
    }

    .hcal-legend {
        display: flex;
        gap: 16px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #f3f4f6;
    }
    .hcal-legend__item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: #6b7280;
        font-weight: 600;
    }

    /* Selected day action bar */
    .hcal-day-action {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 12px;
        padding: 12px 14px;
        background: linear-gradient(135deg, #0a2d29 0%, #0f3d37 100%);
        border-radius: 12px;
        border: 1px solid rgba(241, 205, 134, .28);
        animation: hcalActionIn .28s ease;
    }
    .hcal-day-action[hidden] { display: none !important; }
    @keyframes hcalActionIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .hcal-day-action__info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }
    .hcal-day-action__date {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: rgba(255,255,255,.55);
    }
    .hcal-day-action__name {
        font-size: 13px;
        font-weight: 700;
        color: #f1cd86;
        line-height: 1.3;
    }
    .hcal-generate-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex-shrink: 0;
        padding: 8px 14px;
        border: none;
        border-radius: 999px;
        background: linear-gradient(135deg, #f1cd86 0%, #e2b968 100%);
        color: #0a2d29;
        font-size: 12px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        transition: transform .12s, box-shadow .2s;
        box-shadow: 0 4px 14px rgba(241, 205, 134, .35);
    }
    .hcal-generate-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(241, 205, 134, .45);
    }
    .hcal-generate-btn:active { transform: scale(.96); }
    .hcal-generate-btn svg { width: 15px; height: 15px; }
    .hcal-generate-btn--sm {
        padding: 6px 10px;
        font-size: 10.5px;
        gap: 5px;
    }
    .hcal-generate-btn--sm svg { width: 13px; height: 13px; }

    /* Toast */
    .hcal-toast {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(16px);
        max-width: min(480px, calc(100vw - 32px));
        padding: 12px 18px;
        background: #0a2d29;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.45;
        border-radius: 12px;
        border: 1px solid rgba(241, 205, 134, .3);
        box-shadow: 0 12px 40px rgba(0,0,0,.35);
        z-index: 9600;
        opacity: 0;
        visibility: hidden;
        transition: opacity .25s, transform .25s, visibility .25s;
        pointer-events: none;
    }
    .hcal-toast.show {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }
    .hcal-toast a {
        color: #f1cd86;
        text-decoration: underline;
    }

    /* Events list */
    .hcal-list-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #9ca3af;
        margin-bottom: 10px;
    }
    .hcal-list {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }
    .hcal-event {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        background: #fff;
        border: 1px solid #eef0f2;
        border-radius: 10px;
        opacity: 0;
        transform: translateX(10px);
        animation: hcalEventIn .35s ease forwards;
        transition: border-color .15s, box-shadow .15s;
    }
    .hcal-event:hover {
        border-color: #f1cd86;
        box-shadow: 0 4px 14px rgba(241, 205, 134, .18);
    }
    @keyframes hcalEventIn {
        to { opacity: 1; transform: translateX(0); }
    }
    .hcal-event__date {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 9px;
        background: #0a2d29;
        color: #f1cd86;
        flex-shrink: 0;
    }
    .hcal-event--obs .hcal-event__date {
        background: #ecfdf5;
        color: #0f766e;
    }
    .hcal-event__day {
        font-size: 15px;
        font-weight: 800;
        line-height: 1.05;
    }
    .hcal-event__mon {
        font-size: 8.5px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        opacity: .85;
    }
    .hcal-event__info { min-width: 0; flex: 1; }
    .hcal-event__name {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
        line-height: 1.3;
    }
    .hcal-event__meta {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 1px;
    }
    .hcal-event__badge {
        flex-shrink: 0;
        font-size: 9.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: 3px 8px;
        border-radius: 999px;
        background: #fdf6e5;
        color: #b07d17;
    }
    .hcal-event--obs .hcal-event__badge {
        background: #f0fdf9;
        color: #0f766e;
    }
    .hcal-event--past { opacity: .55 !important; }
    .hcal-event--selected {
        border-color: #f1cd86 !important;
        box-shadow: 0 4px 14px rgba(241, 205, 134, .22) !important;
    }
    .hcal-event__actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }
    .hcal-list-empty {
        padding: 26px 10px;
        text-align: center;
        font-size: 12.5px;
        color: #9ca3af;
    }

    /* Topbar calendar trigger */
    .cal-trigger {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(241, 205, 134, 0.2);
        border-radius: 50%;
        color: #f1cd86;
        cursor: pointer;
        transition: background .25s, border-color .25s, box-shadow .25s, transform .15s;
    }
    .cal-trigger:hover {
        background: rgba(241, 205, 134, 0.12);
        border-color: rgba(241, 205, 134, 0.4);
        box-shadow: 0 0 18px rgba(241, 205, 134, 0.15);
        transform: translateY(-1px);
    }
    .cal-trigger svg { width: 16px; height: 16px; }

    @media (max-width: 720px) {
        .hcal-body { grid-template-columns: 1fr; }
        .hcal-grid-side { border-right: none; border-bottom: 1px solid #eef0f2; }
        .hcal-countries { padding: 10px 14px; overflow-x: auto; }
        .hcal-day[data-tip]:hover::after { display: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        .hcal-day, .hcal-event { animation: none; opacity: 1; transform: none; }
        .hcal-box, .hcal-overlay, .hcal-country-ink { transition: none; }
    }
</style>

<script>
(function () {
    const HCAL_URL = @json(route('calendar.holidays'));
    const POSTER_HEADER_URL = @json(asset('poster_header.png'));
    const COUNTRY_LABELS = { IN: 'India', US: 'United States', CA: 'Canada' };
    const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    let hcalCountry = localStorage.getItem('hcalCountry') || 'IN';
    let hcalYear = new Date().getFullYear();
    let hcalMonth = new Date().getMonth();
    let hcalCache = {};
    let hcalFetchSeq = 0;
    let hcalSelectedIso = null;
    let hcalSelectedEvents = [];
    let hcalToastTimer = null;

    function pad2(n) { return String(n).padStart(2, '0'); }

    function primaryHolidayName(events) {
        const pub = events.find(e => e.type === 'public');
        return (pub || events[0]).name;
    }

    function formatSelectedDate(iso) {
        const d = new Date(iso + 'T00:00:00');
        return d.toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric',
        });
    }

    function buildPosterPrompt(holiday, country) {
        const countryName = COUNTRY_LABELS[country] || country;
        return [
            'Please create a professional LinkedIn poster for RADiiX INFINITEii celebrating ' + holiday + ' in ' + countryName + '.',
            '',
            'DESIGN REQUIREMENTS:',
            '• Use the attached image as the top header/banner of the poster — integrate it as-is at the top, do not redesign the header',
            '• Style: clean, modern, corporate, and professional (IT staffing / recruitment brand aesthetic)',
            '• Color palette: deep teal (#0a2d29), gold/champagne (#f1cd86), and white',
            '• Visual theme: subtle tech and neural-network motifs in gold (nodes, connections, circuit lines) as elegant background accents — refined, not cluttered',
            '• Feature the occasion name (' + holiday + ') prominently with tasteful typography',
            '• Include a short, respectful celebratory message appropriate for ' + countryName + ' and this occasion',
            '• Dimensions: LinkedIn-optimized (1200×627 px landscape or 1080×1080 px square), high resolution, ready to post',
            '• Keep it brand-focused — no random stock photos of people',
            '',
            'AFTER GENERATING THE IMAGE:',
            '• Also provide a ready-to-post LinkedIn caption for this occasion — professional, warm, and on-brand',
            '• Include 3–5 relevant hashtags (e.g. #RADiiXINFINITEii, occasion-specific, and ' + countryName + ')',
            '• Keep the caption concise (2–4 short paragraphs max) with a clear call-to-action where appropriate',
            '',
            'BRAND:',
            'RADiiX INFINITEii — "Rooting Intelligence, Inspiring Innovation"',
            'Website: www.rinfinite.com',
            '',
            'I am attaching our official poster header image. Please use it at the top of the design and build the rest of the poster below it.',
        ].join('\n');
    }

    function showHcalToast(message, duration) {
        const toast = document.getElementById('hcalToast');
        if (!toast) return;
        toast.innerHTML = message;
        toast.classList.add('show');
        if (hcalToastTimer) clearTimeout(hcalToastTimer);
        hcalToastTimer = setTimeout(function () {
            toast.classList.remove('show');
        }, duration || 7000);
    }

    let posterHeaderPngBlob = null;
    let posterHeaderLoadPromise = null;

    function blobToPng(blob) {
        return new Promise(function (resolve, reject) {
            const url = URL.createObjectURL(blob);
            const img = new Image();
            img.onload = function () {
                const canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;
                canvas.getContext('2d').drawImage(img, 0, 0);
                URL.revokeObjectURL(url);
                canvas.toBlob(function (b) {
                    b ? resolve(b) : reject(new Error('toBlob failed'));
                }, 'image/png');
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('image load failed'));
            };
            img.src = url;
        });
    }

    function loadPosterHeaderPng() {
        if (posterHeaderPngBlob) {
            return Promise.resolve(posterHeaderPngBlob);
        }
        if (posterHeaderLoadPromise) {
            return posterHeaderLoadPromise;
        }

        posterHeaderLoadPromise = fetch(POSTER_HEADER_URL, { cache: 'no-store' })
            .then(function (res) {
                if (!res.ok) throw new Error('fetch failed');
                return res.blob();
            })
            .then(function (blob) {
                if (blob.type === 'image/png' && blob.size > 0) {
                    return blob;
                }
                return blobToPng(blob);
            })
            .then(function (blob) {
                posterHeaderPngBlob = blob;
                return blob;
            })
            .catch(function (err) {
                posterHeaderLoadPromise = null;
                throw err;
            });

        return posterHeaderLoadPromise;
    }

    function downloadPosterHeader() {
        const doDownload = function (blob) {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'RADiiX-poster-header.png';
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            setTimeout(function () {
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }, 1000);
        };

        if (posterHeaderPngBlob) {
            doDownload(posterHeaderPngBlob);
            return Promise.resolve();
        }
        return loadPosterHeaderPng().then(doDownload);
    }

    /** Must be invoked synchronously inside the click handler (before window.open). */
    function startPosterHeaderClipboardCopy() {
        if (!navigator.clipboard || !window.ClipboardItem) {
            return Promise.reject(new Error('clipboard unsupported'));
        }

        return navigator.clipboard.write([
            new ClipboardItem({
                'image/png': loadPosterHeaderPng(),
            }),
        ]).catch(function () {
            return loadPosterHeaderPng().then(copyImageViaSelection);
        });
    }

    function copyImageViaSelection(blob) {
        return new Promise(function (resolve, reject) {
            const url = URL.createObjectURL(blob);
            const wrapper = document.createElement('div');
            wrapper.contentEditable = 'true';
            wrapper.style.cssText = 'position:fixed;left:-9999px;top:0;opacity:0;';
            const img = document.createElement('img');
            img.src = url;
            wrapper.appendChild(img);
            document.body.appendChild(wrapper);

            const range = document.createRange();
            range.selectNode(img);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);

            let ok = false;
            try {
                ok = document.execCommand('copy');
            } catch (_) {
                ok = false;
            }

            sel.removeAllRanges();
            document.body.removeChild(wrapper);
            URL.revokeObjectURL(url);

            ok ? resolve() : reject(new Error('execCommand copy failed'));
        });
    }

    window.generateLinkedInPoster = function (holiday, country) {
        const prompt = buildPosterPrompt(holiday, country || hcalCountry);
        const chatUrl = 'https://chatgpt.com/?model=gpt-4o&q=' + encodeURIComponent(prompt);

        // Start clipboard in the same user-gesture turn — do not await before this.
        const copyTask = startPosterHeaderClipboardCopy();

        window.open(chatUrl, '_blank', 'noopener,noreferrer');

        copyTask.then(function () {
            showHcalToast(
                'ChatGPT opened. Click the message box, press <strong>Ctrl+V</strong> (Mac: <strong>Cmd+V</strong>) to paste the header image, then send.',
                10000
            );
        }).catch(function () {
            downloadPosterHeader().then(function () {
                showHcalToast(
                    'ChatGPT opened. The header was downloaded — use the <strong>paperclip</strong> in ChatGPT to attach <strong>RADiiX-poster-header.png</strong>, or drag it into the chat.',
                    11000
                );
            }).catch(function () {
                showHcalToast(
                    'ChatGPT opened. Attach the header from <a href="' + POSTER_HEADER_URL + '" target="_blank" rel="noopener">this image</a>.',
                    10000
                );
            });
        });
    };

    function clearDaySelection() {
        hcalSelectedIso = null;
        hcalSelectedEvents = [];
        document.querySelectorAll('.hcal-day--selected').forEach(function (el) {
            el.classList.remove('hcal-day--selected');
        });
        document.querySelectorAll('.hcal-event--selected').forEach(function (el) {
            el.classList.remove('hcal-event--selected');
        });
        const action = document.getElementById('hcalDayAction');
        if (action) action.hidden = true;
    }

    function selectHolidayDate(iso, events) {
        if (!events || !events.length) {
            clearDaySelection();
            return;
        }

        hcalSelectedIso = iso;
        hcalSelectedEvents = events;

        document.querySelectorAll('.hcal-day--selected').forEach(function (el) {
            el.classList.remove('hcal-day--selected');
        });
        document.querySelectorAll('.hcal-event--selected').forEach(function (el) {
            el.classList.remove('hcal-event--selected');
        });

        const cell = document.querySelector('.hcal-day[data-iso="' + iso + '"]');
        if (cell) cell.classList.add('hcal-day--selected');

        document.querySelectorAll('.hcal-event[data-iso="' + iso + '"]').forEach(function (el) {
            el.classList.add('hcal-event--selected');
        });

        const action = document.getElementById('hcalDayAction');
        const selDate = document.getElementById('hcalSelDate');
        const selName = document.getElementById('hcalSelName');
        if (action && selDate && selName) {
            selDate.textContent = formatSelectedDate(iso);
            selName.textContent = events.map(function (e) { return e.name; }).join(' · ');
            action.hidden = false;
        }
    }

    async function fetchHolidays(country, year) {
        const key = country + '-' + year;
        if (hcalCache[key]) return hcalCache[key];
        const res = await fetch(HCAL_URL + '?country=' + country + '&year=' + year, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) throw new Error('Failed to load holidays');
        const data = await res.json();
        hcalCache[key] = data.holidays || [];
        return hcalCache[key];
    }

    function holidayMap(list) {
        const map = {};
        list.forEach(h => {
            (map[h.date] = map[h.date] || []).push(h);
        });
        return map;
    }

    function renderCountryInk() {
        const active = document.querySelector('.hcal-country.active');
        const ink = document.getElementById('hcalCountryInk');
        if (!active || !ink) return;
        ink.style.left = active.offsetLeft + 'px';
        ink.style.width = active.offsetWidth + 'px';
    }

    function renderCalendar(holidays) {
        const daysEl = document.getElementById('hcalDays');
        const map = holidayMap(holidays);
        const today = new Date();
        const isThisMonth = today.getFullYear() === hcalYear && today.getMonth() === hcalMonth;

        document.getElementById('hcalMonthLabel').textContent = MONTHS[hcalMonth];
        document.getElementById('hcalYearLabel').textContent = hcalYear;
        document.getElementById('hcalHeadSub').textContent =
            COUNTRY_LABELS[hcalCountry] + ' · Public holidays & observances';

        const firstDow = new Date(hcalYear, hcalMonth, 1).getDay();
        const daysInMonth = new Date(hcalYear, hcalMonth + 1, 0).getDate();

        daysEl.innerHTML = '';

        const preserveIso = hcalSelectedIso;
        document.querySelectorAll('.hcal-day--selected').forEach(function (el) {
            el.classList.remove('hcal-day--selected');
        });

        for (let i = 0; i < firstDow; i++) {
            const pad = document.createElement('div');
            pad.className = 'hcal-day hcal-day--pad hcal-day--muted';
            daysEl.appendChild(pad);
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const iso = hcalYear + '-' + pad2(hcalMonth + 1) + '-' + pad2(d);
            const events = map[iso] || [];
            const cell = document.createElement('div');
            cell.className = 'hcal-day';
            cell.style.animationDelay = Math.min((firstDow + d) * 9, 320) + 'ms';

            const hasPublic = events.some(e => e.type === 'public');
            const hasObs = events.some(e => e.type !== 'public');
            if (hasPublic) cell.classList.add('hcal-day--holiday');
            else if (hasObs) cell.classList.add('hcal-day--obs');
            if (isThisMonth && d === today.getDate()) cell.classList.add('hcal-day--today');
            if (events.length) {
                cell.setAttribute('data-tip', events.map(e => e.name).join(' · '));
                cell.dataset.iso = iso;
                if (hcalSelectedIso === iso) cell.classList.add('hcal-day--selected');
                cell.addEventListener('click', function (e) {
                    e.stopPropagation();
                    selectHolidayDate(iso, events);
                });
            }

            const num = document.createElement('span');
            num.textContent = d;
            cell.appendChild(num);

            if (events.length) {
                const dots = document.createElement('span');
                dots.className = 'hcal-day__dots';
                events.slice(0, 3).forEach(e => {
                    const dot = document.createElement('span');
                    dot.className = 'hcal-dot ' + (e.type === 'public' ? 'hcal-dot--public' : 'hcal-dot--obs');
                    dots.appendChild(dot);
                });
                cell.appendChild(dots);
            }

            daysEl.appendChild(cell);
        }

        if (preserveIso && map[preserveIso]) {
            selectHolidayDate(preserveIso, map[preserveIso]);
        } else if (preserveIso && !map[preserveIso]) {
            clearDaySelection();
        }
    }

    function renderEventList(holidays) {
        const listEl = document.getElementById('hcalList');
        const titleEl = document.getElementById('hcalListTitle');
        titleEl.textContent = MONTHS[hcalMonth] + ' ' + hcalYear + ' — ' + COUNTRY_LABELS[hcalCountry];

        const monthPrefix = hcalYear + '-' + pad2(hcalMonth + 1);
        const monthEvents = holidays.filter(h => h.date.startsWith(monthPrefix));

        listEl.innerHTML = '';
        if (!monthEvents.length) {
            listEl.innerHTML = '<div class="hcal-list-empty">No holidays or observances this month.</div>';
            return;
        }

        const todayIso = new Date().getFullYear() + '-' + pad2(new Date().getMonth() + 1) + '-' + pad2(new Date().getDate());

        monthEvents.forEach((h, i) => {
            const d = new Date(h.date + 'T00:00:00');
            const item = document.createElement('div');
            item.className = 'hcal-event' + (h.type !== 'public' ? ' hcal-event--obs' : '') + (h.date < todayIso ? ' hcal-event--past' : '');
            if (hcalSelectedIso === h.date) item.classList.add('hcal-event--selected');
            item.dataset.iso = h.date;
            item.style.animationDelay = (i * 45) + 'ms';

            const genBtn = document.createElement('button');
            genBtn.type = 'button';
            genBtn.className = 'hcal-generate-btn hcal-generate-btn--sm';
            genBtn.title = 'Generate LinkedIn poster in ChatGPT';
            genBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/></svg> Generate';
            genBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                generateLinkedInPoster(h.name, hcalCountry);
            });

            item.innerHTML =
                '<div class="hcal-event__date">' +
                    '<span class="hcal-event__day">' + d.getDate() + '</span>' +
                    '<span class="hcal-event__mon">' + MONTHS[d.getMonth()].substring(0, 3) + '</span>' +
                '</div>' +
                '<div class="hcal-event__info">' +
                    '<div class="hcal-event__name"></div>' +
                    '<div class="hcal-event__meta">' + d.toLocaleDateString('en-US', { weekday: 'long' }) + '</div>' +
                '</div>' +
                '<div class="hcal-event__actions">' +
                    '<span class="hcal-event__badge">' + (h.type === 'public' ? 'Holiday' : 'Observance') + '</span>' +
                '</div>';
            item.querySelector('.hcal-event__name').textContent = h.name;
            item.querySelector('.hcal-event__actions').prepend(genBtn);

            item.addEventListener('click', function () {
                const dayEvents = monthEvents.filter(function (ev) { return ev.date === h.date; });
                selectHolidayDate(h.date, dayEvents);
            });

            listEl.appendChild(item);
        });
    }

    async function refreshHolidayCalendar() {
        const seq = ++hcalFetchSeq;
        document.querySelectorAll('.hcal-country').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.country === hcalCountry);
        });
        renderCountryInk();

        try {
            const holidays = await fetchHolidays(hcalCountry, hcalYear);
            if (seq !== hcalFetchSeq) return;
            renderCalendar(holidays);
            renderEventList(holidays);
        } catch (e) {
            if (seq !== hcalFetchSeq) return;
            renderCalendar([]);
            document.getElementById('hcalList').innerHTML =
                '<div class="hcal-list-empty">Could not load holidays. Please try again.</div>';
        }
    }

    window.openHolidayCalendar = function () {
        const overlay = document.getElementById('holidayCalOverlay');
        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        loadPosterHeaderPng().catch(function () {});
        refreshHolidayCalendar();
    };

    window.closeHolidayCalendar = function () {
        const overlay = document.getElementById('holidayCalOverlay');
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    document.addEventListener('DOMContentLoaded', function () {
        const overlay = document.getElementById('holidayCalOverlay');
        if (!overlay) return;

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeHolidayCalendar();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('open')) closeHolidayCalendar();
        });

        document.querySelectorAll('.hcal-country').forEach(btn => {
            btn.addEventListener('click', function () {
                hcalCountry = this.dataset.country;
                localStorage.setItem('hcalCountry', hcalCountry);
                clearDaySelection();
                refreshHolidayCalendar();
            });
        });

        document.getElementById('hcalPrev').addEventListener('click', function () {
            hcalMonth--;
            if (hcalMonth < 0) { hcalMonth = 11; hcalYear--; }
            clearDaySelection();
            refreshHolidayCalendar();
        });

        document.getElementById('hcalNext').addEventListener('click', function () {
            hcalMonth++;
            if (hcalMonth > 11) { hcalMonth = 0; hcalYear++; }
            clearDaySelection();
            refreshHolidayCalendar();
        });

        document.getElementById('hcalToday').addEventListener('click', function () {
            const now = new Date();
            hcalYear = now.getFullYear();
            hcalMonth = now.getMonth();
            refreshHolidayCalendar();
        });

        document.getElementById('hcalGenerateBtn').addEventListener('click', function () {
            if (!hcalSelectedEvents.length) return;
            generateLinkedInPoster(primaryHolidayName(hcalSelectedEvents), hcalCountry);
        });

        window.addEventListener('resize', renderCountryInk);
    });
})();
</script>
