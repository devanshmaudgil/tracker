<?php

namespace App\Services\Resume;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ResumeAnalysisProgress
{
    private const TTL_SECONDS = 600;

    public static function createToken(): string
    {
        return (string) Str::uuid();
    }

    public static function isValidToken(string $token): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $token
        );
    }

    public static function report(string $token, string $step, string $label, int $percent): void
    {
        if (! self::isValidToken($token)) {
            return;
        }

        Cache::put(self::cacheKey($token), [
            'step' => $step,
            'label' => $label,
            'percent' => max(0, min(100, $percent)),
        ], self::TTL_SECONDS);
    }

  /**
     * @return array{step: string, label: string, percent: int}|null
     */
    public static function read(string $token): ?array
    {
        if (! self::isValidToken($token)) {
            return null;
        }

        $data = Cache::get(self::cacheKey($token));

        return is_array($data) ? $data : null;
    }

    public static function clear(string $token): void
    {
        if (self::isValidToken($token)) {
            Cache::forget(self::cacheKey($token));
        }
    }

    private static function cacheKey(string $token): string
    {
        return 'resume_analysis_progress:' . $token;
    }
}
