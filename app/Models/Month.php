<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Month extends Model
{
    protected $fillable = [
        'month',
    ];

    public static function parseMonthDate(string $label): int
    {
        try {
            return Carbon::parse($label)->startOfMonth()->timestamp;
        } catch (\Exception) {
            return 0;
        }
    }

    public static function orderedLatestFirst()
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

    public static function resolveSelectedId(Request $request): ?int
    {
        if ($request->filled('month_id')) {
            return (int) $request->month_id;
        }

        return static::latestMonth()?->id;
    }
}
