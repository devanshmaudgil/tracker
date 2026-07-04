<script>
(function () {
    const wrap = document.getElementById('guideWrap');
    if (!wrap) return;

    const pages = Array.from(document.querySelectorAll('.guide-page'));
    const tocList = document.getElementById('guideTocList');
    const dotsWrap = document.getElementById('guideDots');
    const prevBtn = document.getElementById('guidePrev');
    const nextBtn = document.getElementById('guideNext');
    const progressBar = document.getElementById('guideProgressBar');
    const pagesEl = document.getElementById('guidePages');
    const total = pages.length;
    let current = 0;

    const ICONS = {
        book: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>',
        compass: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><polygon points="16 8 10 10 8 16 14 14 16 8"/></svg>',
        chart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
        briefcase: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>',
        flow: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="6" height="6" rx="1"/><rect x="15" y="15" width="6" height="6" rx="1"/><path d="M9 6h6a3 3 0 0 1 3 3v6"/></svg>',
        bolt: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
        scan: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><line x1="3" y1="12" x2="21" y2="12"/></svg>',
        search: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
        grid: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
        calendar: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        flag: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V4s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>',
    };

    // Build TOC + dots
    pages.forEach((page, i) => {
        const title = page.dataset.title || ('Page ' + (i + 1));
        const iconKey = page.dataset.icon || 'book';

        const tocItem = document.createElement('button');
        tocItem.type = 'button';
        tocItem.className = 'guide-toc__item';
        tocItem.innerHTML =
            '<span class="guide-toc__num">' + (i === 0 ? (ICONS.book) : i) + '</span>' +
            '<span>' + title + '</span>';
        if (i === 0) {
            tocItem.querySelector('.guide-toc__num').style.padding = '3px';
        }
        tocItem.addEventListener('click', () => goTo(i));
        tocList.appendChild(tocItem);

        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'guide-dot';
        dot.setAttribute('aria-label', 'Go to ' + title);
        dot.addEventListener('click', () => goTo(i));
        dotsWrap.appendChild(dot);
    });

    const tocItems = Array.from(tocList.children);
    const dots = Array.from(dotsWrap.children);

    function render() {
        pages.forEach((page, i) => {
            page.classList.toggle('is-active', i === current);
            page.classList.toggle('is-prev', i < current);
        });
        tocItems.forEach((el, i) => el.classList.toggle('active', i === current));
        dots.forEach((el, i) => el.classList.toggle('active', i === current));

        prevBtn.disabled = current === 0;
        nextBtn.innerHTML = current === total - 1
            ? '<span>Finish</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>'
            : '<span>Next</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>';

        progressBar.style.width = ((current) / (total - 1) * 100) + '%';

        const activePage = pages[current];
        if (activePage) activePage.scrollTop = 0;
    }

    function replayCoverEntrance() {
        const cover = pages[0];
        if (!cover || !cover.classList.contains('guide-cover')) return;
        cover.classList.remove('guide-cover--play');
        void cover.offsetWidth;
        cover.classList.add('guide-cover--play');
    }

    function goTo(i) {
        const target = Math.max(0, Math.min(total - 1, i));
        if (target === current) {
            if (target === 0) replayCoverEntrance();
            return;
        }
        current = target;
        render();
        if (target === 0) replayCoverEntrance();
    }

    function next() {
        if (current < total - 1) goTo(current + 1);
    }
    function prev() {
        if (current > 0) goTo(current - 1);
    }

    prevBtn.addEventListener('click', prev);
    nextBtn.addEventListener('click', next);

    // data-go buttons (cover CTA, restart)
    document.querySelectorAll('[data-go]').forEach(btn => {
        btn.addEventListener('click', () => goTo(parseInt(btn.dataset.go, 10) || 0));
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.target && /^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName)) return;
        if (e.key === 'ArrowRight') { e.preventDefault(); next(); }
        else if (e.key === 'ArrowLeft') { e.preventDefault(); prev(); }
    });

    // Touch swipe
    let touchX = null;
    pagesEl.addEventListener('touchstart', (e) => { touchX = e.changedTouches[0].clientX; }, { passive: true });
    pagesEl.addEventListener('touchend', (e) => {
        if (touchX === null) return;
        const dx = e.changedTouches[0].clientX - touchX;
        if (Math.abs(dx) > 60) { dx < 0 ? next() : prev(); }
        touchX = null;
    }, { passive: true });

    render();
})();
</script>
