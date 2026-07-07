<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TrackerInfo;
use Illuminate\Support\Facades\Schema;

echo 'submission_deadline_text column exists: ' . (Schema::hasColumn('tracker_info', 'submission_deadline_text') ? 'yes' : 'no') . PHP_EOL;
echo 'With target date: ' . TrackerInfo::whereNotNull('submission_deadline')->count() . PHP_EOL;
echo 'Without target date: ' . TrackerInfo::whereNull('submission_deadline')->count() . PHP_EOL;

foreach (TrackerInfo::whereNotNull('submission_deadline')->with('month')->orderBy('id')->limit(15)->get(['id', 'prd', 'submission_deadline', 'position']) as $t) {
    echo sprintf(
        "  #%d prd=%s target=%s | %s\n",
        $t->id,
        $t->prd?->format('d-M-Y') ?? '-',
        $t->submission_deadline->format('d-M-Y'),
        substr($t->position ?? '', 0, 40)
    );
}
