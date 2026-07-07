<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class JobStatus extends Model
{
    protected $table = 'job_status';

    protected $fillable = [
        'status',
        'status_initial',
    ];

    private static ?Collection $cached = null;

    public static function allCached(): Collection
    {
        return static::$cached ??= static::orderBy('id')->get();
    }

    public static function labelFor(int $id, string $fallback = ''): string
    {
        return static::allCached()->firstWhere('id', $id)?->status ?? $fallback;
    }

    public static function idForStatus(string $status): ?int
    {
        $row = static::allCached()->firstWhere('status', $status);

        return $row ? (int) $row->id : null;
    }

    public static function placementCompletedId(): int
    {
        return static::idForStatus('Candidate Placement Completed') ?? 17;
    }

    public static function placementCompletedLabel(): string
    {
        return static::labelFor(static::placementCompletedId(), 'Candidate Placement Completed');
    }

    public static function unservedId(): int
    {
        return static::idForStatus('Unserved') ?? 19;
    }

    public static function unservedLabel(): string
    {
        return static::labelFor(static::unservedId(), 'Unserved');
    }

    public static function clientDecisionAwaitedLabel(): string
    {
        return static::labelFor(12, 'Client Decision Awaited');
    }

    public static function labelsById(): array
    {
        return static::allCached()->pluck('status', 'id')->all();
    }
}
