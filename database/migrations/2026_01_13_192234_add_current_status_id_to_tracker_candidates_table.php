<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tracker_candidates', function (Blueprint $table) {
            $table->unsignedBigInteger('current_status_id')->nullable()->after('candidate_id');
            $table->foreign('current_status_id')->references('id')->on('job_status')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracker_candidates', function (Blueprint $table) {
            $table->dropForeign(['current_status_id']);
            $table->dropColumn('current_status_id');
        });
    }
};
