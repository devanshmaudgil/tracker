<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrackerCandidate;

$trackerId = 2;
$candidates = TrackerCandidate::where('tracker_info_id', $trackerId)->get();

foreach ($candidates as $tc) {
    echo "TC ID: " . $tc->id . "\n";
    echo "Candidate ID: " . $tc->candidate_id . "\n";
    echo "Current Status ID: " . $tc->current_status_id . "\n";
    
    $status = \DB::table('candidate_pipeline_status')->where('tracker_candidate_id', $tc->id)->first();
    if ($status) {
        echo "Pipeline Status Record Found:\n";
        echo " - Round 1 Date: " . ($status->client_interview_round_1_date ?? 'NULL') . "\n";
        echo " - Round 2 Date: " . ($status->client_interview_round_2_date ?? 'NULL') . "\n";
    } else {
        echo "No Pipeline Status record found for TC ID " . $tc->id . "\n";
    }
}
