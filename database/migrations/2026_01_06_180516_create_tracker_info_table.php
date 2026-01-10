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
        Schema::create('tracker_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('month_id')->constrained('months')->onDelete('cascade');
            $table->date('prd')->nullable()->comment('Position Receiving Date');
            $table->enum('cf', ['Canada', 'USA'])->nullable()->comment('Country of Position fulfillment');
            $table->string('country')->nullable();
            $table->string('position')->nullable();
            $table->foreignId('lr')->nullable()->constrained('staff_users')->onDelete('set null')->comment('Lead Recruiter');
            $table->enum('csi', ['Internal', 'External', 'Dice', 'Linkedin', 'Others'])->nullable()->comment('Candidate Source Info');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracker_info');
    }
};
