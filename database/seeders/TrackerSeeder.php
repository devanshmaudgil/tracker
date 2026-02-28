<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrackerInfo;
use App\Models\Candidate;
use App\Models\TrackerCandidate;
use App\Models\Client;
use App\Models\Region;
use App\Models\StaffUser;
use App\Models\Month;
use App\Models\CandidatePipelineStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrackerSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure basic reference data exists
        $months = [];
        $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        foreach ($monthNames as $name) {
            $months[] = Month::firstOrCreate(['month' => $name . ' ' . date('Y')]);
        }

        $clients = [];
        $clientNames = ['Google', 'Microsoft', 'Amazon', 'Netflix', 'Tesla', 'Adobe', 'Salesforce', 'Oracle', 'IBM', 'Intel'];
        foreach ($clientNames as $name) {
            $clients[] = Client::firstOrCreate(['client' => $name]);
        }

        $regions = [];
        $regionNames = [
            ['city' => 'New York', 'region' => 'NY'],
            ['city' => 'San Francisco', 'region' => 'CA'],
            ['city' => 'Austin', 'region' => 'TX'],
            ['city' => 'Seattle', 'region' => 'WA'],
            ['city' => 'Chicago', 'region' => 'IL'],
            ['city' => 'Boston', 'region' => 'MA'],
            ['city' => 'Denver', 'region' => 'CO'],
            ['city' => 'Atlanta', 'region' => 'GA'],
        ];
        foreach ($regionNames as $data) {
            $regions[] = Region::firstOrCreate($data);
        }

        $recruiters = [];
        $recruiterNames = ['Alice.Smith', 'Bob.Jones', 'Charlie.Brown', 'Diana.Prince', 'Evan.Wright'];
        foreach ($recruiterNames as $name) {
            $recruiters[] = StaffUser::firstOrCreate(
                ['username' => $name],
                [
                    'phone_number' => '555-' . rand(1000, 9999),
                    'date_of_birth' => '1990-01-01',
                    'remarks' => 'Seeded Recruiter'
                ]
            );
        }

        // 2. Create Candidates (Pool of ~150 candidates)
        $candidates = [];
        $firstNames = ['John', 'Jane', 'Michael', 'Emily', 'David', 'Sarah', 'James', 'Emma', 'Robert', 'Olivia', 'William', 'Ava', 'Joseph', 'Isabella', 'Thomas', 'Sophia', 'Charles', 'Mia', 'Daniel', 'Charlotte'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];
        
        for ($i = 0; $i < 150; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $fullName = "$firstName $lastName";
            
            $candidates[] = Candidate::create([
                'full_name' => $fullName,
                'email' => strtolower("$firstName.$lastName" . rand(100, 999) . '@example.com'),
                'phone' => '555-' . rand(100, 999) . '-' . rand(1000, 9999),
                'location_id' => $regions[array_rand($regions)]->id,
                'work_status' => ['Citizen', 'GC', 'H1B', 'OPT'][rand(0, 3)],
                'current_company' => $clientNames[array_rand($clientNames)] . ' ' . ['Corp', 'Inc', 'LLC', 'Systems'][rand(0, 3)],
                'pay_rate' => '$' . rand(50, 150) . '/hr',
                'agency_name' => ['TekSystems', 'Randstad', 'Robert Half', 'Kelly Services', 'Manpower'][rand(0, 4)],
                'agency_poc' => $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)],
                'agency_poc_phone' => '555-' . rand(100, 999) . '-' . rand(1000, 9999),
            ]);
        }

        // 3. Create Job Trackers & Assign Candidates
        $positions = ['Java Developer', 'Python Developer', 'React Engineer', 'DevOps Specialist', 'Project Manager', 'Business Analyst', 'QA Engineer', 'Data Scientist', 'Full Stack Developer', 'Cloud Architect'];
        
        // We want about 100-110 TOTAL assignments (TrackerCandidate records), so we'll create ~40 jobs and assign ~2-3 candidates to each.
        
        $jobCount = 40;
        
        for ($j = 0; $j < $jobCount; $j++) {
            $month = $months[array_rand($months)];
            $client = $clients[array_rand($clients)];
            
            $tracker = TrackerInfo::create([
                'month_id' => $month->id,
                'client_id' => $client->id,
                'region_id' => $regions[array_rand($regions)]->id,
                'lr' => $recruiters[array_rand($recruiters)]->id,
                'job_status_FK' => rand(1, 5), // Mostly open jobs
                'prd' => Carbon::parse($month->month)->addDays(rand(1, 28)),
                'cf' => ['USA', 'Canada'][rand(0, 1)],
                'position' => $positions[array_rand($positions)],
                'country' => 'USA',
                'type_of_job' => ['onsite', 'remote', 'hybrid'][rand(0, 2)],
                'bill_rate_salary_range' => '$' . rand(60, 100) . '/hr',
                'priority' => ['High', 'Medium', 'Low', 'Urgent'][rand(0, 3)],
                'submission_deadline' => Carbon::parse($month->month)->addDays(rand(5, 30)),
                'csi' => ['Internal', 'External', 'Linkedin'][rand(0, 2)],
            ]);

            // Assign 1 to 4 candidates to this job
            $assignCount = rand(1, 4);
            $jobCandidates = [];
            
            for ($k = 0; $k < $assignCount; $k++) {
                $candidate = $candidates[array_rand($candidates)];
                
                // Avoid duplicate assignment to same job
                if (in_array($candidate->id, $jobCandidates)) continue;
                $jobCandidates[] = $candidate->id;

                // Determine pipeline progress (random but logical)
                // 1=Identified, ..., 18=Rejected
                // We'll simulate a funnel: lots at early stages, few at later stages
                $progressDice = rand(1, 100);
                
                $statusId = 2; // Default: Identified
                $pipelineData = ['candidate_identified' => true];
                
                if ($progressDice > 20) { // 80% pass screening
                    $statusId = 3;
                    $pipelineData['resume_reviewed_by_recruiter'] = 'Completed';
                    $pipelineData['resume_reviewed_date'] = Carbon::now()->subDays(rand(10, 20));
                }
                if ($progressDice > 40) { // 60% pass recruiter call
                    $statusId = 5;
                    $pipelineData['recruiter_screening_call'] = 'Completed';
                    $pipelineData['recruiter_screening_call_date'] = Carbon::now()->subDays(rand(8, 15));
                    $pipelineData['candidate_shortlisted'] = true;
                }
                if ($progressDice > 55) { // 45% submitted to client
                    $statusId = 6;
                    $pipelineData['resume_submitted_to_client'] = 'Submitted';
                }
                if ($progressDice > 70) { // 30% client interview
                    $statusId = 9;
                    $pipelineData['client_resume_review'] = 'Approved';
                    $pipelineData['client_interview_round_1_date'] = Carbon::now()->subDays(rand(5, 10));
                }
                if ($progressDice > 85) { // 15% second round
                    $statusId = 10;
                    $pipelineData['client_interview_round_2_date'] = Carbon::now()->subDays(rand(1, 5));
                }
                if ($progressDice > 95) { // 5% Offer/Placed
                    $statusId = 17;
                    $pipelineData['client_decision'] = 'Selected';
                    $pipelineData['client_decision_date'] = Carbon::now()->subDays(2);
                    $pipelineData['client_confirmation_received'] = true;
                    $pipelineData['offer_extended_to_candidate'] = true;
                    $pipelineData['final_status_placement_completion'] = 'Confirmed';
                }

                $trackerCandidate = TrackerCandidate::create([
                    'tracker_info_id' => $tracker->id,
                    'candidate_id' => $candidate->id,
                    'current_status_id' => $statusId,
                ]);

                $pipelineData['tracker_candidate_id'] = $trackerCandidate->id;
                CandidatePipelineStatus::create($pipelineData);
            }
        }
    }
}
