<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracker_info_region', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tracker_info_id')->constrained('tracker_info')->onDelete('cascade');
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->unsignedInteger('openings_count')->default(1);
            $table->timestamps();

            $table->unique(['tracker_info_id', 'region_id']);
        });

        // Backfill: copy each job's existing single region into the pivot.
        DB::table('tracker_info')
            ->whereNotNull('region_id')
            ->orderBy('id')
            ->chunkById(200, function ($jobs) {
                $now = now();
                $rows = [];

                foreach ($jobs as $job) {
                    $rows[] = [
                        'tracker_info_id' => $job->id,
                        'region_id' => $job->region_id,
                        'openings_count' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('tracker_info_region')->insertOrIgnore($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracker_info_region');
    }
};
