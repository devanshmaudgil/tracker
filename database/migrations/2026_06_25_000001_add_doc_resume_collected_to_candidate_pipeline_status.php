<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_pipeline_status', function (Blueprint $table) {
            $table->boolean('doc_resume_collected')->default(false)->after('candidate_shortlisted');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_pipeline_status', function (Blueprint $table) {
            $table->dropColumn('doc_resume_collected');
        });
    }
};
