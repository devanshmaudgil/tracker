<script>
(function () {
    const searchInput = document.getElementById('candSearchInput');
    const searchWrap = document.getElementById('candSearchWrap');
    const clearBtn = document.getElementById('candSearchClear');
    const resultMeta = document.getElementById('candSearchMeta');
    const tbody = document.getElementById('candTableBody');
    const tableCard = document.getElementById('candTableCard');
    const paginationContainer = document.getElementById('paginationContainer');
    const chips = document.querySelectorAll('.cand-chip[data-filter]');
    const indexUrl = '{{ route('candidates.index') }}';

    if (!searchInput || !tbody) return;

    let activeStatusFilter = '{{ request('work_status') }}';
    let debounceTimer;
    let fetchController = null;

    function escapeHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function highlightSequential(text, query) {
        if (!text) return '';
        if (!query) return escapeHtml(text);

        const src = String(text);
        const q = query.toLowerCase();
        let qi = 0;
        let out = '';
        let hiIndex = 0;

        for (let i = 0; i < src.length; i++) {
            const ch = src[i];
            if (qi < q.length && ch.toLowerCase() === q[qi]) {
                out += '<mark class="cand-hl" style="--hi:' + hiIndex + '">' + escapeHtml(ch) + '</mark>';
                qi++;
                hiIndex++;
            } else {
                out += escapeHtml(ch);
            }
        }
        return out;
    }

    function getRows() {
        return Array.from(tbody.querySelectorAll('tr.cand-row'));
    }

    function updateHighlights(row, query) {
        row.querySelectorAll('[data-hl]').forEach(function (el) {
            const raw = el.dataset.raw || el.textContent;
            const mode = el.dataset.hlMode || 'seq';
            el.innerHTML = mode === 'sub'
                ? highlightSequential(raw, query)
                : highlightSequential(raw, query);
        });
    }

    function applyHighlights() {
        const query = searchInput.value.trim();
        searchWrap.classList.toggle('is-active', query.length > 0);
        clearBtn.classList.toggle('is-visible', query.length > 0);

        getRows().forEach(function (row) {
            updateHighlights(row, query);
        });
    }

    function updateCountText(text) {
        if (!resultMeta || !text) return;
        const match = text.match(/Showing (\d+) to (\d+) of (\d+)/i);
        if (match) {
            resultMeta.innerHTML = 'Showing <strong>' + match[1] + '</strong>–<strong>' + match[2] + '</strong> of <strong>' + match[3] + '</strong> candidates';
        } else {
            resultMeta.textContent = text;
        }
    }

    function fetchCandidates(url) {
        if (fetchController) {
            fetchController.abort();
        }
        fetchController = new AbortController();

        if (tableCard) {
            tableCard.classList.add('is-loading');
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: fetchController.signal,
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                tbody.innerHTML = data.table;
                paginationContainer.innerHTML = data.pagination || '';
                updateCountText(data.count_text);
                applyHighlights();

                if (url !== window.location.href) {
                    window.history.replaceState({}, '', url);
                }
            })
            .catch(function (error) {
                if (error.name !== 'AbortError') {
                    console.error('Error:', error);
                }
            })
            .finally(function () {
                if (tableCard) {
                    tableCard.classList.remove('is-loading');
                }
            });
    }

    function buildUrl(pageUrl) {
        const url = new URL(pageUrl || indexUrl, window.location.origin);
        const search = searchInput.value.trim();
        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }
        if (activeStatusFilter) {
            url.searchParams.set('work_status', activeStatusFilter);
        } else {
            url.searchParams.delete('work_status');
        }
        return url.toString();
    }

    function fetchFromFilters(pageUrl) {
        fetchCandidates(buildUrl(pageUrl));
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            fetchFromFilters(indexUrl);
        }, 350);
    });

    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            searchInput.value = '';
            fetchFromFilters(indexUrl);
            searchInput.blur();
        }
    });

    clearBtn.addEventListener('click', function () {
        searchInput.value = '';
        searchInput.focus();
        fetchFromFilters(indexUrl);
    });

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            const val = chip.dataset.filter || '';
            if (activeStatusFilter === val && val !== '') {
                activeStatusFilter = '';
                chips.forEach(function (c) { c.classList.remove('is-active'); });
                chips[0]?.classList.add('is-active');
            } else {
                activeStatusFilter = val;
                chips.forEach(function (c) {
                    c.classList.toggle('is-active', (c.dataset.filter || '') === val);
                });
            }
            fetchFromFilters(indexUrl);
        });
    });

    document.addEventListener('click', function (e) {
        const paginationLink = e.target.closest('#paginationContainer a');
        if (paginationLink) {
            e.preventDefault();
            fetchFromFilters(paginationLink.getAttribute('href'));
        }
    });

    applyHighlights();
})();

function setDetailText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value && String(value).trim() ? value : '—';
}

function openCandidateDetail(row) {
    if (!row) return;
    const d = row.dataset;
    document.getElementById('candDetailAvatar').textContent = d.initials || '?';
    document.getElementById('candDetailName').textContent = d.name || 'Candidate';
    document.getElementById('candDetailEmail').textContent = d.email || '';
    setDetailText('candDetailPhone', d.phone);
    setDetailText('candDetailLocation', d.location);
    setDetailText('candDetailStatus', d.status);
    setDetailText('candDetailCompany', d.company);
    setDetailText('candDetailRate', d.rate);
    setDetailText('candDetailPlacementRate', d.placementRate);
    setDetailText('candDetailAgency', d.agency);
    setDetailText('candDetailAgencyPoc', d.agencyPoc);
    setDetailText('candDetailAgencyPhone', d.agencyPhone);
    setDetailText('candDetailSummary', d.summary);
    setDetailText('candDetailRemarks', d.remarks);

    const jobsEl = document.getElementById('candDetailJobs');
    if (jobsEl) {
        const jobs = (d.jobs || '').split(',').filter(Boolean);
        jobsEl.innerHTML = jobs.length
            ? jobs.map(function (id) {
                return '<a href="/tracker/info/' + id + '" class="cand-job-link">#' + id + '</a>';
            }).join(' ')
            : '—';
    }

    const resumeEl = document.getElementById('candDetailResume');
    if (resumeEl) {
        resumeEl.innerHTML = d.resume
            ? '<a href="' + d.resume + '" target="_blank" class="cand-resume-btn">View Resume</a>'
            : '—';
    }

    const editBtn = document.getElementById('candDetailEditBtn');
    if (editBtn) editBtn.href = d.editUrl || '#';

    const overlay = document.getElementById('candDetailOverlay');
    if (overlay) {
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
}

function closeCandidateDetail() {
    const overlay = document.getElementById('candDetailOverlay');
    if (overlay) {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
    }
}

document.getElementById('candDetailOverlay')?.addEventListener('click', function (e) {
    if (e.target === this) closeCandidateDetail();
});

function openCandidateModal() {
    const overlay = document.getElementById('candidateModal');
    if (overlay) {
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        const first = overlay.querySelector('input[name="full_name"]');
        if (first) setTimeout(function () { first.focus(); }, 300);
    }
}

function closeCandidateModal() {
    const overlay = document.getElementById('candidateModal');
    if (overlay) {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        const form = document.getElementById('candidateForm');
        if (form) form.reset();
    }
}

document.getElementById('candidateModal')?.addEventListener('click', function (e) {
    if (e.target === this) closeCandidateModal();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeCandidateDetail();
        closeCandidateModal();
    }
});

@if($errors->any())
    document.addEventListener('DOMContentLoaded', openCandidateModal);
@endif
</script>
