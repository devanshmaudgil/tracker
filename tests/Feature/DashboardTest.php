<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\CandidatePipelineStatus;
use App\Models\Client;
use App\Models\Month;
use App\Models\StaffUser;
use App\Models\TrackerCandidate;
use App\Models\TrackerInfo;
use App\Models\UserLogin;
use Carbon\Carbon;
use Database\Seeders\JobStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function seedScenario(): array
    {
        $this->seed(JobStatusSeeder::class);

        $month = Month::create(['month' => 'June 2026']);
        $otherMonth = Month::create(['month' => 'May 2025']);
        $client = Client::create(['client' => 'Acme Corp']);
        $recruiter = StaffUser::create(['username' => 'Test.Recruiter']);

        // 2 open, 1 in progress, 1 placed, 1 rejected (all June 2026 / Acme / recruiter)
        $rows = [
            ['job_status_FK' => 1,  'priority' => 'Urgent', 'type_of_job' => 'remote',  'csi' => 'Internal', 'cf' => 'USA'],
            ['job_status_FK' => 1,  'priority' => 'High',   'type_of_job' => 'onsite',  'csi' => 'Dice',     'cf' => 'USA'],
            ['job_status_FK' => 5,  'priority' => 'Medium', 'type_of_job' => 'hybrid',  'csi' => 'Linkedin', 'cf' => 'Canada'],
            ['job_status_FK' => 17, 'priority' => 'High',   'type_of_job' => 'remote',  'csi' => 'Internal', 'cf' => 'Canada'],
            ['job_status_FK' => 18, 'priority' => 'Low',    'type_of_job' => 'onsite',  'csi' => 'External', 'cf' => 'USA'],
        ];

        foreach ($rows as $i => $row) {
            TrackerInfo::create(array_merge($row, [
                'month_id' => $month->id,
                'client_id' => $client->id,
                'lr' => $recruiter->id,
                'position' => 'Position ' . ($i + 1),
            ]));
        }

        // A different-month placed row to verify filtering
        TrackerInfo::create([
            'job_status_FK' => 17,
            'month_id' => $otherMonth->id,
            'client_id' => $client->id,
            'lr' => $recruiter->id,
            'position' => 'Old Placement',
            'priority' => 'Medium',
            'type_of_job' => 'remote',
            'csi' => 'Others',
            'cf' => 'Canada',
        ]);

        $login = UserLogin::create([
            'username' => 'dash_tester',
            'password' => 'secret-password',
        ]);

        return compact('month', 'otherMonth', 'client', 'recruiter', 'login');
    }

    public function test_dashboard_page_loads_for_authenticated_user(): void
    {
        $data = $this->seedScenario();

        $this->actingAs($data['login'])
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Analytics Dashboard')
            ->assertSee('Total Positions');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_data_endpoint_returns_correct_aggregate_counts(): void
    {
        $data = $this->seedScenario();

        $response = $this->actingAs($data['login'])->getJson('/dashboard/data');

        $response->assertOk()
            ->assertJsonPath('kpis.total', 6)
            ->assertJsonPath('kpis.open', 2)
            ->assertJsonPath('kpis.in_progress', 1)
            ->assertJsonPath('kpis.placed', 2)
            ->assertJsonPath('kpis.rejected', 1)
            ->assertJsonStructure([
                'kpis' => ['total', 'open', 'in_progress', 'placed', 'rejected', 'placement_rate', 'win_rate'],
                'attention' => ['total', 'overdue', 'due_soon', 'urgent'],
                'status_breakdown' => ['labels', 'data'],
                'monthly_trend' => ['labels', 'raised', 'placed'],
                'top_clients' => ['labels', 'data'],
                'recruiter_performance' => ['labels', 'total', 'placed'],
                'priority_breakdown' => ['labels', 'data'],
                'job_type_breakdown' => ['labels', 'data'],
                'source_breakdown' => ['labels', 'data'],
                'pipeline_funnel' => ['labels', 'data'],
                'recent_positions',
            ]);
    }

    public function test_month_filter_narrows_results(): void
    {
        $data = $this->seedScenario();

        $this->actingAs($data['login'])
            ->getJson('/dashboard/data?month_id=' . $data['month']->id)
            ->assertOk()
            ->assertJsonPath('kpis.total', 5)
            ->assertJsonPath('kpis.placed', 1);
    }

    public function test_status_filter_returns_only_matching_positions(): void
    {
        $data = $this->seedScenario();

        $this->actingAs($data['login'])
            ->getJson('/dashboard/data?status=placed')
            ->assertOk()
            ->assertJsonPath('kpis.total', 2)
            ->assertJsonPath('kpis.placed', 2)
            ->assertJsonPath('kpis.open', 0);
    }

    public function test_year_filter_uses_month_label(): void
    {
        $data = $this->seedScenario();

        $this->actingAs($data['login'])
            ->getJson('/dashboard/data?year=2025')
            ->assertOk()
            ->assertJsonPath('kpis.total', 1)
            ->assertJsonPath('kpis.placed', 1);
    }

    public function test_lead_recruiter_filter_narrows_results(): void
    {
        $data = $this->seedScenario();

        $this->actingAs($data['login'])
            ->getJson('/dashboard/data?lead_recruiter_id=' . $data['recruiter']->id)
            ->assertOk()
            ->assertJsonPath('kpis.total', 6)
            ->assertJsonPath('kpis.open', 2);
    }

    public function test_region_filter_groups_by_canada_or_usa(): void
    {
        $data = $this->seedScenario();

        $this->actingAs($data['login'])
            ->getJson('/dashboard/data?region=Canada')
            ->assertOk()
            ->assertJsonPath('kpis.total', 3)
            ->assertJsonPath('kpis.placed', 2);

        $this->actingAs($data['login'])
            ->getJson('/dashboard/data?region=USA')
            ->assertOk()
            ->assertJsonPath('kpis.total', 3)
            ->assertJsonPath('kpis.open', 2);
    }

    public function test_pipeline_confirmed_placement_counts_as_placed_even_without_status_17(): void
    {
        $data = $this->seedScenario();

        // An in-progress position (status 5) whose candidate is confirmed-placed in the pipeline.
        $position = TrackerInfo::create([
            'job_status_FK' => 5,
            'month_id' => $data['month']->id,
            'client_id' => $data['client']->id,
            'lr' => $data['recruiter']->id,
            'position' => 'Pipeline Placed Role',
            'priority' => 'High',
            'type_of_job' => 'remote',
            'csi' => 'Internal',
        ]);

        $candidate = Candidate::create(['full_name' => 'Jane Placed', 'email' => 'jane@example.com']);
        $trackerCandidate = TrackerCandidate::create([
            'tracker_info_id' => $position->id,
            'candidate_id' => $candidate->id,
            'current_status_id' => 12,
        ]);
        CandidatePipelineStatus::create([
            'tracker_candidate_id' => $trackerCandidate->id,
            'candidate_identified' => true,
            'final_status_placement_completion' => 'Confirmed',
        ]);

        // Now 3 placed positions: 2 via status 17 (June+May) + this pipeline one. Total 7.
        $this->actingAs($data['login'])
            ->getJson('/dashboard/data')
            ->assertOk()
            ->assertJsonPath('kpis.total', 7)
            ->assertJsonPath('kpis.placed', 3);

        // And it shows up under the placed status filter.
        $this->actingAs($data['login'])
            ->getJson('/dashboard/data?status=placed')
            ->assertOk()
            ->assertJsonPath('kpis.placed', 3)
            ->assertJsonPath('kpis.total', 3);
    }

    public function test_kpi_detail_returns_open_positions(): void
    {
        $data = $this->seedScenario();

        $this->actingAs($data['login'])
            ->getJson('/dashboard/kpi/open')
            ->assertOk()
            ->assertJsonPath('kpi', 'open')
            ->assertJsonPath('count', 2)
            ->assertJsonStructure(['title', 'subtitle', 'items' => [['id', 'position', 'client', 'concerns', 'url']]]);
    }

    public function test_kpi_attention_lists_concerns_for_overdue_and_urgent(): void
    {
        $data = $this->seedScenario();

        TrackerInfo::create([
            'job_status_FK' => 2,
            'month_id' => $data['month']->id,
            'client_id' => $data['client']->id,
            'lr' => $data['recruiter']->id,
            'position' => 'Critical Overdue Role',
            'priority' => 'Urgent',
            'submission_deadline' => Carbon::today()->subDays(3),
        ]);

        $response = $this->actingAs($data['login'])->getJson('/dashboard/kpi/attention');

        $response->assertOk()
            ->assertJsonPath('kpi', 'attention');

        $items = collect($response->json('items'));
        $critical = $items->firstWhere('position', 'Critical Overdue Role');
        $this->assertNotNull($critical);
        $this->assertTrue(
            collect($critical['concerns'])->contains(fn ($c) => str_contains($c, 'Overdue'))
        );
        $this->assertTrue(
            collect($critical['concerns'])->contains(fn ($c) => str_contains($c, 'Urgent'))
        );
    }

    public function test_dashboard_export_returns_excel_file(): void
    {
        $data = $this->seedScenario();

        $response = $this->actingAs($data['login'])->get('/dashboard/export');

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml.sheet',
            (string) $response->headers->get('content-type')
        );
        $this->assertStringContainsString(
            'RADiiX_Dashboard_Report_',
            (string) $response->headers->get('content-disposition')
        );
    }
}
