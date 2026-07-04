<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_pipeline_status', function (Blueprint $table) {
            $table->text('interview_transcript')->nullable()->after('placement_completion_date');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_pipeline_status', function (Blueprint $table) {
            $table->dropColumn('interview_transcript');
        });
    }
};
