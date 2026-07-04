<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Month;
use App\Models\StaffUser;
use App\Models\TrackerCandidate;
use App\Models\TrackerInfo;
use App\Models\UserLogin;
use Database\Seeders\JobStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateMailDraftTest extends TestCase
{
    use RefreshDatabase;

    private function seedMailScenario(): array
    {
        $this->seed(JobStatusSeeder::class);

        $month = Month::create(['month' => 'July 2026']);

        $staff = StaffUser::create([
            'username' => 'RecruiterOne',
            'email' => 'recruiter@rinfinite.com',
        ]);

        $user = UserLogin::create([
            'staff_user_id' => $staff->id,
            'username' => 'RecruiterOne',
            'password' => 'password123',
        ]);

        $tracker = TrackerInfo::create([
            'month_id' => $month->id,
            'position' => 'Snowflake Developer',
            'job_description' => "Role: Snowflake Developer\nLocation: Dallas, Texas, USA",
        ]);

        $candidate = Candidate::create([
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $tc = TrackerCandidate::create([
            'tracker_info_id' => $tracker->id,
            'candidate_id' => $candidate->id,
            'current_status_id' => 2,
        ]);

        return compact('user', 'tracker', 'tc');
    }

    public function test_mail_draft_requires_rinfinite_sender(): void
    {
        $data = $this->seedMailScenario();

        $response = $this->actingAs($data['user'])->postJson(
            route('tracker.candidates.mail.draft', [
                'tracker_id' => $data['tracker']->id,
                'tracker_candidate_id' => $data['tc']->id,
            ]),
            [
                'from' => 'bad@gmail.com',
                'to' => 'jane@example.com',
                'subject' => 'Test',
                'body' => '<p>Hello</p>',
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['from']);
    }

    public function test_mail_draft_returns_eml_attachment(): void
    {
        $data = $this->seedMailScenario();

        $response = $this->actingAs($data['user'])->post(
            route('tracker.candidates.mail.draft', [
                'tracker_id' => $data['tracker']->id,
                'tracker_candidate_id' => $data['tc']->id,
            ]),
            [
                'from' => 'recruiter@rinfinite.com',
                'to' => 'jane@example.com',
                'subject' => 'Exciting Opportunity',
                'body' => '<p>Hi Jane,</p>',
                'candidate_name' => 'Jane Doe',
            ]
        );

        $response->assertOk();
        $response->assertHeader('content-type', 'message/rfc822');
        $this->assertStringContainsString('X-Unsent: 1', $response->getContent());
        $this->assertStringContainsString('Exciting Opportunity', $response->getContent());
    }
}
