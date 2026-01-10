<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->foreignId('location_id')->nullable()->constrained('regions')->onDelete('set null')->comment('Candidate Location (City, State)');
            $table->enum('work_status', ['GC', 'PR', 'Citizen', 'H1B', 'OPT'])->nullable()->comment('Candidate Work Status');
            $table->string('current_company')->nullable();
            $table->string('pay_rate')->nullable();
            $table->string('agency_name')->nullable();
            $table->string('agency_poc')->nullable()->comment('Candidate Agency POC (Point-of-Contact)');
            $table->string('agency_poc_phone')->nullable()->comment('Candidate Agency POC Phone Number');
            $table->string('resume_file')->nullable()->comment('Resume Link / File Name - stored in Supabase');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
