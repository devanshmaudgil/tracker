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
            $table->foreignId('client_id')->nullable()->after('month_id')->constrained('clients')->onDelete('set null')->comment('Client Foreign Key');
            $table->foreignId('region_id')->nullable()->after('client_id')->constrained('regions')->onDelete('set null')->comment('Job Location - Region Foreign Key');
            $table->enum('type_of_job', ['onsite', 'remote', 'hybrid'])->nullable()->after('region_id')->comment('Type of Job');
            $table->string('bill_rate_salary_range')->nullable()->after('type_of_job')->comment('Bill Rate / Salary Range');
            $table->enum('priority', ['Urgent', 'Low', 'High', 'Medium'])->nullable()->after('bill_rate_salary_range')->comment('Priority');
            $table->date('submission_deadline')->nullable()->after('priority')->comment('Submission Deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracker_info', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['region_id']);
            $table->dropColumn(['client_id', 'region_id', 'type_of_job', 'bill_rate_salary_range', 'priority', 'submission_deadline']);
        });
    }
};
