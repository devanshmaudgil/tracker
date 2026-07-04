<?php

namespace Tests\Unit;

use App\Models\TrackerCandidate;
use PHPUnit\Framework\TestCase;

class ApprovedStageTest extends TestCase
{
    public function test_pipeline_stages_are_recognized(): void
    {
        $this->assertContains('on_hold', TrackerCandidate::PIPELINE_APPROVED_STAGES);
        $this->assertContains('in_progress', TrackerCandidate::PIPELINE_APPROVED_STAGES);
    }

    public function test_submitted_stages_are_recognized(): void
    {
        $this->assertContains('submitted_to_client', TrackerCandidate::SUBMITTED_APPROVED_STAGES);
        $this->assertContains('awaited_from_client', TrackerCandidate::SUBMITTED_APPROVED_STAGES);
    }

    public function test_stage_labels_cover_all_values(): void
    {
        foreach (TrackerCandidate::APPROVED_STAGES as $stage) {
            $this->assertArrayHasKey($stage, TrackerCandidate::approvedStageLabels());
        }
    }
}
