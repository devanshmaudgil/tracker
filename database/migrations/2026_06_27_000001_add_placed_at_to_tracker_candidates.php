<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracker_candidates', function (Blueprint $table) {
            $table->timestamp('placed_at')->nullable()->after('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('tracker_candidates', function (Blueprint $table) {
            $table->dropColumn('placed_at');
        });
    }
};
