<?php

namespace Database\Seeders;

use App\Models\Month;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MonthSeeder extends Seeder
{
    public function run(): void
    {
        $start = Carbon::create(2025, 10, 1);
        $end = Carbon::create(2026, 7, 1);

        while ($start->lte($end)) {
            Month::firstOrCreate(['month' => $start->format('F Y')]);
            $start->addMonth();
        }
    }
}
