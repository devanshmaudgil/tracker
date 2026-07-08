<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_login', function (Blueprint $table) {
            $table->boolean('password_policy_compliant')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('user_login', function (Blueprint $table) {
            $table->dropColumn('password_policy_compliant');
        });
    }
};
