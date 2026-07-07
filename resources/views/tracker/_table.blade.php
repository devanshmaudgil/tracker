@forelse($trackerInfos as $info)
    @php
        $regionLabel = fn ($region) => $region->city ? $region->city . ', ' . $region->region : $region->region;
        if ($info->regions->isNotEmpty()) {
            $locationText = $info->regions->map($regionLabel)->implode(' | ');
        } elseif ($info->region) {
            $locationText = $regionLabel($info->region);
        } else {
            $locationText = $info->country ?? '-';
        }
    @endphp
    <tr data-id="{{ $info->id }}">
        <td><span class="cell-id">#{{ $info->id }}</span></td>
        <td>{{ $info->month->month ?? '-' }}</td>
        <td>{{ $info->prd ? $info->prd->format('d-M-Y') : '-' }}</td>
        <td>{{ $info->submission_deadline ? $info->submission_deadline->format('d-M-Y') : '-' }}</td>
        <td>
            <span class="cell-position">{{ $info->position ?? '-' }}</span>
            @php
                $placedCount = $info->placed_candidates_count ?? 0;
                $activeCount = $info->active_candidates_count ?? 0;
            @endphp
            @if($placedCount > 0 || $activeCount > 0)
                <span class="cand-mix" title="Candidate pipeline for this requisition">
                    @if($placedCount > 0)<span class="cand-mix__placed">{{ $placedCount }} placed</span>@endif
                    @if($placedCount > 0 && $activeCount > 0)<span class="cand-mix__dot">·</span>@endif
                    @if($activeCount > 0)<span class="cand-mix__active">{{ $activeCount }} in pipeline</span>@endif
                </span>
            @endif
        </td>
        <td>
            <span class="cell-location" title="{{ $locationText }}">{{ $locationText }}</span>
        </td>
        <td><span class="cell-client">{{ $info->client->client ?? '-' }}</span></td>
        <td>{{ $info->isUnserved() ? '-' : ($info->leadRecruiter->username ?? '-') }}</td>
        <td title="{{ $info->jobStatus->status ?? 'Demand Raised' }}">
            <span class="status-badge">{{ $info->jobStatus->status_initial ?? 'DR' }}</span>
        </td>
        <td>
            <div class="action-buttons">
                <button type="button"
                    class="action-btn action-remarks{{ $info->remarks ? ' has-remarks' : '' }}"
                    title="{{ $info->remarks ? 'Edit remarks' : 'Add remarks' }}"
                    data-remarks-btn
                    data-id="{{ $info->id }}"
                    data-position="{{ $info->position ?? 'Position' }}"
                    data-remarks="{{ e($info->remarks ?? '') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </button>
                <a href="{{ route('tracker.info', $info->id) }}" class="action-btn action-view" title="View Details">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <a href="{{ route('tracker.edit', $info->id) }}" class="action-btn action-edit" title="Edit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>
                <form method="POST" action="{{ route('tracker.destroy', $info->id) }}" class="action-form" onsubmit="return confirm('Are you sure you want to delete this record?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn action-delete" title="Delete">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/></svg>
            <p>No records found.</p>
            <a href="{{ route('tracker.create') }}" class="empty-link">Add your first record</a>
        </td>
    </tr>
@endforelse
