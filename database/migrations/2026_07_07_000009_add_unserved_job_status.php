<?php

use App\Models\JobStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('job_status')->updateOrInsert(
            ['status' => 'Unserved'],
            ['status_initial' => 'UN', 'created_at' => now(), 'updated_at' => now()]
        );

        $unservedId = DB::table('job_status')->where('status', 'Unserved')->value('id');

        if ($unservedId) {
            DB::table('tracker_info')
                ->where('is_unserved', true)
                ->update(['job_status_FK' => $unservedId]);
        }
    }

    public function down(): void
    {
        $unservedId = DB::table('job_status')->where('status', 'Unserved')->value('id');

        if ($unservedId) {
            DB::table('tracker_info')
                ->where('job_status_FK', $unservedId)
                ->update(['job_status_FK' => 1, 'is_unserved' => true]);
        }

        DB::table('job_status')->where('status', 'Unserved')->delete();
    }
};
