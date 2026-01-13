<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrackerCandidate;
use App\Models\Candidate;

$trackerId = 4;
echo "Checking candidates for Tracker ID: $trackerId\n";

$candidates = TrackerCandidate::where('tracker_info_id', $trackerId)
    ->with('candidate', 'pipelineStatus')
    ->get();

if ($candidates->isEmpty()) {
    echo "No candidates found for Tracker ID $trackerId.\n";
}

foreach ($candidates as $tc) {
    echo "Candidate: " . $tc->candidate->full_name . " (TC ID: " . $tc->id . ", Candidate ID: " . $tc->candidate_id . ")\n";
    echo "Current Status ID: " . $tc->current_status_id . "\n";
    
    if ($tc->pipelineStatus) {
        echo "Pipeline Status Found (ID: " . $tc->pipelineStatus->id . "):\n";
        echo " - Raw Data: " . json_encode($tc->pipelineStatus->toArray(), JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "!!! No Pipeline Status record found for TC ID " . $tc->id . " !!!\n";
    }
    echo "-----------------------------------\n";
}
