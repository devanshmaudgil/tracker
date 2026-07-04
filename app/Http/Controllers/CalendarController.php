<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CalendarController extends Controller
{
    /**
     * Return public holidays / observances for a country + year.
     * US & Canada holidays are rule-based (any year). India combines
     * fixed national days with a lunar-festival lookup table (2024-2028).
     */
    public function holidays(Request $request)
    {
        $validated = $request->validate([
            'country' => 'required|in:IN,US,CA',
            'year' => 'required|integer|min:2020|max:2035',
        ]);

        $country = $validated['country'];
        $year = (int) $validated['year'];

        $holidays = Cache::remember("calendar.holidays.{$country}.{$year}", now()->addDay(), function () use ($country, $year) {
            return match ($country) {
                'US' => $this->usHolidays($year),
                'CA' => $this->canadaHolidays($year),
                'IN' => $this->indiaHolidays($year),
            };
        });

        usort($holidays, fn ($a, $b) => strcmp($a['date'], $b['date']));

        return response()->json([
            'country' => $country,
            'year' => $year,
            'holidays' => $holidays,
        ]);
    }

    /** nth occurrence of a weekday in a month, e.g. 3rd Monday of January. */
    private function nthWeekday(int $year, int $month, int $weekday, int $nth): Carbon
    {
        $date = Carbon::create($year, $month, 1);
        $offset = ($weekday - $date->dayOfWeek + 7) % 7;
        return $date->addDays($offset + 7 * ($nth - 1));
    }

    /** Last occurrence of a weekday in a month, e.g. last Monday of May. */
    private function lastWeekday(int $year, int $month, int $weekday): Carbon
    {
        $date = Carbon::create($year, $month, 1)->endOfMonth()->startOfDay();
        $offset = ($date->dayOfWeek - $weekday + 7) % 7;
        return $date->subDays($offset);
    }

    /** Western (Gregorian) Easter Sunday. easter_days avoids timezone shifts of easter_date. */
    private function easterSunday(int $year): Carbon
    {
        return Carbon::create($year, 3, 21)->addDays(easter_days($year));
    }

    private function entry(Carbon $date, string $name, string $type = 'public'): array
    {
        return [
            'date' => $date->format('Y-m-d'),
            'name' => $name,
            'type' => $type,
        ];
    }

    private function usHolidays(int $year): array
    {
        $easter = $this->easterSunday($year);

        return [
            $this->entry(Carbon::create($year, 1, 1), "New Year's Day"),
            $this->entry($this->nthWeekday($year, 1, Carbon::MONDAY, 3), 'Martin Luther King Jr. Day'),
            $this->entry(Carbon::create($year, 2, 14), "Valentine's Day", 'observance'),
            $this->entry($this->nthWeekday($year, 2, Carbon::MONDAY, 3), "Presidents' Day"),
            $this->entry($easter->copy()->subDays(2), 'Good Friday', 'observance'),
            $this->entry($easter, 'Easter Sunday', 'observance'),
            $this->entry($this->nthWeekday($year, 5, Carbon::SUNDAY, 2), "Mother's Day", 'observance'),
            $this->entry($this->lastWeekday($year, 5, Carbon::MONDAY), 'Memorial Day'),
            $this->entry($this->nthWeekday($year, 6, Carbon::SUNDAY, 3), "Father's Day", 'observance'),
            $this->entry(Carbon::create($year, 6, 19), 'Juneteenth'),
            $this->entry(Carbon::create($year, 7, 4), 'Independence Day'),
            $this->entry($this->nthWeekday($year, 9, Carbon::MONDAY, 1), 'Labor Day'),
            $this->entry($this->nthWeekday($year, 10, Carbon::MONDAY, 2), 'Columbus Day'),
            $this->entry(Carbon::create($year, 10, 31), 'Halloween', 'observance'),
            $this->entry(Carbon::create($year, 11, 11), 'Veterans Day'),
            $this->entry($this->nthWeekday($year, 11, Carbon::THURSDAY, 4), 'Thanksgiving Day'),
            $this->entry($this->nthWeekday($year, 11, Carbon::THURSDAY, 4)->addDay(), 'Black Friday', 'observance'),
            $this->entry(Carbon::create($year, 12, 24), 'Christmas Eve', 'observance'),
            $this->entry(Carbon::create($year, 12, 25), 'Christmas Day'),
            $this->entry(Carbon::create($year, 12, 31), "New Year's Eve", 'observance'),
        ];
    }

    private function canadaHolidays(int $year): array
    {
        $easter = $this->easterSunday($year);

        return [
            $this->entry(Carbon::create($year, 1, 1), "New Year's Day"),
            $this->entry($this->nthWeekday($year, 2, Carbon::MONDAY, 3), 'Family Day'),
            $this->entry(Carbon::create($year, 2, 14), "Valentine's Day", 'observance'),
            $this->entry($easter->copy()->subDays(2), 'Good Friday'),
            $this->entry($easter, 'Easter Sunday', 'observance'),
            $this->entry($easter->copy()->addDay(), 'Easter Monday', 'observance'),
            $this->entry($this->nthWeekday($year, 5, Carbon::SUNDAY, 2), "Mother's Day", 'observance'),
            $this->entry(Carbon::create($year, 5, 24)->subDays((Carbon::create($year, 5, 24)->dayOfWeek - Carbon::MONDAY + 7) % 7), 'Victoria Day'),
            $this->entry($this->nthWeekday($year, 6, Carbon::SUNDAY, 3), "Father's Day", 'observance'),
            $this->entry(Carbon::create($year, 7, 1), 'Canada Day'),
            $this->entry($this->nthWeekday($year, 8, Carbon::MONDAY, 1), 'Civic Holiday'),
            $this->entry($this->nthWeekday($year, 9, Carbon::MONDAY, 1), 'Labour Day'),
            $this->entry(Carbon::create($year, 9, 30), 'National Day for Truth and Reconciliation'),
            $this->entry($this->nthWeekday($year, 10, Carbon::MONDAY, 2), 'Thanksgiving Day'),
            $this->entry(Carbon::create($year, 10, 31), 'Halloween', 'observance'),
            $this->entry(Carbon::create($year, 11, 11), 'Remembrance Day'),
            $this->entry(Carbon::create($year, 12, 24), 'Christmas Eve', 'observance'),
            $this->entry(Carbon::create($year, 12, 25), 'Christmas Day'),
            $this->entry(Carbon::create($year, 12, 26), 'Boxing Day'),
            $this->entry(Carbon::create($year, 12, 31), "New Year's Eve", 'observance'),
        ];
    }

    private function indiaHolidays(int $year): array
    {
        $holidays = [
            $this->entry(Carbon::create($year, 1, 1), "New Year's Day", 'observance'),
            $this->entry(Carbon::create($year, 1, 14), 'Makar Sankranti / Pongal', 'observance'),
            $this->entry(Carbon::create($year, 1, 26), 'Republic Day'),
            $this->entry(Carbon::create($year, 8, 15), 'Independence Day'),
            $this->entry(Carbon::create($year, 10, 2), 'Gandhi Jayanti'),
            $this->entry(Carbon::create($year, 12, 25), 'Christmas Day'),
        ];

        // Lunar / movable festivals — verified dates per year.
        $festivalTable = [
            2024 => [
                '2024-03-08' => 'Maha Shivaratri', '2024-03-25' => 'Holi',
                '2024-03-29' => 'Good Friday', '2024-04-09' => 'Ugadi / Gudi Padwa',
                '2024-04-11' => 'Eid-ul-Fitr', '2024-04-17' => 'Ram Navami',
                '2024-04-21' => 'Mahavir Jayanti', '2024-05-23' => 'Buddha Purnima',
                '2024-06-17' => 'Eid-ul-Adha (Bakrid)', '2024-07-17' => 'Muharram',
                '2024-08-19' => 'Raksha Bandhan', '2024-08-26' => 'Janmashtami',
                '2024-09-07' => 'Ganesh Chaturthi', '2024-09-16' => 'Milad-un-Nabi',
                '2024-10-12' => 'Dussehra', '2024-10-31' => 'Diwali',
                '2024-11-15' => 'Guru Nanak Jayanti',
            ],
            2025 => [
                '2025-02-26' => 'Maha Shivaratri', '2025-03-14' => 'Holi',
                '2025-03-31' => 'Eid-ul-Fitr', '2025-04-06' => 'Ram Navami',
                '2025-04-10' => 'Mahavir Jayanti', '2025-04-18' => 'Good Friday',
                '2025-05-12' => 'Buddha Purnima', '2025-06-07' => 'Eid-ul-Adha (Bakrid)',
                '2025-07-06' => 'Muharram', '2025-08-09' => 'Raksha Bandhan',
                '2025-08-16' => 'Janmashtami', '2025-08-27' => 'Ganesh Chaturthi',
                '2025-09-05' => 'Milad-un-Nabi', '2025-10-02' => 'Dussehra',
                '2025-10-20' => 'Diwali', '2025-11-05' => 'Guru Nanak Jayanti',
            ],
            2026 => [
                '2026-02-15' => 'Maha Shivaratri', '2026-03-04' => 'Holi',
                '2026-03-21' => 'Eid-ul-Fitr', '2026-03-26' => 'Ram Navami',
                '2026-03-31' => 'Mahavir Jayanti', '2026-04-03' => 'Good Friday',
                '2026-05-01' => 'Buddha Purnima', '2026-05-27' => 'Eid-ul-Adha (Bakrid)',
                '2026-06-26' => 'Muharram', '2026-08-28' => 'Raksha Bandhan',
                '2026-09-04' => 'Janmashtami', '2026-09-14' => 'Ganesh Chaturthi',
                '2026-08-26' => 'Milad-un-Nabi', '2026-10-20' => 'Dussehra',
                '2026-11-08' => 'Diwali', '2026-11-24' => 'Guru Nanak Jayanti',
            ],
            2027 => [
                '2027-03-06' => 'Maha Shivaratri', '2027-03-22' => 'Holi',
                '2027-03-10' => 'Eid-ul-Fitr', '2027-04-15' => 'Ram Navami',
                '2027-04-19' => 'Mahavir Jayanti', '2027-03-26' => 'Good Friday',
                '2027-05-20' => 'Buddha Purnima', '2027-05-17' => 'Eid-ul-Adha (Bakrid)',
                '2027-06-16' => 'Muharram', '2027-08-17' => 'Raksha Bandhan',
                '2027-08-25' => 'Janmashtami', '2027-09-03' => 'Ganesh Chaturthi',
                '2027-08-15' => 'Milad-un-Nabi', '2027-10-09' => 'Dussehra',
                '2027-10-29' => 'Diwali', '2027-11-14' => 'Guru Nanak Jayanti',
            ],
            2028 => [
                '2028-02-23' => 'Maha Shivaratri', '2028-03-11' => 'Holi',
                '2028-02-28' => 'Eid-ul-Fitr', '2028-04-03' => 'Ram Navami',
                '2028-04-07' => 'Mahavir Jayanti', '2028-04-14' => 'Good Friday',
                '2028-05-08' => 'Buddha Purnima', '2028-05-05' => 'Eid-ul-Adha (Bakrid)',
                '2028-06-04' => 'Muharram', '2028-08-05' => 'Raksha Bandhan',
                '2028-08-13' => 'Janmashtami', '2028-08-23' => 'Ganesh Chaturthi',
                '2028-08-03' => 'Milad-un-Nabi', '2028-09-27' => 'Dussehra',
                '2028-10-17' => 'Diwali', '2028-11-02' => 'Guru Nanak Jayanti',
            ],
        ];

        foreach ($festivalTable[$year] ?? [] as $date => $name) {
            $holidays[] = [
                'date' => $date,
                'name' => $name,
                'type' => 'public',
            ];
        }

        return $holidays;
    }
}
