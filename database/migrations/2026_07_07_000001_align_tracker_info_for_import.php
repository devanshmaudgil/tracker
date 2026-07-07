<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Widen the cf enum to include India (and keep existing values).
        DB::statement("ALTER TABLE tracker_info MODIFY cf ENUM('Canada', 'USA', 'India') NULL COMMENT 'Country of Position fulfillment'");

        // Widen priority enum to include the tracker's real values.
        DB::statement("ALTER TABLE tracker_info MODIFY priority ENUM('Urgent', 'Low', 'High', 'Medium', 'Intermediate') NULL COMMENT 'Priority'");

        Schema::table('tracker_info', function (Blueprint $table) {
            // Excel stores deadlines as free text like "48 Hours", so keep a text field
            // alongside the existing date column.
            if (!Schema::hasColumn('tracker_info', 'submission_deadline_text')) {
                $table->string('submission_deadline_text')->nullable()->after('submission_deadline');
            }
            if (!Schema::hasColumn('tracker_info', 'notes')) {
                $table->text('notes')->nullable()->after('job_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tracker_info', function (Blueprint $table) {
            if (Schema::hasColumn('tracker_info', 'submission_deadline_text')) {
                $table->dropColumn('submission_deadline_text');
            }
            if (Schema::hasColumn('tracker_info', 'notes')) {
                $table->dropColumn('notes');
            }
        });

        DB::statement("ALTER TABLE tracker_info MODIFY priority ENUM('Urgent', 'Low', 'High', 'Medium') NULL COMMENT 'Priority'");
        DB::statement("ALTER TABLE tracker_info MODIFY cf ENUM('Canada', 'USA') NULL COMMENT 'Country of Position fulfillment'");
    }
};
