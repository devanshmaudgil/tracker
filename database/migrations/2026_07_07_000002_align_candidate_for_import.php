<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Placement pay rate (Excel column AL) is distinct from the sourcing pay rate.
            if (!Schema::hasColumn('candidates', 'placement_pay_rate')) {
                $table->string('placement_pay_rate')->nullable()->after('pay_rate');
            }
            // Free-text location string from Excel when it cannot be matched to a region.
            if (!Schema::hasColumn('candidates', 'location_text')) {
                $table->string('location_text')->nullable()->after('location_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            if (Schema::hasColumn('candidates', 'placement_pay_rate')) {
                $table->dropColumn('placement_pay_rate');
            }
            if (Schema::hasColumn('candidates', 'location_text')) {
                $table->dropColumn('location_text');
            }
        });
    }
};
