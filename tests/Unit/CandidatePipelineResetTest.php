<?php

namespace Tests\Unit;

use App\Services\Tracker\CandidatePipelineService;
use PHPUnit\Framework\TestCase;

class CandidatePipelineResetTest extends TestCase
{
    public function test_reset_service_method_exists(): void
    {
        $this->assertTrue(method_exists(CandidatePipelineService::class, 'resetCandidateToFreshStart'));
    }
}
