<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_pipeline_status', function (Blueprint $table) {
            $table->boolean('requirement_reviewed')->default(false)->after('candidate_identified');
            $table->boolean('doc_govt_id_collected')->default(false)->after('candidate_shortlisted');
            $table->boolean('doc_work_auth_collected')->default(false)->after('doc_govt_id_collected');
            $table->boolean('doc_linkedin_collected')->default(false)->after('doc_work_auth_collected');
            $table->boolean('rtr_signed')->default(false)->after('doc_linkedin_collected');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_pipeline_status', function (Blueprint $table) {
            $table->dropColumn([
                'requirement_reviewed',
                'doc_govt_id_collected',
                'doc_work_auth_collected',
                'doc_linkedin_collected',
                'rtr_signed',
            ]);
        });
    }
};
