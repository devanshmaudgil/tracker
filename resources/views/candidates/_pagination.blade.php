@if($candidates->total() > 0)
    <div class="cand-pagination">
        <div class="cand-pagination__info" id="candPaginationInfo">
            @if($candidates->total() > 0)
                Showing <strong>{{ $candidates->firstItem() }}</strong>–<strong>{{ $candidates->lastItem() }}</strong> of <strong>{{ $candidates->total() }}</strong> candidates
            @else
                No candidates found
            @endif
        </div>
        @if($candidates->hasPages())
            {{ $candidates->appends(request()->query())->links('vendor.pagination.custom') }}
        @endif
    </div>
@endif
