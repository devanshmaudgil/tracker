<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Widen client_decision to include the tracker's real value.
        DB::statement("ALTER TABLE candidate_pipeline_status MODIFY client_decision ENUM('Selected', 'Rejected', 'On Hold', 'Selected but declined the offer') NULL");

        Schema::table('candidate_pipeline_status', function (Blueprint $table) {
            // Recruiter Notes (Excel column AU) - availability, other offers, call logs.
            if (!Schema::hasColumn('candidate_pipeline_status', 'recruiter_notes')) {
                $table->text('recruiter_notes')->nullable()->after('interview_transcript');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidate_pipeline_status', function (Blueprint $table) {
            if (Schema::hasColumn('candidate_pipeline_status', 'recruiter_notes')) {
                $table->dropColumn('recruiter_notes');
            }
        });

        DB::statement("ALTER TABLE candidate_pipeline_status MODIFY client_decision ENUM('Selected', 'Rejected', 'On Hold') NULL");
    }
};
