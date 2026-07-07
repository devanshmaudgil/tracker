<?php

namespace App\Services\Tracker;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;

class SubmissionDeadlineResolver
{
    /**
     * Resolve a target/submission deadline from a raw Excel or form value.
     * Relative values such as "48 Hours" are calculated from the receiving date (PRD).
     */
    public static function resolve(mixed $raw, Carbon|string|null $receivingDate = null): ?Carbon
    {
        if ($raw === null || trim((string) $raw) === '') {
            return null;
        }

        if ($raw instanceof Carbon) {
            return $raw->copy()->startOfDay();
        }

        if (is_numeric($raw)) {
            try {
                return Carbon::instance(XlsDate::excelToDateTimeObject((float) $raw))->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        $text = trim((string) $raw);
        $explicit = self::parseExplicitDate($text);
        if ($explicit) {
            return $explicit;
        }

        $prd = self::normalizeReceivingDate($receivingDate);
        if (! $prd) {
            return null;
        }

        if (preg_match('/^(\d+)\s*hours?$/i', $text, $matches)) {
            return $prd->copy()->addHours((int) $matches[1])->startOfDay();
        }

        if (preg_match('/^(\d+)\s*days?$/i', $text, $matches)) {
            return $prd->copy()->addDays((int) $matches[1]);
        }

        if (preg_match('/^(\d+)\s*weeks?$/i', $text, $matches)) {
            return $prd->copy()->addWeeks((int) $matches[1]);
        }

        return null;
    }

    public static function toDateString(mixed $raw, Carbon|string|null $receivingDate = null): ?string
    {
        return self::resolve($raw, $receivingDate)?->format('Y-m-d');
    }

    private static function normalizeReceivingDate(Carbon|string|null $receivingDate): ?Carbon
    {
        if ($receivingDate === null || $receivingDate === '') {
            return null;
        }

        if ($receivingDate instanceof Carbon) {
            return $receivingDate->copy()->startOfDay();
        }

        try {
            return Carbon::parse($receivingDate)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function parseExplicitDate(string $value): ?Carbon
    {
        if (preg_match('#^(\d{1,2})[/.\-](\d{1,2})[/.\-](\d{4})$#', $value, $matches)) {
            $day = (int) $matches[1];
            $month = (int) $matches[2];
            $year = (int) $matches[3];
            if ($month > 12 && $day <= 12) {
                [$day, $month] = [$month, $day];
            }
            if ($day > 31) {
                return null;
            }

            return Carbon::createFromDate($year, $month, $day)->startOfDay();
        }

        if (preg_match('#^\d{4}-\d{2}-\d{2}#', $value)) {
            try {
                return Carbon::parse($value)->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
