@forelse($trackerInfos as $info)
    <tr data-id="{{ $info->id }}">
        <td>{{ $info->id }}</td>
        <td>{{ $info->month->month ?? '-' }}</td>
        <td>{{ $info->prd ? $info->prd->format('d-M-Y') : '-' }}</td>
        <td>{{ $info->submission_deadline ? $info->submission_deadline->format('d-M-Y') : '-' }}</td>
        <td>{{ $info->client->client ?? '-' }}</td>
        <td>
            @if($info->region)
                @if($info->region->city)
                    {{ $info->region->city }}, {{ $info->region->region }}
                @else
                    {{ $info->region->region }}
                @endif
            @else
                -
            @endif
        </td>
        <td>{{ $info->position ?? '-' }}</td>
        <td>{{ $info->leadRecruiter->username ?? '-' }}</td>
        <td title="{{ $info->jobStatus->status ?? 'Demand Raised' }}">
            <span style="cursor: help; border-bottom: 1px dotted #666;">
                {{ $info->jobStatus->status_initial ?? 'DR' }}
            </span>
        </td>
        <td>
            <div class="action-buttons">
                <a href="{{ route('tracker.info', $info->id) }}" class="btn btn-primary btn-sm" title="View Details">View</a>
                <a href="{{ route('tracker.edit', $info->id) }}" class="btn btn-secondary btn-sm" title="Edit">Edit</a>
                <form method="POST" action="{{ route('tracker.destroy', $info->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this record?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" style="text-align: center; padding: 30px; color: #666;">No records found. <a href="{{ route('tracker.create') }}" style="color: #f1cd86;">Add your first record</a></td>
    </tr>
@endforelse
