<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_login', function (Blueprint $table) {
            $table->foreignId('staff_user_id')->nullable()->after('id')
                ->constrained('staff_users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_login', function (Blueprint $table) {
            $table->dropForeign(['staff_user_id']);
            $table->dropColumn('staff_user_id');
        });
    }
};
