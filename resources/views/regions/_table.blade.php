@forelse($regions as $region)
    <tr class="region-row" 
        data-id="{{ $region->id }}"
        data-region="{{ $region->region }}"
        data-city="{{ $region->city ?? '' }}">
        <td>{{ $region->city ?? '-' }}</td>
        <td>{{ $region->region }}</td>
        <td>
            <div class="action-buttons">
                <button type="button" class="btn btn-secondary btn-sm" onclick="editRecord({{ $region->id }})" title="Edit">Edit</button>
                <form method="POST" action="{{ route('regions.destroy', $region->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this region?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr id="emptyRow">
        <td colspan="3" style="text-align: center; padding: 30px; color: #666;">No regions found. <a href="#" onclick="openModal(); return false;" style="color: #f1cd86;">Add your first region</a></td>
    </tr>
@endforelse
