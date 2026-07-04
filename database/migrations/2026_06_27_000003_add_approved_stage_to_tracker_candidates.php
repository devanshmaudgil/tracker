<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracker_candidates', function (Blueprint $table) {
            $table->string('approved_stage', 32)->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('tracker_candidates', function (Blueprint $table) {
            $table->dropColumn('approved_stage');
        });
    }
};
