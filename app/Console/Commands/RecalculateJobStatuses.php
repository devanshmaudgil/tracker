<?php

namespace App\Console\Commands;

use App\Models\TrackerInfo;
use Illuminate\Console\Command;

class RecalculateJobStatuses extends Command
{
    protected $signature = 'tracker:recalculate-job-statuses';

    protected $description = 'Recompute job_status_FK for every tracker from its candidate pipelines';

    public function handle(): int
    {
        $jobs = TrackerInfo::with('trackerCandidates.pipelineStatus')->get();
        $changed = 0;

        foreach ($jobs as $job) {
            $before = (int) $job->job_status_FK;
            $job->updateStatusFromCandidates();

            if ((int) $job->fresh()->job_status_FK !== $before) {
                $changed++;
            }
        }

        $this->info("Recalculated {$jobs->count()} jobs. {$changed} status(es) updated.");

        return self::SUCCESS;
    }
}
