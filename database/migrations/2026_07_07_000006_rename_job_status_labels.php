<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('job_status')
            ->where('status', 'Client Decision')
            ->update([
                'status' => 'Client Decision Awaited',
                'status_initial' => 'CD',
            ]);

        DB::table('job_status')
            ->where('status', 'Candidate Placement Confirmed')
            ->update([
                'status' => 'Candidate Placement Completed',
                'status_initial' => 'CP',
            ]);
    }

    public function down(): void
    {
        DB::table('job_status')
            ->where('status', 'Client Decision Awaited')
            ->update([
                'status' => 'Client Decision',
                'status_initial' => 'CD',
            ]);

        DB::table('job_status')
            ->where('status', 'Candidate Placement Completed')
            ->update([
                'status' => 'Candidate Placement Confirmed',
                'status_initial' => 'CP',
            ]);
    }
};
