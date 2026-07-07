@php
    $initials = function ($name) {
        $parts = preg_split('/\s+/', trim($name));
        $a = strtoupper(substr($parts[0] ?? '?', 0, 1));
        $b = strtoupper(substr($parts[1] ?? '', 0, 1));
        return $b ? $a . $b : $a;
    };

    $locationLabel = function ($candidate) {
        if ($candidate->location) {
            return $candidate->location->city
                ? trim($candidate->location->city . ($candidate->location->region ? ', ' . $candidate->location->region : ''))
                : $candidate->location->region;
        }
        return $candidate->location_text ?? '';
    };
@endphp

@forelse($candidates as $candidate)
    @php
        $loc = $locationLabel($candidate);
        $searchBlob = strtolower(implode(' ', array_filter([
            $candidate->full_name,
            $candidate->email,
            $candidate->phone,
            $loc,
            $candidate->work_status,
            $candidate->current_company,
            $candidate->pay_rate,
            $candidate->placement_pay_rate,
            $candidate->summary,
            $candidate->remarks,
            $candidate->agency_name,
            $candidate->agency_poc,
            $candidate->agency_poc_phone,
        ])));
    @endphp
    <tr class="cand-row"
        data-id="{{ $candidate->id }}"
        data-search="{{ $searchBlob }}"
        data-work-status="{{ $candidate->work_status ?? '' }}"
        data-name="{{ $candidate->full_name }}"
        data-email="{{ $candidate->email }}"
        data-phone="{{ $candidate->phone ?? '' }}"
        data-location="{{ $loc }}"
        data-status="{{ $candidate->work_status ?? '' }}"
        data-company="{{ $candidate->current_company ?? '' }}"
        data-rate="{{ $candidate->pay_rate ?? '' }}"
        data-placement-rate="{{ $candidate->placement_pay_rate ?? '' }}"
        data-agency="{{ $candidate->agency_name ?? '' }}"
        data-agency-poc="{{ $candidate->agency_poc ?? '' }}"
        data-agency-phone="{{ $candidate->agency_poc_phone ?? '' }}"
        data-summary="{{ $candidate->summary ?? '' }}"
        data-remarks="{{ e($candidate->remarks ?? '') }}"
        data-resume="{{ $candidate->resume_file_url ?? '' }}"
        data-jobs="{{ $candidate->trackerCandidates->map(fn($tc) => $tc->tracker_info_id)->implode(',') }}"
        data-initials="{{ $initials($candidate->full_name) }}"
        data-edit-url="{{ route('candidates.edit', $candidate->id) }}">
        <td class="cand-col-person">
            <div class="cand-person">
                <div class="cand-avatar">{{ $initials($candidate->full_name) }}</div>
                <div>
                    <div class="cand-person__name" data-hl data-hl-mode="seq" data-raw="{{ $candidate->full_name }}">{{ $candidate->full_name }}</div>
                    <div class="cand-person__email" data-hl data-hl-mode="seq" data-raw="{{ $candidate->email }}">{{ $candidate->email }}</div>
                </div>
            </div>
        </td>
        <td data-hl data-hl-mode="seq" data-raw="{{ $candidate->phone ?? '' }}">{{ $candidate->phone ?? '—' }}</td>
        <td data-hl data-hl-mode="seq" data-raw="{{ $loc }}">{{ $loc ?: '—' }}</td>
        <td>
            @if($candidate->work_status)
                <span class="cand-badge">{{ $candidate->work_status }}</span>
            @else
                <span class="cand-badge cand-badge--muted">—</span>
            @endif
        </td>
        <td data-hl data-hl-mode="seq" data-raw="{{ $candidate->current_company ?? '' }}">{{ $candidate->current_company ?? '—' }}</td>
        <td>
            @if($candidate->pay_rate)
                <span data-hl data-hl-mode="seq" data-raw="{{ $candidate->pay_rate }}">{{ $candidate->pay_rate }}</span>
            @else
                —
            @endif
        </td>
        <td>
            @if($candidate->trackerCandidates->count() > 0)
                @foreach($candidate->trackerCandidates as $tc)
                    <a href="{{ route('tracker.info', $tc->tracker_info_id) }}" class="cand-job-link">#{{ $tc->tracker_info_id }}</a>
                @endforeach
            @else
                <span style="color:var(--c-muted);">—</span>
            @endif
        </td>
        <td>
            @if($candidate->resume_file_url)
                <a href="{{ $candidate->resume_file_url }}" target="_blank" class="cand-resume-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    CV
                </a>
            @else
                —
            @endif
        </td>
        <td>
            <div class="cand-actions">
                <button type="button" class="cand-view-btn" onclick="openCandidateDetail(this.closest('.cand-row'))" aria-label="View details" title="View details">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
                <a href="{{ route('candidates.edit', $candidate->id) }}" class="cand-act">Edit</a>
                <form method="POST" action="{{ route('candidates.destroy', $candidate->id) }}" style="display:inline;" onsubmit="return confirm('Delete this candidate?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="cand-act cand-act--danger">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr class="cand-row-empty">
        <td colspan="9">
            <div class="cand-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <h3>No candidates found</h3>
                <p>Try a different search or <a href="#" onclick="openCandidateModal(); return false;">add a candidate</a>.</p>
            </div>
        </td>
    </tr>
@endforelse
