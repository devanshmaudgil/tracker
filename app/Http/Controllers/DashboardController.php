<?php

namespace App\Http\Controllers;

use App\Exports\DashboardExport;
use App\Models\Client;
use App\Models\Month;
use App\Models\StaffUser;
use App\Models\TrackerCandidate;
use App\Models\TrackerInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    /** Job statuses that represent an actively-worked (in progress) position. */
    private const IN_PROGRESS_STATUSES = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16];

    private const STATUS_PLACED = 17;

    private const STATUS_REJECTED = 18;

    private const STATUS_OPEN = 1;

    public function index(Request $request)
    {
        return view('dashboard.index', [
            'filterOptions' => $this->filterOptions(),
            'payload' => $this->buildPayload($request),
            'activeFilters' => $this->activeFilters($request),
        ]);
    }

    public function data(Request $request)
    {
        return response()->json($this->buildPayload($request));
    }

    public function kpiDetail(Request $request, string $kpi)
    {
        $allowed = ['total', 'open', 'in_progress', 'placed', 'rejected', 'attention'];
        if (! in_array($kpi, $allowed, true)) {
            return response()->json(['message' => 'Invalid KPI.'], 404);
        }

        $base = $this->baseQuery($request);
        $trackerIds = (clone $base)->pluck('id');
        $placedIds = $this->placedPositionIds($trackerIds);

        $meta = match ($kpi) {
            'total' => ['title' => 'All Positions', 'subtitle' => 'Every requirement matching your current filters'],
            'open' => ['title' => 'Open Positions', 'subtitle' => 'Demands raised and awaiting candidate sourcing'],
            'in_progress' => ['title' => 'In Progress', 'subtitle' => 'Active positions with candidates in the pipeline'],
            'placed' => ['title' => 'Placed Positions', 'subtitle' => 'Successfully closed with confirmed placement'],
            'rejected' => ['title' => 'Rejected Positions', 'subtitle' => 'Positions closed without a successful placement'],
            'attention' => ['title' => 'Needs Attention', 'subtitle' => 'Overdue deadlines, due this week, or urgent priority'],
        };

        $items = $kpi === 'attention'
            ? $this->attentionItems($base, $placedIds)
            : $this->kpiPositionItems($base, $placedIds, $kpi);

        return response()->json(array_merge($meta, [
            'kpi' => $kpi,
            'count' => count($items),
            'items' => $items,
        ]));
    }

    public function export(Request $request)
    {
        $base = $this->baseQuery($request);
        $trackerIds = (clone $base)->pluck('id');
        $placedIds = $this->placedPositionIds($trackerIds);
        $payload = $this->buildPayload($request);

        $positions = $this->kpiPositionItems($base, $placedIds, 'total');

        if (empty($positions)) {
            return back()->with('error', 'No data found to export for the selected filters.');
        }

        $exporter = new DashboardExport(
            $payload,
            $positions,
            $this->filterLabelsForExport($request),
        );

        return $exporter->download();
    }

    /**
     * @return array<string, mixed>
     */
    private function filterOptions(): array
    {
        $months = Month::orderedLatestFirst();

        $years = $months
            ->map(fn (Month $m) => Str::afterLast($m->month, ' '))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        return [
            'years' => $years,
            'months' => $months->map(fn (Month $m) => ['id' => $m->id, 'label' => $m->month])->values(),
            'clients' => Client::orderBy('client')->get(['id', 'client']),
            'recruiters' => StaffUser::query()
                ->whereIn('id', TrackerInfo::query()->whereNotNull('lr')->distinct()->pluck('lr'))
                ->orderBy('username')
                ->get(['id', 'username'])
                ->whenEmpty(fn ($c) => StaffUser::orderBy('username')->get(['id', 'username'])),
            'regionGroups' => [
                ['value' => 'Canada', 'label' => 'Canada'],
                ['value' => 'USA', 'label' => 'USA'],
            ],
            'priorities' => ['Urgent', 'High', 'Medium', 'Low'],
            'jobTypes' => ['onsite', 'remote', 'hybrid'],
            'sources' => ['Internal', 'External', 'Dice', 'Linkedin', 'Others'],
            'statuses' => [
                ['value' => 'open', 'label' => 'Open'],
                ['value' => 'in_progress', 'label' => 'In Progress'],
                ['value' => 'placed', 'label' => 'Placed'],
                ['value' => 'rejected', 'label' => 'Rejected'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function activeFilters(Request $request): array
    {
        return [
            'year' => $request->input('year', ''),
            'month_id' => $request->input('month_id', ''),
            'client_id' => $request->input('client_id', ''),
            'lead_recruiter_id' => $request->input('lead_recruiter_id', ''),
            'status' => $request->input('status', ''),
            'priority' => $request->input('priority', ''),
            'type_of_job' => $request->input('type_of_job', ''),
            'csi' => $request->input('csi', ''),
            'region' => $request->input('region', ''),
            'search' => $request->input('search', ''),
        ];
    }

    /**
     * Build a filtered TrackerInfo query based on the request filters.
     */
    private function baseQuery(Request $request)
    {
        $query = TrackerInfo::query();

        if ($request->filled('year')) {
            $monthIds = Month::all()
                ->filter(fn (Month $m) => Str::afterLast($m->month, ' ') === (string) $request->year)
                ->pluck('id');
            $query->whereIn('month_id', $monthIds);
        }

        if ($request->filled('month_id')) {
            $query->where('month_id', $request->month_id);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('lead_recruiter_id')) {
            $query->where('lr', $request->lead_recruiter_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('type_of_job')) {
            $query->where('type_of_job', $request->type_of_job);
        }

        if ($request->filled('csi')) {
            $query->where('csi', $request->csi);
        }

        if ($request->filled('region')) {
            $query->where('cf', $request->region);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'open' => $query->where('job_status_FK', self::STATUS_OPEN),
                'in_progress' => $query->whereIn('job_status_FK', self::IN_PROGRESS_STATUSES),
                'placed' => $query->where(function ($q) {
                    $q->where('job_status_FK', self::STATUS_PLACED)
                        ->orWhereHas('trackerCandidates.pipelineStatus', fn ($p) => $p->where('final_status_placement_completion', 'Confirmed'));
                }),
                'rejected' => $query->where('job_status_FK', self::STATUS_REJECTED),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('position', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('client', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(Request $request): array
    {
        $base = $this->baseQuery($request);

        $total = (clone $base)->count();
        $trackerIds = (clone $base)->pluck('id');

        $placedIds = $this->placedPositionIds($trackerIds);
        $placed = $placedIds->count();
        $rejected = (clone $base)->where('job_status_FK', self::STATUS_REJECTED)
            ->whereNotIn('id', $placedIds)->count();
        $open = (clone $base)->where('job_status_FK', self::STATUS_OPEN)
            ->whereNotIn('id', $placedIds)->count();
        $inProgress = max(0, $total - $placed - $rejected - $open);

        $candidateBase = TrackerCandidate::whereIn('tracker_info_id', $trackerIds);
        $totalCandidates = (clone $candidateBase)->count();
        $activeCandidates = (clone $candidateBase)->whereNull('rejected_at')->count();

        $closed = $placed + $rejected;
        $placementRate = $total > 0 ? round(($placed / $total) * 100, 1) : 0.0;
        $winRate = $closed > 0 ? round(($placed / $closed) * 100, 1) : 0.0;

        return [
            'kpis' => [
                'total' => $total,
                'open' => $open,
                'in_progress' => $inProgress,
                'placed' => $placed,
                'rejected' => $rejected,
                'closed' => $closed,
                'total_candidates' => $totalCandidates,
                'active_candidates' => $activeCandidates,
                'placement_rate' => $placementRate,
                'win_rate' => $winRate,
            ],
            'attention' => $this->attentionStats($base, $placedIds),
            'status_breakdown' => [
                'labels' => ['Open', 'In Progress', 'Placed', 'Rejected'],
                'data' => [$open, $inProgress, $placed, $rejected],
            ],
            'monthly_trend' => $this->monthlyTrend($base, $placedIds),
            'top_clients' => $this->topClients($base),
            'recruiter_performance' => $this->recruiterPerformance($base, $placedIds),
            'priority_breakdown' => $this->groupCount($base, 'priority', ['Urgent', 'High', 'Medium', 'Low']),
            'job_type_breakdown' => $this->groupCount($base, 'type_of_job', ['onsite', 'remote', 'hybrid']),
            'source_breakdown' => $this->groupCount($base, 'csi', ['Internal', 'External', 'Dice', 'Linkedin', 'Others']),
            'pipeline_funnel' => $this->pipelineFunnel($candidateBase),
            'recent_positions' => $this->recentPositions($base, $placedIds),
        ];
    }

    /**
     * Position IDs considered "placed": position-level status confirmed OR
     * at least one candidate with a confirmed pipeline placement.
     *
     * @param  \Illuminate\Support\Collection<int, int>  $trackerIds
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function placedPositionIds($trackerIds)
    {
        if ($trackerIds->isEmpty()) {
            return collect();
        }

        $byStatus = TrackerInfo::whereIn('id', $trackerIds)
            ->where('job_status_FK', self::STATUS_PLACED)
            ->pluck('id');

        $byPipeline = TrackerCandidate::whereIn('tracker_info_id', $trackerIds)
            ->whereHas('pipelineStatus', fn ($q) => $q->where('final_status_placement_completion', 'Confirmed'))
            ->distinct()
            ->pluck('tracker_info_id');

        return $byStatus->merge($byPipeline)->unique()->values();
    }

    /**
     * @return array<string, int>
     */
    private function attentionStats($base, $placedIds): array
    {
        $items = $this->attentionItems($base, $placedIds);

        $overdue = 0;
        $dueSoon = 0;
        $urgent = 0;
        foreach ($items as $item) {
            foreach ($item['concerns'] as $concern) {
                if (str_starts_with($concern, 'Overdue')) {
                    $overdue++;
                } elseif (str_starts_with($concern, 'Due this week')) {
                    $dueSoon++;
                } elseif (str_starts_with($concern, 'Urgent')) {
                    $urgent++;
                }
            }
        }

        return [
            'total' => count($items),
            'overdue' => $overdue,
            'due_soon' => $dueSoon,
            'urgent' => $urgent,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function attentionItems($base, $placedIds): array
    {
        $today = Carbon::today();
        $weekEnd = Carbon::today()->endOfWeek();
        $placedLookup = $placedIds->flip();

        $positions = (clone $base)
            ->with(['client', 'leadRecruiter', 'jobStatus', 'month', 'region'])
            ->withCount('trackerCandidates')
            ->get()
            ->filter(fn (TrackerInfo $info) => ! $placedLookup->has($info->id)
                && (int) $info->job_status_FK !== self::STATUS_REJECTED);

        $items = [];
        foreach ($positions as $info) {
            $concerns = $this->attentionConcernsFor($info, $today, $weekEnd);
            if ($concerns === []) {
                continue;
            }
            $items[] = $this->formatPositionRow($info, $placedLookup, $concerns);
        }

        usort($items, fn ($a, $b) => count($b['concerns']) <=> count($a['concerns']) ?: $a['id'] <=> $b['id']);

        return $items;
    }

    /**
     * @return list<string>
     */
    private function attentionConcernsFor(TrackerInfo $info, Carbon $today, Carbon $weekEnd): array
    {
        $concerns = [];

        if ($info->submission_deadline) {
            if ($info->submission_deadline->lt($today)) {
                $days = $info->submission_deadline->diffInDays($today);
                $concerns[] = 'Overdue — submission deadline was ' . $info->submission_deadline->format('M d, Y') . " ({$days} day(s) ago)";
            } elseif ($info->submission_deadline->between($today, $weekEnd)) {
                $concerns[] = 'Due this week — submission deadline on ' . $info->submission_deadline->format('M d, Y');
            }
        }

        if ($info->priority === 'Urgent') {
            $concerns[] = 'Urgent priority — requires immediate recruiter action';
        }

        return $concerns;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function kpiPositionItems($base, $placedIds, string $kpi): array
    {
        $placedLookup = $placedIds->flip();
        $query = (clone $base)
            ->with(['client', 'leadRecruiter', 'jobStatus', 'month', 'region'])
            ->withCount('trackerCandidates');

        $positions = match ($kpi) {
            'open' => $query->where('job_status_FK', self::STATUS_OPEN)
                ->whereNotIn('id', $placedIds)->get(),
            'in_progress' => $query->get()->filter(function (TrackerInfo $info) use ($placedLookup) {
                return ! $placedLookup->has($info->id)
                    && (int) $info->job_status_FK !== self::STATUS_OPEN
                    && (int) $info->job_status_FK !== self::STATUS_REJECTED;
            })->values(),
            'placed' => $query->whereIn('id', $placedIds)->get(),
            'rejected' => $query->where('job_status_FK', self::STATUS_REJECTED)
                ->whereNotIn('id', $placedIds)->get(),
            default => $query->orderByDesc('id')->get(),
        };

        $detail = match ($kpi) {
            'open' => 'Awaiting candidate sourcing — no active pipeline yet',
            'in_progress' => 'Active recruitment in progress',
            'placed' => 'Placement confirmed',
            'rejected' => 'Position closed without placement',
            default => null,
        };

        return $positions->map(function (TrackerInfo $info) use ($placedLookup, $detail, $kpi) {
            $concerns = $detail ? [$detail] : [];
            if ($kpi === 'in_progress' && $info->tracker_candidates_count === 0) {
                $concerns[] = 'No candidates assigned yet';
            }

            return $this->formatPositionRow($info, $placedLookup, $concerns);
        })->values()->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>  $placedLookup
     * @param  list<string>  $concerns
     * @return array<string, mixed>
     */
    private function formatPositionRow(TrackerInfo $info, $placedLookup, array $concerns = []): array
    {
        $isPlaced = $placedLookup->has($info->id);
        $status = $isPlaced
            ? 'Candidate Placement Confirmed'
            : ($info->jobStatus->status ?? 'Demand Raised');

        return [
            'id' => $info->id,
            'position' => $info->position ?: 'Untitled Position',
            'client' => $info->client->client ?? '—',
            'recruiter' => $info->leadRecruiter->username ?? '—',
            'month' => $info->month->month ?? '—',
            'priority' => $info->priority ?: '—',
            'status' => $status,
            'status_group' => $isPlaced ? 'placed' : $this->statusGroup((int) $info->job_status_FK),
            'job_type' => $info->type_of_job ? ucfirst($info->type_of_job) : '—',
            'source' => $info->csi ?? '—',
            'country' => $info->cf ?? ($info->country ?? '—'),
            'bill_rate' => $info->bill_rate_salary_range ?? '—',
            'deadline' => $info->submission_deadline?->format('M d, Y') ?? '—',
            'candidate_count' => $info->tracker_candidates_count ?? 0,
            'concerns' => $concerns,
            'url' => route('tracker.info', $info->id),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function filterLabelsForExport(Request $request): array
    {
        $labels = [
            'Year' => $request->filled('year') ? (string) $request->year : 'All Years',
            'Month' => $request->filled('month_id')
                ? (Month::find($request->month_id)?->month ?? 'Selected')
                : 'All Months',
            'Client' => $request->filled('client_id')
                ? (Client::find($request->client_id)?->client ?? 'Selected')
                : 'All Clients',
            'Lead Recruiter' => $request->filled('lead_recruiter_id')
                ? (StaffUser::find($request->lead_recruiter_id)?->username ?? 'Selected')
                : 'All Recruiters',
            'Status' => $request->filled('status') ? Str::title(str_replace('_', ' ', $request->status)) : 'All Statuses',
            'Priority' => $request->filled('priority') ? $request->priority : 'All Priorities',
            'Job Type' => $request->filled('type_of_job') ? ucfirst($request->type_of_job) : 'All Types',
            'Source' => $request->filled('csi') ? $request->csi : 'All Sources',
            'Region' => $request->filled('region') ? $request->region : 'All Regions',
            'Search' => $request->filled('search') ? $request->search : '—',
        ];

        return $labels;
    }

    /**
     * Positions raised vs placed per month, in chronological order.
     *
     * @return array<string, mixed>
     */
    private function monthlyTrend($base, $placedIds): array
    {
        $months = Month::orderedLatestFirst()->reverse()->values();

        $raisedByMonth = (clone $base)
            ->select('month_id', DB::raw('count(*) as c'))
            ->groupBy('month_id')
            ->pluck('c', 'month_id');

        $placedByMonth = TrackerInfo::whereIn('id', $placedIds)
            ->select('month_id', DB::raw('count(*) as c'))
            ->groupBy('month_id')
            ->pluck('c', 'month_id');

        $labels = [];
        $raised = [];
        $placed = [];

        foreach ($months as $month) {
            $labels[] = $month->month;
            $raised[] = (int) ($raisedByMonth[$month->id] ?? 0);
            $placed[] = (int) ($placedByMonth[$month->id] ?? 0);
        }

        return [
            'labels' => $labels,
            'raised' => $raised,
            'placed' => $placed,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function topClients($base): array
    {
        $rows = (clone $base)
            ->select('client_id', DB::raw('count(*) as c'))
            ->groupBy('client_id')
            ->orderByDesc('c')
            ->limit(8)
            ->get();

        $clientNames = Client::whereIn('id', $rows->pluck('client_id')->filter())
            ->pluck('client', 'id');

        $labels = [];
        $data = [];
        foreach ($rows as $row) {
            $labels[] = $row->client_id ? ($clientNames[$row->client_id] ?? 'Unknown') : 'Unassigned';
            $data[] = (int) $row->c;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return array<string, mixed>
     */
    private function recruiterPerformance($base, $placedIds): array
    {
        $totals = (clone $base)
            ->whereNotNull('lr')
            ->select('lr', DB::raw('count(*) as c'))
            ->groupBy('lr')
            ->orderByDesc('c')
            ->limit(8)
            ->get();

        $placed = TrackerInfo::whereIn('id', $placedIds)
            ->whereNotNull('lr')
            ->select('lr', DB::raw('count(*) as c'))
            ->groupBy('lr')
            ->pluck('c', 'lr');

        $names = StaffUser::whereIn('id', $totals->pluck('lr'))->pluck('username', 'id');

        $labels = [];
        $totalData = [];
        $placedData = [];
        foreach ($totals as $row) {
            $labels[] = $names[$row->lr] ?? ('User #' . $row->lr);
            $totalData[] = (int) $row->c;
            $placedData[] = (int) ($placed[$row->lr] ?? 0);
        }

        return ['labels' => $labels, 'total' => $totalData, 'placed' => $placedData];
    }

    /**
     * Generic grouped count over a column, preserving a fixed order of buckets.
     *
     * @param  list<string>  $buckets
     * @return array<string, mixed>
     */
    private function groupCount($base, string $column, array $buckets): array
    {
        $counts = (clone $base)
            ->select($column, DB::raw('count(*) as c'))
            ->groupBy($column)
            ->pluck('c', $column);

        $labels = [];
        $data = [];
        foreach ($buckets as $bucket) {
            $labels[] = Str::title($bucket);
            $data[] = (int) ($counts[$bucket] ?? 0);
        }

        $known = array_sum($data);
        $totalAll = (int) $counts->sum();
        if ($totalAll > $known) {
            $labels[] = 'Other';
            $data[] = $totalAll - $known;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Candidate-level pipeline funnel across the filtered positions.
     *
     * @return array<string, mixed>
     */
    private function pipelineFunnel($candidateBase): array
    {
        $stages = [
            'Identified' => [2],
            'Screening' => [3, 4, 5],
            'Submission' => [6, 7, 8],
            'Interview' => [9, 10, 11],
            'Decision' => [12],
        ];

        $labels = [];
        $data = [];
        foreach ($stages as $label => $statusIds) {
            $labels[] = $label;
            $data[] = (clone $candidateBase)->whereIn('current_status_id', $statusIds)->count();
        }

        $labels[] = 'Placed';
        $data[] = (clone $candidateBase)
            ->whereHas('pipelineStatus', fn ($q) => $q->where('final_status_placement_completion', 'Confirmed'))
            ->count();

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentPositions($base, $placedIds): array
    {
        $placedLookup = $placedIds->flip();

        return (clone $base)
            ->with(['client', 'leadRecruiter', 'jobStatus', 'month'])
            ->withCount('trackerCandidates')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn (TrackerInfo $info) => $this->formatPositionRow($info, $placedLookup))
            ->toArray();
    }

    private function statusGroup(int $statusId): string
    {
        return match (true) {
            $statusId === self::STATUS_OPEN => 'open',
            $statusId === self::STATUS_PLACED => 'placed',
            $statusId === self::STATUS_REJECTED => 'rejected',
            default => 'in_progress',
        };
    }
}
