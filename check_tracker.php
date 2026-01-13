<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrackerInfo;
use App\Models\TrackerCandidate;

$tracker = TrackerInfo::find(2);
if (!$tracker) {
    echo "Tracker ID 2 not found.\n";
} else {
    echo "Tracker ID 2 found: " . $tracker->position . "\n";
    $count = TrackerCandidate::where('tracker_info_id', 2)->count();
    echo "Candidates assigned: $count\n";
}
