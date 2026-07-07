<?php

use App\Models\TrackerInfo;
use App\Services\Tracker\SubmissionDeadlineResolver;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        TrackerInfo::query()
            ->whereNotNull('submission_deadline_text')
            ->orderBy('id')
            ->chunkById(100, function ($trackers) {
                foreach ($trackers as $tracker) {
                    $resolved = SubmissionDeadlineResolver::resolve(
                        $tracker->submission_deadline_text,
                        $tracker->prd ? Carbon::parse($tracker->prd) : null,
                    );

                    if ($resolved) {
                        $tracker->submission_deadline = $resolved;
                    }

                    $tracker->submission_deadline_text = null;
                    $tracker->save();
                }
            });

        if (Schema::hasColumn('tracker_info', 'submission_deadline_text')) {
            Schema::table('tracker_info', function (Blueprint $table) {
                $table->dropColumn('submission_deadline_text');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('tracker_info', 'submission_deadline_text')) {
            Schema::table('tracker_info', function (Blueprint $table) {
                $table->string('submission_deadline_text')->nullable()->after('submission_deadline');
            });
        }
    }
};
