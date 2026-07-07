<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Month extends Model
{
    protected $fillable = [
        'month',
    ];

    public function trackers()
    {
        return $this->hasMany(TrackerInfo::class, 'month_id');
    }

    public function yearLabel(): string
    {
        return Str::afterLast($this->month, ' ');
    }

    public static function parseMonthDate(string $label): int
    {
        try {
            return Carbon::parse($label)->startOfMonth()->timestamp;
        } catch (\Exception) {
            return 0;
        }
    }

    public static function orderedLatestFirst(): Collection
    {
        return static::query()
            ->get()
            ->sortByDesc(fn (self $month) => static::parseMonthDate($month->month))
            ->values();
    }

    public static function latestMonth(): ?self
    {
        return static::orderedLatestFirst()->first();
    }

    public static function currentMonth(): ?self
    {
        $now = Carbon::now();
        $target = $now->format('F') . ' ' . $now->format('Y');

        $exact = static::query()
            ->get()
            ->first(fn (self $month) => strcasecmp($month->month, $target) === 0);

        if ($exact) {
            return $exact;
        }

        return static::forYear((string) $now->year)->first();
    }

    public static function availableYears(): Collection
    {
        return static::orderedLatestFirst()
            ->map(fn (self $month) => $month->yearLabel())
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();
    }

    public static function forYear(string $year): Collection
    {
        return static::orderedLatestFirst()
            ->filter(fn (self $month) => $month->yearLabel() === $year)
            ->values();
    }

    public static function monthIdsForYear(string $year): Collection
    {
        return static::forYear($year)->pluck('id');
    }

    public static function resolveSelectedYear(Request $request): string
    {
        if ($request->filled('year')) {
            return (string) $request->year;
        }

        if ($request->filled('month_id') && $request->input('month_id') !== 'all') {
            $month = static::find($request->month_id);
            if ($month) {
                return $month->yearLabel();
            }
        }

        return (string) (static::currentMonth()?->yearLabel() ?? static::availableYears()->first() ?? date('Y'));
    }

    public static function resolveSelectedId(Request $request): ?int
    {
        if ($request->query->has('month_id')) {
            if ($request->input('month_id') === 'all' || $request->input('month_id') === '') {
                return null;
            }

            return (int) $request->month_id;
        }

        return static::currentMonth()?->id;
    }
}
