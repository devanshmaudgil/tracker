<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TrackerInfo;
use App\Models\Month;
use Illuminate\Support\Facades\Schema;

echo 'Total trackers: ' . TrackerInfo::count() . PHP_EOL;
echo 'Unserved trackers: ' . TrackerInfo::where('is_unserved', true)->count() . PHP_EOL;
echo 'Current month: ' . (Month::currentMonth()->month ?? 'none') . PHP_EOL;
echo "Unserved by month:\n";
foreach (TrackerInfo::where('is_unserved', true)->with('month')->get() as $t) {
    echo "  #{$t->id}: " . ($t->month->month ?? '-') . PHP_EOL;
}
