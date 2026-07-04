<style>
    :root {
        --g-teal-deep: #0a2d29;
        --g-teal: #0f3d37;
        --g-teal-soft: #14554d;
        --g-gold: #f1cd86;
        --g-gold-dim: #e2b968;
        --g-ink: #12211f;
        --g-muted: #5f7370;
        --g-line: #e6ebe9;
    }

    /* Give the booklet room; neutralize default page padding feel */
    .guide-wrap {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-height: calc(100vh - var(--topbar-height, 72px) - 48px);
        max-width: 100%;
        overflow-x: hidden;
    }

    /* Top progress bar */
    .guide-progress {
        height: 5px;
        background: #e9efed;
        border-radius: 99px;
        overflow: hidden;
    }
    .guide-progress span {
        display: block;
        height: 100%;
        width: 0%;
        border-radius: 99px;
        background: linear-gradient(90deg, var(--g-teal) 0%, var(--g-gold) 100%);
        transition: width .5s cubic-bezier(.22,1,.36,1);
    }

    .guide-shell {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 18px;
        flex: 1;
        min-height: 0;
        min-width: 0;
        max-width: 100%;
    }

    /* ── Table of contents ── */
    .guide-toc {
        background: linear-gradient(165deg, var(--g-teal-deep) 0%, var(--g-teal) 100%);
        border-radius: 18px;
        padding: 20px 14px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(10,45,41,.18);
    }
    .guide-toc::after {
        content: "";
        position: absolute;
        top: -40px; right: -40px;
        width: 140px; height: 140px;
        background: radial-gradient(circle, rgba(241,205,134,.18), transparent 70%);
    }
    .guide-toc__head {
        padding: 0 8px 14px;
        margin-bottom: 8px;
        border-bottom: 1px solid rgba(241,205,134,.16);
    }
    .guide-toc__eyebrow {
        display: block;
        font-size: 10px;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: var(--g-gold);
        font-weight: 700;
    }
    .guide-toc__title {
        font-size: 18px;
        font-weight: 700;
    }
    .guide-toc__list {
        display: flex;
        flex-direction: column;
        gap: 2px;
        position: relative;
        z-index: 1;
    }
    .guide-toc__item {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 9px 10px;
        border-radius: 10px;
        color: rgba(255,255,255,.7);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        background: transparent;
        text-align: left;
        width: 100%;
        font-family: inherit;
        transition: background .2s, color .2s;
    }
    .guide-toc__item:hover { background: rgba(255,255,255,.06); color: #fff; }
    .guide-toc__item.active {
        background: rgba(241,205,134,.14);
        color: #fff;
    }
    .guide-toc__num {
        width: 22px; height: 22px;
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px;
        font-weight: 700;
        border-radius: 6px;
        background: rgba(255,255,255,.08);
        color: var(--g-gold);
    }
    .guide-toc__item.active .guide-toc__num {
        background: linear-gradient(135deg, var(--g-gold), var(--g-gold-dim));
        color: var(--g-teal-deep);
    }

    /* ── Book / pages ── */
    .guide-book {
        position: relative;
        background: #fff;
        border: 1px solid var(--g-line);
        border-radius: 20px;
        box-shadow: 0 24px 60px rgba(10,45,41,.12);
        overflow: hidden;
        min-height: 540px;
        min-width: 0;
        max-width: 100%;
        display: flex;
    }
    .guide-book::before {
        content: "";
        position: absolute;
        top: 0; bottom: 0; left: 0;
        width: 6px;
        background: linear-gradient(180deg, var(--g-teal) 0%, var(--g-gold) 100%);
        z-index: 3;
    }
    .guide-pages {
        position: relative;
        flex: 1;
        min-height: 540px;
        min-width: 0;
        overflow: hidden;
    }
    .guide-page {
        position: absolute;
        inset: 0;
        padding: 46px 54px;
        overflow-x: hidden;
        overflow-y: auto;
        opacity: 0;
        visibility: hidden;
        transform: translateX(40px);
        transition: opacity .45s ease, transform .5s cubic-bezier(.22,1,.36,1), visibility .45s;
    }
    .guide-page.is-active {
        opacity: 1;
        visibility: visible;
        transform: translateX(0);
    }
    .guide-page.is-prev {
        transform: translateX(-40px);
    }

    /* staggered content reveal */
    .guide-page.is-active .guide-page__head > *,
    .guide-page.is-active .guide-card,
    .guide-page.is-active .guide-step,
    .guide-page.is-active .guide-flow,
    .guide-page.is-active .guide-callout {
        animation: guideRise .55s cubic-bezier(.22,1,.36,1) both;
    }
    .guide-page.is-active .guide-page__head > *:nth-child(1) { animation-delay: .05s; }
    .guide-page.is-active .guide-page__head > *:nth-child(2) { animation-delay: .11s; }
    .guide-page.is-active .guide-page__head > *:nth-child(3) { animation-delay: .17s; }
    .guide-page.is-active .guide-grid .guide-card:nth-child(1),
    .guide-page.is-active .guide-steps .guide-step:nth-child(1) { animation-delay: .20s; }
    .guide-page.is-active .guide-grid .guide-card:nth-child(2),
    .guide-page.is-active .guide-steps .guide-step:nth-child(2) { animation-delay: .27s; }
    .guide-page.is-active .guide-grid .guide-card:nth-child(3),
    .guide-page.is-active .guide-steps .guide-step:nth-child(3) { animation-delay: .34s; }
    .guide-page.is-active .guide-grid .guide-card:nth-child(4),
    .guide-page.is-active .guide-steps .guide-step:nth-child(4) { animation-delay: .41s; }
    .guide-page.is-active .guide-grid .guide-card:nth-child(5) { animation-delay: .48s; }
    @keyframes guideRise {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .guide-page__head { margin-bottom: 26px; }
    .guide-chip {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--g-gold-dim);
        background: #fbf3df;
        padding: 5px 12px;
        border-radius: 99px;
        margin-bottom: 12px;
    }
    .guide-page__head h2 {
        font-size: 28px;
        font-weight: 800;
        color: var(--g-teal-deep);
        line-height: 1.2;
        margin: 0 0 8px;
    }
    .guide-page__head p {
        font-size: 15px;
        color: var(--g-muted);
        max-width: 640px;
        line-height: 1.55;
        margin: 0;
    }

    /* Grids of cards */
    .guide-grid { display: grid; gap: 16px; }
    .guide-grid--2 { grid-template-columns: repeat(2, 1fr); }
    .guide-grid--3 { grid-template-columns: repeat(3, 1fr); }
    .guide-card {
        background: #fbfdfc;
        border: 1px solid var(--g-line);
        border-radius: 14px;
        padding: 20px;
        transition: transform .2s, box-shadow .2s, border-color .2s;
    }
    .guide-card:hover {
        transform: translateY(-3px);
        border-color: var(--g-gold);
        box-shadow: 0 14px 30px rgba(241,205,134,.2);
    }
    .guide-card__ico {
        width: 42px; height: 42px;
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, var(--g-teal-deep), var(--g-teal-soft));
        color: var(--g-gold);
        margin-bottom: 14px;
    }
    .guide-card__ico svg { width: 21px; height: 21px; }
    .guide-card h3 {
        font-size: 15px;
        font-weight: 700;
        color: var(--g-ink);
        margin: 0 0 6px;
    }
    .guide-card p {
        font-size: 13px;
        color: var(--g-muted);
        line-height: 1.55;
        margin: 0 0 12px;
    }
    .guide-go {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 4px;
        padding: 7px 12px;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--g-teal-deep), var(--g-teal));
        color: var(--g-gold);
        font-size: 11.5px;
        font-weight: 700;
        text-decoration: none;
        transition: transform .15s, box-shadow .2s, background .2s;
        box-shadow: 0 4px 12px rgba(10, 45, 41, .15);
    }
    .guide-go:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(10, 45, 41, .22);
        color: #fff;
    }
    .guide-go svg { width: 13px; height: 13px; flex-shrink: 0; }
    .guide-step .guide-go { margin-top: 10px; }

    /* Steps */
    .guide-steps { display: flex; flex-direction: column; gap: 14px; }
    .guide-step {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        background: #fbfdfc;
        border: 1px solid var(--g-line);
        border-radius: 14px;
        padding: 18px 20px;
        transition: border-color .2s, box-shadow .2s;
    }
    .guide-step:hover { border-color: var(--g-gold); box-shadow: 0 10px 24px rgba(241,205,134,.16); }
    .guide-step__n {
        flex-shrink: 0;
        width: 34px; height: 34px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800;
        font-size: 15px;
        color: var(--g-teal-deep);
        background: linear-gradient(135deg, var(--g-gold), var(--g-gold-dim));
        box-shadow: 0 4px 12px rgba(241,205,134,.4);
    }
    .guide-step h3 {
        font-size: 15px;
        font-weight: 700;
        color: var(--g-ink);
        margin: 3px 0 5px;
    }
    .guide-step p {
        font-size: 13.5px;
        color: var(--g-muted);
        line-height: 1.55;
        margin: 0;
    }

    /* Flow diagram */
    .guide-flow {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        padding: 18px;
        background: linear-gradient(135deg, #f6faf9, #fbf3df);
        border: 1px solid var(--g-line);
        border-radius: 14px;
        margin-bottom: 20px;
        max-width: 100%;
        overflow: hidden;
    }
    .guide-flow__node {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--g-teal-deep);
        background: #fff;
        border: 1px solid var(--g-line);
        padding: 8px 13px;
        border-radius: 99px;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(10,45,41,.05);
    }
    .guide-flow__node--gold {
        background: linear-gradient(135deg, var(--g-gold), var(--g-gold-dim));
        border-color: var(--g-gold-dim);
    }
    .guide-flow__arrow { color: var(--g-gold-dim); font-weight: 700; }

    /* Callout */
    .guide-callout {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-top: 20px;
        padding: 16px 18px;
        background: linear-gradient(135deg, var(--g-teal-deep), var(--g-teal));
        border-radius: 14px;
        color: #fff;
    }
    .guide-callout__ico {
        flex-shrink: 0;
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(241,205,134,.16);
        color: var(--g-gold);
    }
    .guide-callout__ico svg { width: 20px; height: 20px; }
    .guide-callout p { margin: 0; font-size: 13.5px; line-height: 1.5; color: rgba(255,255,255,.9); }
    .guide-callout strong { color: var(--g-gold); }

    /* ── Cover page ── */
    .guide-cover {
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        overflow: hidden;
        background:
            radial-gradient(circle at 20% 20%, rgba(241,205,134,.12), transparent 45%),
            linear-gradient(160deg, var(--g-teal-deep) 0%, var(--g-teal) 60%, var(--g-teal-soft) 100%);
        padding: 40px 32px;
    }

    /* Ambient light sweep across cover */
    .guide-cover__ambient {
        position: absolute;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
        z-index: 0;
    }
    .guide-cover__ambient::before {
        content: "";
        position: absolute;
        top: 0;
        left: -60%;
        width: 45%;
        height: 100%;
        background: linear-gradient(
            105deg,
            transparent 0%,
            rgba(241, 205, 134, 0.04) 40%,
            rgba(255, 248, 231, 0.09) 50%,
            rgba(241, 205, 134, 0.04) 60%,
            transparent 100%
        );
        transform: skewX(-12deg);
        animation: guideCoverAmbient 7s ease-in-out infinite;
        animation-delay: 1.4s;
    }
    @keyframes guideCoverAmbient {
        0% { left: -60%; opacity: 0; }
        12% { opacity: 1; }
        88% { opacity: 1; }
        100% { left: 120%; opacity: 0; }
    }

    .guide-cover__glow {
        position: absolute;
        width: 320px;
        height: 320px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(241,205,134,.22), transparent 65%);
        top: -80px;
        right: -80px;
        pointer-events: none;
        z-index: 0;
        opacity: 0;
        animation: guideFloat 8s ease-in-out infinite, guideCoverGlowIn 1.4s ease forwards;
        animation-delay: 0s, 0.35s;
    }
    @keyframes guideFloat {
        0%,100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(24px) scale(1.06); }
    }
    @keyframes guideCoverGlowIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Staggered entrance — triggered by .guide-cover--play */
    @keyframes guideCoverIn {
        from {
            opacity: 0;
            transform: translateY(16px);
            filter: blur(4px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
            filter: blur(0);
        }
    }
    .guide-cover.guide-cover--play .guide-cover__badge,
    .guide-cover.guide-cover--play .guide-cover__logo,
    .guide-cover.guide-cover--play .guide-cover__title,
    .guide-cover.guide-cover--play .guide-cover__tag,
    .guide-cover.guide-cover--play .guide-cover__lead,
    .guide-cover.guide-cover--play .guide-cover__cta,
    .guide-cover.guide-cover--play .guide-cover__meta {
        opacity: 0;
        animation: guideCoverIn 0.95s cubic-bezier(.22, 1, .36, 1) forwards;
    }
    .guide-cover.guide-cover--play .guide-cover__badge  { animation-delay: 0.12s; }
    .guide-cover.guide-cover--play .guide-cover__logo   { animation-delay: 0.28s; }
    .guide-cover.guide-cover--play .guide-cover__title  { animation-delay: 0.44s; }
    .guide-cover.guide-cover--play .guide-cover__tag     { animation-delay: 0.62s; }
    .guide-cover.guide-cover--play .guide-cover__lead    { animation-delay: 0.82s; }
    .guide-cover.guide-cover--play .guide-cover__cta     { animation-delay: 1.02s; }
    .guide-cover.guide-cover--play .guide-cover__meta     { animation-delay: 1.18s; }

    .guide-cover__inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        max-width: 520px;
        margin: 0 auto;
    }
    .guide-cover__badge {
        display: inline-block;
        font-size: 11px;
        letter-spacing: .18em;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--g-teal-deep);
        background: linear-gradient(135deg, var(--g-gold), var(--g-gold-dim));
        padding: 6px 16px;
        border-radius: 99px;
        margin-bottom: 22px;
    }
    .guide-cover__logo {
        display: block;
        height: 64px;
        width: auto;
        margin: 0 auto 14px;
        filter: drop-shadow(0 8px 18px rgba(0,0,0,.3));
    }
    .guide-cover.guide-cover--play .guide-cover__logo {
        animation-name: guideCoverLogoIn;
    }
    @keyframes guideCoverLogoIn {
        from {
            opacity: 0;
            transform: translateY(14px) scale(0.94);
            filter: blur(4px) drop-shadow(0 0 0 transparent);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
            filter: blur(0) drop-shadow(0 8px 18px rgba(0,0,0,.3));
        }
    }
    .guide-cover__title {
        font-size: 40px;
        font-weight: 800;
        color: #fff;
        letter-spacing: .01em;
        margin: 0 0 6px;
        line-height: 1.15;
    }
    .guide-cover__title span { color: var(--g-gold); }

    /* Tagline with left-to-right glow sweep */
    .guide-cover__tag {
        position: relative;
        display: inline-block;
        font-size: 14px;
        font-style: normal;
        font-weight: 500;
        color: var(--g-gold);
        margin: 0 0 22px;
        letter-spacing: .04em;
        padding: 2px 4px;
        overflow: hidden;
    }
    .guide-cover__tag-text {
        position: relative;
        z-index: 1;
    }
    .guide-cover__tag-shine {
        position: absolute;
        top: -20%;
        left: 0;
        width: 55%;
        height: 140%;
        background: linear-gradient(
            90deg,
            transparent 0%,
            rgba(255, 248, 231, 0) 20%,
            rgba(255, 248, 231, 0.75) 50%,
            rgba(241, 205, 134, 0.35) 70%,
            transparent 100%
        );
        transform: translateX(-120%);
        pointer-events: none;
        mix-blend-mode: screen;
        animation: guideTagShine 3.2s ease-in-out infinite;
        animation-delay: 1.1s;
    }
    @keyframes guideTagShine {
        0% { transform: translateX(-120%); opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { transform: translateX(220%); opacity: 0; }
    }
    .guide-cover__tag::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: -4px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(241, 205, 134, 0.5), transparent);
        opacity: 0;
        animation: guideTagLineIn 1s ease forwards;
        animation-delay: 1.05s;
    }
    @keyframes guideTagLineIn {
        from { opacity: 0; transform: scaleX(0); }
        to { opacity: 1; transform: scaleX(1); }
    }

    .guide-cover__lead {
        font-size: 15px;
        line-height: 1.65;
        color: rgba(255,255,255,.82);
        margin: 0 0 30px;
        max-width: 480px;
        text-align: center;
    }
    .guide-cover__cta {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 13px 26px;
        border: none;
        border-radius: 99px;
        background: linear-gradient(135deg, var(--g-gold), var(--g-gold-dim));
        color: var(--g-teal-deep);
        font-size: 15px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        box-shadow: 0 12px 30px rgba(241,205,134,.4);
        transition: transform .18s, box-shadow .25s;
    }
    .guide-cover.guide-cover--play .guide-cover__cta {
        animation-name: guideCoverCtaIn;
    }
    @keyframes guideCoverCtaIn {
        from {
            opacity: 0;
            transform: translateY(14px);
            box-shadow: 0 0 0 rgba(241, 205, 134, 0);
        }
        to {
            opacity: 1;
            transform: translateY(0);
            box-shadow: 0 12px 30px rgba(241, 205, 134, .4);
        }
    }
    .guide-cover__cta:hover { transform: translateY(-2px); box-shadow: 0 16px 38px rgba(241,205,134,.55); }
    .guide-cover__cta svg { width: 18px; height: 18px; }
    .guide-cover__meta {
        display: flex; align-items: center; justify-content: center; gap: 12px;
        margin-top: 22px;
        font-size: 12px;
        color: rgba(255,255,255,.55);
        font-weight: 600;
    }
    .guide-cover__meta .dot { width: 4px; height: 4px; border-radius: 50%; background: var(--g-gold); }

    /* ── End page ── */
    .guide-end {
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        background: radial-gradient(circle at 50% 0%, #f6faf9, #fff 60%);
    }
    .guide-end__inner { max-width: 540px; }
    .guide-end__ico {
        width: 74px; height: 74px;
        margin: 0 auto 20px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, var(--g-teal-deep), var(--g-teal-soft));
        color: var(--g-gold);
        box-shadow: 0 14px 34px rgba(10,45,41,.28);
        animation: guidePop .6s cubic-bezier(.34,1.56,.64,1) both;
    }
    .guide-end__ico svg { width: 38px; height: 38px; }
    @keyframes guidePop { from { transform: scale(.4); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .guide-end h2 { font-size: 30px; font-weight: 800; color: var(--g-teal-deep); margin: 0 0 12px; }
    .guide-end p { font-size: 15px; color: var(--g-muted); line-height: 1.6; margin: 0 auto 28px; max-width: 460px; }
    .guide-end__actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .guide-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 22px;
        border-radius: 99px;
        font-size: 14px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        border: 1px solid transparent;
        text-decoration: none;
        transition: transform .18s, box-shadow .25s, background .2s;
    }
    .guide-btn--gold {
        background: linear-gradient(135deg, var(--g-gold), var(--g-gold-dim));
        color: var(--g-teal-deep);
        box-shadow: 0 10px 26px rgba(241,205,134,.4);
    }
    .guide-btn--gold:hover { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(241,205,134,.55); }
    .guide-btn--ghost {
        background: #fff;
        border-color: var(--g-line);
        color: var(--g-teal-deep);
    }
    .guide-btn--ghost:hover { border-color: var(--g-gold); background: #fbf7ec; }

    /* ── Bottom controls ── */
    .guide-controls {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 6px 4px;
    }
    .guide-nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 20px;
        border-radius: 99px;
        border: 1px solid var(--g-line);
        background: #fff;
        color: var(--g-teal-deep);
        font-size: 14px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        transition: transform .15s, box-shadow .2s, background .2s, opacity .2s;
    }
    .guide-nav-btn svg { width: 17px; height: 17px; }
    .guide-nav-btn:hover:not(:disabled) { border-color: var(--g-gold); background: #fbf7ec; }
    .guide-nav-btn:disabled { opacity: .4; cursor: not-allowed; }
    .guide-nav-btn--primary {
        background: linear-gradient(135deg, var(--g-teal-deep), var(--g-teal));
        color: #fff;
        border-color: transparent;
        box-shadow: 0 8px 22px rgba(10,45,41,.22);
    }
    .guide-nav-btn--primary:hover:not(:disabled) {
        transform: translateY(-2px);
        background: linear-gradient(135deg, var(--g-teal), var(--g-teal-soft));
    }

    .guide-dots { display: flex; align-items: center; gap: 8px; }
    .guide-dot {
        width: 9px; height: 9px;
        border-radius: 50%;
        background: #d8e1df;
        border: none;
        padding: 0;
        cursor: pointer;
        transition: transform .2s, background .2s, width .2s;
    }
    .guide-dot:hover { background: var(--g-gold-dim); }
    .guide-dot.active {
        background: linear-gradient(135deg, var(--g-gold), var(--g-gold-dim));
        width: 24px;
        border-radius: 99px;
    }

    /* Scrollbars inside pages */
    .guide-page::-webkit-scrollbar { width: 8px; }
    .guide-page::-webkit-scrollbar-thumb { background: #d8e1df; border-radius: 8px; }
    .guide-page::-webkit-scrollbar-track { background: transparent; }

    /* ── Responsive ── */
    @media (max-width: 1080px) {
        .guide-shell { grid-template-columns: 1fr; }
        .guide-toc { display: none; }
    }
    @media (max-width: 720px) {
        .guide-page { padding: 30px 22px; }
        .guide-grid--2, .guide-grid--3 { grid-template-columns: 1fr; }
        .guide-page__head h2 { font-size: 23px; }
        .guide-cover__title { font-size: 30px; }
        .guide-flow { justify-content: flex-start; }
    }
    @media (prefers-reduced-motion: reduce) {
        .guide-page,
        .guide-page.is-active .guide-page__head > *,
        .guide-page.is-active .guide-card,
        .guide-page.is-active .guide-step,
        .guide-page.is-active .guide-flow,
        .guide-page.is-active .guide-callout,
        .guide-cover__glow,
        .guide-cover__ambient::before,
        .guide-cover__tag-shine,
        .guide-cover__tag::after,
        .guide-end__ico {
            animation: none !important;
            transition: opacity .2s ease !important;
            transform: none !important;
            filter: none !important;
            opacity: 1 !important;
        }
        .guide-cover.guide-cover--play .guide-cover__badge,
        .guide-cover.guide-cover--play .guide-cover__logo,
        .guide-cover.guide-cover--play .guide-cover__title,
        .guide-cover.guide-cover--play .guide-cover__tag,
        .guide-cover.guide-cover--play .guide-cover__lead,
        .guide-cover.guide-cover--play .guide-cover__cta,
        .guide-cover.guide-cover--play .guide-cover__meta {
            opacity: 1 !important;
        }
    }
</style>
