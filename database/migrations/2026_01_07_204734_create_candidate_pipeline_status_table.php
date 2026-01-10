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
        Schema::create('candidate_pipeline_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tracker_candidate_id')->constrained('tracker_candidates')->onDelete('cascade');
            
            // Stage 1: Candidate Identified
            $table->boolean('candidate_identified')->default(false)->comment('Candidate Identified (Yes/No)');
            
            // Stage 2: Resume Reviewed by Recruiter
            $table->enum('resume_reviewed_by_recruiter', ['Completed', 'Pending'])->nullable()->comment('Resume Reviewed by Recruiter');
            $table->date('resume_reviewed_date')->nullable();
            
            // Stage 3: Recruiter Screening Call
            $table->enum('recruiter_screening_call', ['Completed', 'Pending', 'No Show'])->nullable()->comment('Recruiter Screening Call');
            $table->date('recruiter_screening_call_date')->nullable();
            
            // Stage 4: Candidate Shortlisted
            $table->boolean('candidate_shortlisted')->default(false)->comment('Candidate Shortlisted (Yes/No)');
            
            // Stage 5: Resume Submitted to Client
            $table->enum('resume_submitted_to_client', ['Submitted', 'Not Submitted'])->nullable();
            
            // Stage 6: RADIX Internal Interview Prep
            $table->enum('radix_internal_interview_prep', ['Completed', 'Planned', 'Not Required'])->nullable();
            $table->date('radix_internal_interview_prep_date')->nullable();
            
            // Stage 7: Client Resume Review
            $table->enum('client_resume_review', ['Approved', 'Rejected'])->nullable();
            
            // Stage 8: Client Interview - Round 1
            $table->date('client_interview_round_1_date')->nullable();
            
            // Stage 9: Client Interview - Round 2
            $table->date('client_interview_round_2_date')->nullable();
            
            // Stage 10: Additional Rounds
            $table->boolean('additional_rounds')->default(false)->comment('Additional Rounds (Tech/Manager/Panel) (Yes/No)');
            
            // Stage 11: Client Decision
            $table->enum('client_decision', ['Selected', 'Rejected', 'On Hold'])->nullable();
            $table->date('client_decision_date')->nullable();
            
            // Stage 12: Client Confirmation Received
            $table->boolean('client_confirmation_received')->default(false);
            $table->date('client_confirmation_date')->nullable();
            
            // Stage 13: Offer Extended to Candidate
            $table->boolean('offer_extended_to_candidate')->default(false);
            $table->date('offer_extended_date')->nullable();
            
            // Stage 14: Background Check
            $table->enum('background_check', ['Completed', 'Initiated', 'Pending'])->nullable();
            
            // Stage 15: Candidate Project Start Date
            $table->date('candidate_project_start_date')->nullable();
            
            // Stage 16: Final Status - Placement Completion
            $table->enum('final_status_placement_completion', ['Confirmed', 'Not Confirmed'])->nullable();
            $table->date('placement_completion_date')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_pipeline_status');
    }
};
