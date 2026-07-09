<script>
(function () {
    'use strict';

    const PALETTE = {
        teal: '#0a2d29',
        teal2: '#0f3d38',
        tealGlow: '#1f6f60',
        tealSoft: '#3f8f7f',
        gold: '#e3b04b',
        goldD: '#c9932f',
        goldDeep: '#a9711b',
        goldLight: '#f1cd86',
        sand: '#d8c39a',
        slate: '#7d9a92',
    };
    // Status order from the backend: Open, In Progress, Placed, Rejected.
    const STATUS_COLORS = [PALETTE.teal, PALETTE.gold, PALETTE.tealGlow, PALETTE.slate];
    const MULTI = [PALETTE.teal, PALETTE.gold, PALETTE.tealGlow, PALETTE.goldD, PALETTE.tealSoft, PALETTE.sand, PALETTE.goldDeep, PALETTE.slate];

    const charts = {};
    let payload = window.DASHBOARD_PAYLOAD || {};

    if (window.Chart) {
        Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#6b7280';
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.boxWidth = 8;
        Chart.defaults.plugins.legend.labels.padding = 14;
        Chart.defaults.plugins.tooltip.backgroundColor = '#0a2d29';
        Chart.defaults.plugins.tooltip.titleColor = '#f1cd86';
        Chart.defaults.plugins.tooltip.bodyColor = '#ffffff';
        Chart.defaults.plugins.tooltip.titleFont = { weight: '700', size: 12 };
        Chart.defaults.plugins.tooltip.bodyFont = { size: 12 };
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
        Chart.defaults.plugins.tooltip.displayColors = true;
        Chart.defaults.plugins.tooltip.boxPadding = 4;
        Chart.defaults.elements.point.hoverBorderWidth = 2;
        Chart.defaults.elements.arc.borderWidth = 2;

        // Center total label for doughnut charts.
        Chart.register({
            id: 'd2CenterText',
            afterDraw: function (chart) {
                if (chart.config.type !== 'doughnut' || !chart.config.options.plugins || !chart.config.options.plugins.d2CenterText) return;
                const dataset = chart.data.datasets[0] || { data: [] };
                const total = dataset.data.reduce(function (a, b) { return a + (Number(b) || 0); }, 0);
                const area = chart.chartArea;
                const cx = (area.left + area.right) / 2;
                const cy = (area.top + area.bottom) / 2;
                const ctx = chart.ctx;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.font = "800 24px 'Inter', system-ui, sans-serif";
                ctx.fillStyle = '#0a2d29';
                ctx.fillText(total.toLocaleString(), cx, cy - 9);
                ctx.font = "700 10.5px 'Inter', system-ui, sans-serif";
                ctx.fillStyle = '#9ca3af';
                ctx.letterSpacing = '.05em';
                ctx.fillText('TOTAL', cx, cy + 13);
                ctx.restore();
            },
        });
    }

    /* ── KPI count-up ── */
    function animateValue(el, to) {
        const isFloat = String(to).indexOf('.') !== -1 || el.dataset.float === '1';
        const from = parseFloat(el.dataset.current || '0') || 0;
        const target = parseFloat(to) || 0;
        el.dataset.current = target;
        const start = performance.now();
        const dur = 700;
        function frame(now) {
            const t = Math.min((now - start) / dur, 1);
            const eased = 1 - Math.pow(1 - t, 3);
            const val = from + (target - from) * eased;
            el.textContent = isFloat ? val.toFixed(1) : Math.round(val).toLocaleString();
            if (t < 1) requestAnimationFrame(frame);
            else el.textContent = isFloat ? target.toFixed(1) : Math.round(target).toLocaleString();
        }
        requestAnimationFrame(frame);
    }

    function setKpis(data) {
        const k = data.kpis || {};
        const a = data.attention || {};
        const attentionTotal = a.total != null ? a.total : ((a.overdue || 0) + (a.due_soon || 0) + (a.urgent || 0));
        const map = {
            total: k.total, open: k.open, in_progress: k.in_progress,
            placed: k.placed, rejected: k.rejected,
            total_candidates: k.total_candidates, active_candidates: k.active_candidates,
            placement_rate: k.placement_rate, win_rate: k.win_rate,
            overdue: a.overdue, urgent: a.urgent, attention_total: attentionTotal,
        };
        Object.keys(map).forEach(function (key) {
            document.querySelectorAll('[data-kpi="' + key + '"]').forEach(function (el) {
                if (key === 'placement_rate' || key === 'win_rate') el.dataset.float = '1';
                animateValue(el, map[key] == null ? 0 : map[key]);
            });
        });
    }

    /* ── Chart builders ── */
    function doughnut(id, cfg) {
        const ctx = document.getElementById(id);
        if (!ctx) return;
        charts[id] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: cfg.labels,
                datasets: [{
                    data: cfg.data,
                    backgroundColor: cfg.colors,
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' },
                    d2CenterText: true,
                },
                animation: { animateRotate: true, duration: 800 },
            },
        });
    }

    function buildAll() {
        // Trend (line)
        const t = payload.monthly_trend || { labels: [], raised: [], placed: [] };
        const trendCtx = document.getElementById('chartTrend');
        if (trendCtx) {
            const g = trendCtx.getContext('2d');
            const fill = g.createLinearGradient(0, 0, 0, 300);
            fill.addColorStop(0, 'rgba(10,45,41,.22)');
            fill.addColorStop(1, 'rgba(10,45,41,0)');
            charts.chartTrend = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: t.labels,
                    datasets: [
                        { label: 'Raised', data: t.raised, borderColor: PALETTE.teal, backgroundColor: fill, fill: true, tension: .38, borderWidth: 2.5, pointRadius: 3, pointBackgroundColor: PALETTE.teal },
                        { label: 'Placed', data: t.placed, borderColor: PALETTE.gold, backgroundColor: 'transparent', fill: false, tension: .38, borderWidth: 2.5, pointRadius: 3, pointBackgroundColor: PALETTE.goldD },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'top', align: 'end' } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.05)' } },
                    },
                    animation: { duration: 800 },
                },
            });
        }

        // Status doughnut
        const s = payload.status_breakdown || { labels: [], data: [] };
        doughnut('chartStatus', { labels: s.labels, data: s.data, colors: STATUS_COLORS });

        // Funnel (horizontal bar)
        const f = payload.pipeline_funnel || { labels: [], data: [] };
        const funnelCtx = document.getElementById('chartFunnel');
        if (funnelCtx) {
            charts.chartFunnel = new Chart(funnelCtx, {
                type: 'bar',
                data: { labels: f.labels, datasets: [{ label: 'Candidates', data: f.data, backgroundColor: MULTI, borderRadius: 6, barThickness: 'flex', maxBarThickness: 28 }] },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.05)' } }, y: { grid: { display: false } } },
                    animation: { duration: 800 },
                },
            });
        }

        // Top clients (horizontal bar)
        const c = payload.top_clients || { labels: [], data: [] };
        const clientsCtx = document.getElementById('chartClients');
        if (clientsCtx) {
            charts.chartClients = new Chart(clientsCtx, {
                type: 'bar',
                data: { labels: c.labels, datasets: [{ label: 'Positions', data: c.data, backgroundColor: PALETTE.teal, borderRadius: 6, maxBarThickness: 24 }] },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.05)' } }, y: { grid: { display: false } } },
                    animation: { duration: 800 },
                },
            });
        }

        // Recruiter performance (grouped bar)
        const r = payload.recruiter_performance || { labels: [], total: [], placed: [] };
        const recCtx = document.getElementById('chartRecruiters');
        if (recCtx) {
            charts.chartRecruiters = new Chart(recCtx, {
                type: 'bar',
                data: {
                    labels: r.labels,
                    datasets: [
                        { label: 'Positions', data: r.total, backgroundColor: PALETTE.teal, borderRadius: 5, maxBarThickness: 26 },
                        { label: 'Placed', data: r.placed, backgroundColor: PALETTE.gold, borderRadius: 5, maxBarThickness: 26 },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', align: 'end' } },
                    scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.05)' } } },
                    animation: { duration: 800 },
                },
            });
        }

        // Priority
        const p = payload.priority_breakdown || { labels: [], data: [] };
        doughnut('chartPriority', { labels: p.labels, data: p.data, colors: [PALETTE.goldD, PALETTE.goldDeep, PALETTE.teal, PALETTE.tealGlow, PALETTE.slate] });

        // Job type
        const j = payload.job_type_breakdown || { labels: [], data: [] };
        doughnut('chartJobType', { labels: j.labels, data: j.data, colors: [PALETTE.teal, PALETTE.gold, PALETTE.tealGlow, PALETTE.slate] });

        // Source
        const src = payload.source_breakdown || { labels: [], data: [] };
        doughnut('chartSource', { labels: src.labels, data: src.data, colors: MULTI });
    }

    function updateChart(name, updater) {
        if (!charts[name]) return;
        updater(charts[name]);
        charts[name].update();
    }

    function refreshCharts(data) {
        payload = data;
        updateChart('chartTrend', function (ch) {
            const t = data.monthly_trend; ch.data.labels = t.labels;
            ch.data.datasets[0].data = t.raised; ch.data.datasets[1].data = t.placed;
        });
        updateChart('chartStatus', function (ch) { ch.data.labels = data.status_breakdown.labels; ch.data.datasets[0].data = data.status_breakdown.data; });
        updateChart('chartFunnel', function (ch) { ch.data.labels = data.pipeline_funnel.labels; ch.data.datasets[0].data = data.pipeline_funnel.data; });
        updateChart('chartClients', function (ch) { ch.data.labels = data.top_clients.labels; ch.data.datasets[0].data = data.top_clients.data; });
        updateChart('chartRecruiters', function (ch) {
            ch.data.labels = data.recruiter_performance.labels;
            ch.data.datasets[0].data = data.recruiter_performance.total;
            ch.data.datasets[1].data = data.recruiter_performance.placed;
        });
        updateChart('chartPriority', function (ch) { ch.data.labels = data.priority_breakdown.labels; ch.data.datasets[0].data = data.priority_breakdown.data; });
        updateChart('chartJobType', function (ch) { ch.data.labels = data.job_type_breakdown.labels; ch.data.datasets[0].data = data.job_type_breakdown.data; });
        updateChart('chartSource', function (ch) { ch.data.labels = data.source_breakdown.labels; ch.data.datasets[0].data = data.source_breakdown.data; });
    }

    /* ── Consolidated data table ── */
    function escapeHtml(str) {
        return String(str == null ? '' : str).replace(/[&<>"']/g, function (m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
        });
    }

    function renderConsolidatedTable(rows) {
        const body = document.getElementById('consolidatedPositionsBody');
        const empty = document.getElementById('consolidatedEmpty');
        if (!body) return;
        if (!rows || !rows.length) {
            body.innerHTML = '';
            if (empty) empty.hidden = false;
            return;
        }
        if (empty) empty.hidden = true;
        body.innerHTML = rows.map(function (row) {
            const url = row.url || (window.APP_BASE_URL || '') + '/tracker/info/' + row.id;
            const prio = escapeHtml(row.priority);
            return '<tr>'
                + '<td>#' + escapeHtml(row.id) + '</td>'
                + '<td><a class="dash2-pos-link" href="' + url + '">' + escapeHtml(row.position) + '</a></td>'
                + '<td>' + escapeHtml(row.client) + '</td>'
                + '<td>' + escapeHtml(row.recruiter) + '</td>'
                + '<td>' + escapeHtml(row.month) + '</td>'
                + '<td><span class="dash2-prio dash2-prio--' + prio + '">' + prio + '</span></td>'
                + '<td><span class="dash2-badge dash2-badge--' + escapeHtml(row.status_group) + '">' + escapeHtml(row.status) + '</span></td>'
                + '</tr>';
        }).join('');
    }

    let consolidatedPage = 1;
    let consolidatedDebounce;

    function fetchConsolidated(page) {
        consolidatedPage = page || 1;
        const form = document.getElementById('dashFilters');
        if (!form) return;
        const params = new URLSearchParams(new FormData(form));
        params.set('page', String(consolidatedPage));

        fetch(window.DASHBOARD_POSITIONS_URL + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                renderConsolidatedTable(data.items);
                const pagination = document.getElementById('consolidatedPagination');
                const countText = document.getElementById('consolidatedCountText');
                if (pagination) pagination.innerHTML = data.pagination || '';
                if (countText) countText.textContent = data.count_text || '';
                if (pagination) {
                    pagination.querySelectorAll('a').forEach(function (link) {
                        link.addEventListener('click', function (e) {
                            const href = link.getAttribute('href');
                            if (!href) return;
                            e.preventDefault();
                            const pageMatch = href.match(/[?&]page=(\d+)/);
                            fetchConsolidated(pageMatch ? parseInt(pageMatch[1], 10) : 1);
                        });
                    });
                }
            })
            .catch(function () { /* keep previous view */ });
    }

    /* ── AJAX filtering ── */
    const form = document.getElementById('dashFilters');
    const loading = document.getElementById('dashLoading');
    const filterToggle = document.getElementById('dashFilterToggle');
    const filterPanel = document.getElementById('dashFilterPanel');
    const filterCount = document.getElementById('dashFilterCount');
    const chipsWrap = document.getElementById('dashChips');

    function closeFilterPanel() {
        if (!filterPanel || filterPanel.hidden) return;
        filterPanel.hidden = true;
        if (filterToggle) filterToggle.classList.remove('is-active');
    }

    function toggleFilterPanel() {
        if (!filterPanel) return;
        const willOpen = filterPanel.hidden;
        filterPanel.hidden = !willOpen;
        if (filterToggle) filterToggle.classList.toggle('is-active', willOpen);
    }

    function syncFilterChips() {
        if (!form || !chipsWrap) return;
        const chips = [];
        form.querySelectorAll('select').forEach(function (sel) {
            if (sel.value) {
                const label = sel.dataset.label || sel.name;
                const text = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : sel.value;
                chips.push({ key: sel.name, label: label, text: text });
            }
        });
        const searchInput = form.querySelector('input[name="search"]');
        if (searchInput && searchInput.value.trim()) {
            chips.push({ key: 'search', label: 'Search', text: searchInput.value.trim() });
        }

        chipsWrap.innerHTML = chips.map(function (c) {
            return '<span class="dash2-chip">' + escapeHtml(c.label) + ': <strong>' + escapeHtml(c.text) + '</strong>'
                + '<button type="button" class="dash2-chip__remove" data-clear="' + escapeHtml(c.key) + '" aria-label="Remove filter">&times;</button></span>';
        }).join('');
        chipsWrap.hidden = chips.length === 0;

        if (filterCount) {
            if (chips.length) { filterCount.textContent = String(chips.length); filterCount.hidden = false; }
            else { filterCount.hidden = true; }
        }

        chipsWrap.querySelectorAll('[data-clear]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const field = form.querySelector('[name="' + btn.dataset.clear + '"]');
                if (field) field.value = '';
                fetchData();
            });
        });
    }

    function fetchData() {
        if (loading) loading.classList.add('is-active');
        const params = new URLSearchParams(new FormData(form));
        const url = window.DASHBOARD_DATA_URL + '?' + params.toString();
        const newUrl = window.location.pathname + '?' + params.toString();
        window.history.replaceState({}, '', newUrl);
        syncFilterChips();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                setKpis(data);
                refreshCharts(data);
                fetchConsolidated(1);
            })
            .catch(function () { /* keep previous view on error */ })
            .finally(function () { if (loading) loading.classList.remove('is-active'); });
    }

    if (form) {
        form.addEventListener('submit', function (e) { e.preventDefault(); fetchData(); });
        form.querySelectorAll('select').forEach(function (sel) {
            sel.addEventListener('change', fetchData);
        });
        let searchTimer = null;
        const searchInput = form.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(fetchData, 450);
            });
        }
        const resetBtn = document.getElementById('dashReset');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                form.reset();
                form.querySelectorAll('select').forEach(function (s) { s.value = ''; });
                const si = form.querySelector('input[name="search"]'); if (si) si.value = '';
                closeFilterPanel();
                fetchData();
            });
        }
        const applyPanelBtn = document.getElementById('dashApplyPanel');
        if (applyPanelBtn) {
            applyPanelBtn.addEventListener('click', function () {
                closeFilterPanel();
                fetchData();
            });
        }
        const exportBtn = document.getElementById('dashExport');
        if (exportBtn) {
            exportBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const params = new URLSearchParams(new FormData(form));
                window.location.href = window.DASHBOARD_EXPORT_URL + '?' + params.toString();
            });
        }
    }

    if (filterToggle && filterPanel) {
        filterToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleFilterPanel();
        });
        document.addEventListener('click', function (e) {
            if (filterPanel.hidden) return;
            if (filterPanel.contains(e.target) || filterToggle.contains(e.target)) return;
            closeFilterPanel();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeFilterPanel();
        });
    }

    /* ── KPI detail modal ── */
    const kpiModal = document.getElementById('kpiModal');
    const kpiModalTitle = document.getElementById('kpiModalTitle');
    const kpiModalSubtitle = document.getElementById('kpiModalSubtitle');
    const kpiModalBody = document.getElementById('kpiModalBody');
    const kpiModalLoading = document.getElementById('kpiModalLoading');

    function concernClass(text) {
        if (text.indexOf('Overdue') === 0 || text.indexOf('Urgent') === 0) return 'dash2-concern';
        if (text.indexOf('Due this week') === 0) return 'dash2-concern dash2-concern--warn';
        return 'dash2-concern dash2-concern--info';
    }

    function renderKpiItems(items, kpi) {
        if (!items || !items.length) {
            return '<div class="dash2-modal__empty">No positions in this category for the current filters.</div>';
        }
        return '<ul class="dash2-detail-list">' + items.map(function (item) {
            const concerns = (item.concerns && item.concerns.length)
                ? '<div class="dash2-concerns">' + item.concerns.map(function (c) {
                    return '<div class="' + concernClass(c) + '"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>' + escapeHtml(c) + '</span></div>';
                }).join('') + '</div>'
                : '';
            return '<li class="dash2-detail-item">'
                + '<div class="dash2-detail-item__top">'
                + '<a class="dash2-detail-item__title" href="' + escapeHtml(item.url) + '">' + escapeHtml(item.position) + '</a>'
                + '<span class="dash2-badge dash2-badge--' + escapeHtml(item.status_group) + '">' + escapeHtml(item.status) + '</span>'
                + '</div>'
                + '<div class="dash2-detail-item__meta">'
                + '<span>#' + escapeHtml(item.id) + '</span>'
                + '<span>' + escapeHtml(item.client) + '</span>'
                + '<span>' + escapeHtml(item.recruiter) + '</span>'
                + '<span>' + escapeHtml(item.month) + '</span>'
                + '<span class="dash2-prio dash2-prio--' + escapeHtml(item.priority) + '">' + escapeHtml(item.priority) + '</span>'
                + (item.deadline && item.deadline !== '—' ? '<span>Due ' + escapeHtml(item.deadline) + '</span>' : '')
                + (item.candidate_count ? '<span>' + item.candidate_count + ' candidate(s)</span>' : '')
                + '</div>'
                + concerns
                + '</li>';
        }).join('') + '</ul>';
    }

    function openKpiModal(kpiKey) {
        if (!kpiModal || !form) return;
        kpiModal.hidden = false;
        kpiModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        kpiModalTitle.textContent = 'Loading...';
        kpiModalSubtitle.textContent = '';
        kpiModalBody.innerHTML = '<div class="dash2-modal__loading" id="kpiModalLoading">Loading positions...</div>';

        const params = new URLSearchParams(new FormData(form));
        fetch(window.DASHBOARD_KPI_URL + '/' + encodeURIComponent(kpiKey) + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                kpiModalTitle.textContent = data.title + ' (' + data.count + ')';
                kpiModalSubtitle.textContent = data.subtitle || '';
                kpiModalBody.innerHTML = renderKpiItems(data.items, data.kpi);
            })
            .catch(function () {
                kpiModalBody.innerHTML = '<div class="dash2-modal__empty">Could not load details. Please try again.</div>';
            });
    }

    function closeKpiModal() {
        if (!kpiModal) return;
        kpiModal.hidden = true;
        kpiModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.dash2-kpi--clickable').forEach(function (card) {
        card.addEventListener('click', function () { openKpiModal(card.dataset.kpiKey); });
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openKpiModal(card.dataset.kpiKey); }
        });
    });

    if (kpiModal) {
        kpiModal.querySelectorAll('[data-close-modal]').forEach(function (el) {
            el.addEventListener('click', closeKpiModal);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !kpiModal.hidden) closeKpiModal();
        });
    }

    /* ── Init ── */
    function init() {
        buildAll();
        setKpis(payload);
        fetchConsolidated(1);
        syncFilterChips();

        const consolidatedSearch = document.getElementById('consolidatedSearch');
        const mainSearch = form ? form.querySelector('input[name="search"]') : null;
        if (consolidatedSearch && mainSearch) {
            consolidatedSearch.addEventListener('input', function () {
                mainSearch.value = consolidatedSearch.value;
                clearTimeout(consolidatedDebounce);
                consolidatedDebounce = setTimeout(function () {
                    fetchData();
                }, 400);
            });
            mainSearch.addEventListener('input', function () {
                consolidatedSearch.value = mainSearch.value;
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
