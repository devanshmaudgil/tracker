@forelse($regions as $index => $region)
    <tr class="region-row"
        data-id="{{ $region->id }}"
        data-region="{{ $region->region }}"
        data-city="{{ $region->city ?? '' }}">
        <td><span class="crud-index">{{ $regions->firstItem() + $index }}</span></td>
        <td class="is-left"><span class="crud-name">{{ $region->city ?? '—' }}</span></td>
        <td>{{ $region->region }}</td>
        <td>
            <div class="crud-actions">
                <button type="button" class="crud-act" onclick="editRecord({{ $region->id }})">Edit</button>
                <form method="POST" action="{{ route('locations.destroy', $region->id) }}" style="display:inline;" onsubmit="return confirm('Delete this location?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="crud-act crud-act--danger">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4">
            <div class="crud-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <h3>No locations found</h3>
                <p>Try a different search or <a href="#" onclick="openModal(); return false;">add a location</a>.</p>
            </div>
        </td>
    </tr>
@endforelse
