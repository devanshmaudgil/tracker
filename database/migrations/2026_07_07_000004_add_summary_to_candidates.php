<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Candidate skill / experience / notice summary (Excel column AW "NOTES").
            if (!Schema::hasColumn('candidates', 'summary')) {
                $table->text('summary')->nullable()->after('resume_file');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            if (Schema::hasColumn('candidates', 'summary')) {
                $table->dropColumn('summary');
            }
        });
    }
};
