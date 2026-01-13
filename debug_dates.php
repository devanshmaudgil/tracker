<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrackerCandidate;
use App\Models\CandidatePipelineStatus;

$trackerId = 2;
echo "Checking candidates for Tracker ID: $trackerId\n";

$candidates = TrackerCandidate::where('tracker_info_id', $trackerId)->with('candidate', 'pipelineStatus')->get();

foreach ($candidates as $tc) {
    echo "Candidate: " . $tc->candidate->full_name . " (ID: " . $tc->candidate_id . ")\n";
    echo "Current Status ID: " . $tc->current_status_id . "\n";
    
    if ($tc->pipelineStatus) {
        echo "Pipeline Status Found:\n";
        echo " - Round 1 Date: " . ($tc->pipelineStatus->client_interview_round_1_date ? $tc->pipelineStatus->client_interview_round_1_date->format('Y-m-d') : 'NULL') . "\n";
        echo " - Round 2 Date: " . ($tc->pipelineStatus->client_interview_round_2_date ? $tc->pipelineStatus->client_interview_round_2_date->format('Y-m-d') : 'NULL') . "\n";
        echo " - Raw Data: " . json_encode($tc->pipelineStatus->toArray()) . "\n";
    } else {
        echo "No Pipeline Status record found.\n";
    }
    echo "-----------------------------------\n";
}
