<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'Demand Raised',
            'Candidate Identified',
            'Resume Reviewed',
            'Screening Call',
            'Shortlisted',
            'Submitted to client',
            'Internal Prep',
            'Client Review',
            'Round 1',
            'Round 2',
            'Additional Round',
            'Client Decision',
            'Client Confirmation Recieved',
            'Offer Extended to Candidate',
            'Background Check',
            'Candidate Project Start',
            'Candidate Placement Confirmed',
            'Candidate Placement Rejected',
        ];

        foreach ($statuses as $status) {
            $words = explode(' ', $status);
            $initial = '';
            if (count($words) >= 1) {
                $initial .= strtoupper(substr($words[0], 0, 1));
            }
            if (count($words) >= 2) {
                $initial .= strtoupper(substr($words[1], 0, 1));
            }

            \App\Models\JobStatus::create([
                'status' => $status,
                'status_initial' => $initial,
            ]);
        }
    }
}
