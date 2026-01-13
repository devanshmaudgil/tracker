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
        Schema::table('tracker_info', function (Blueprint $table) {
            $table->unsignedBigInteger('job_status_FK')->nullable()->after('id');
            $table->foreign('job_status_FK')->references('id')->on('job_status')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracker_info', function (Blueprint $table) {
            $table->dropForeign(['job_status_FK']);
            $table->dropColumn('job_status_FK');
        });
    }
};
