@php
    /** @var \Illuminate\Support\Collection $regions */
    $selectedRegionIds = collect(old('region_ids', $selectedRegionIds ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $regionLabel = fn ($region) => $region->city
        ? $region->city . ', ' . $region->region
        : $region->region;
@endphp

<div class="form-group">
    <label for="region_search">Job Location(s)</label>
    <p style="margin:-4px 0 8px; font-size:12px; color:#6b7280;">Add one location, or multiple for India requisitions opened across cities.</p>

    <div class="region-ms" id="regionMs">
        <div class="region-ms__chips" id="regionMsChips"></div>
        <div class="region-ms__control">
            <input type="text" id="region_search" placeholder="Search and add a location..." autocomplete="off" class="region-ms__input">
            <div id="region_dropdown" class="region-ms__dropdown"></div>
        </div>
        <div id="regionMsHidden"></div>
    </div>

    <select id="region_source" style="display:none;">
        @foreach($regions as $region)
            <option value="{{ $region->id }}" data-region="{{ $region->region }}" data-city="{{ $region->city ?? '' }}">{{ $regionLabel($region) }}</option>
        @endforeach
    </select>

    @error('region_ids')<div style="color:#dc3545; margin-top:5px; font-size:14px;">{{ $message }}</div>@enderror
    @error('region_id')<div style="color:#dc3545; margin-top:5px; font-size:14px;">{{ $message }}</div>@enderror
</div>

<style>
    .region-ms {
        position: relative;
    }
    .region-ms__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 8px;
    }
    .region-ms__chips:empty { display: none; }
    .region-ms__chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        background: rgba(241, 205, 134, 0.18);
        border: 1px solid rgba(10, 45, 41, 0.12);
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        color: #0a2d29;
    }
    .region-ms__chip button {
        border: none;
        background: transparent;
        color: #0a2d29;
        cursor: pointer;
        font-size: 15px;
        line-height: 1;
        padding: 0;
    }
    .region-ms__chip button:hover { color: #dc2626; }
    .region-ms__input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    .region-ms__input:focus {
        outline: none;
        border-color: #f1cd86;
        box-shadow: 0 0 0 3px rgba(241, 205, 134, 0.1);
    }
    .region-ms__dropdown {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 4px 4px;
        max-height: 220px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .region-ms__dropdown div {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        text-align: left;
    }
    .region-ms__dropdown div:hover {
        background-color: #f1cd86;
        color: #0a2d29;
    }
</style>

<script>
(function () {
    const source = document.getElementById('region_source');
    const searchInput = document.getElementById('region_search');
    const dropdown = document.getElementById('region_dropdown');
    const chipsWrap = document.getElementById('regionMsChips');
    const hiddenWrap = document.getElementById('regionMsHidden');
    if (!source || !searchInput) return;

    const options = Array.from(source.querySelectorAll('option')).map(o => ({
        id: o.value,
        label: o.textContent.trim(),
        region: (o.dataset.region || '').toLowerCase(),
        city: (o.dataset.city || '').toLowerCase(),
    }));

    const initial = @json(array_values(array_unique($selectedRegionIds)));
    const selected = new Map();

    function render() {
        chipsWrap.innerHTML = '';
        hiddenWrap.innerHTML = '';
        selected.forEach((label, id) => {
            const chip = document.createElement('span');
            chip.className = 'region-ms__chip';
            chip.textContent = label;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.textContent = '\u00d7';
            remove.addEventListener('click', () => {
                selected.delete(id);
                render();
            });
            chip.appendChild(remove);
            chipsWrap.appendChild(chip);

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'region_ids[]';
            hidden.value = id;
            hiddenWrap.appendChild(hidden);
        });
    }

    function addRegion(id, label) {
        if (!selected.has(id)) {
            selected.set(id, label);
            render();
        }
    }

    initial.forEach(id => {
        const match = options.find(o => String(o.id) === String(id));
        if (match) addRegion(match.id, match.label);
    });

    function filter(term) {
        const q = term.toLowerCase();
        dropdown.innerHTML = '';
        if (!q) { dropdown.style.display = 'none'; return; }

        const matches = options.filter(o =>
            !selected.has(o.id) &&
            (o.region.includes(q) || o.city.includes(q) || o.label.toLowerCase().includes(q))
        );

        if (matches.length === 0) { dropdown.style.display = 'none'; return; }

        dropdown.style.display = 'block';
        matches.slice(0, 50).forEach(o => {
            const div = document.createElement('div');
            div.textContent = o.label;
            div.addEventListener('click', () => {
                addRegion(o.id, o.label);
                searchInput.value = '';
                dropdown.style.display = 'none';
            });
            dropdown.appendChild(div);
        });
    }

    searchInput.addEventListener('input', function () { filter(this.value); });
    searchInput.addEventListener('focus', function () { if (this.value) filter(this.value); });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#regionMs')) dropdown.style.display = 'none';
    });
})();
</script>
