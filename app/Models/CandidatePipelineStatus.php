<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidatePipelineStatus extends Model
{
    protected $table = 'candidate_pipeline_status';

    protected $fillable = [
        'tracker_candidate_id',
        'candidate_identified',
        'resume_reviewed_by_recruiter',
        'resume_reviewed_date',
        'recruiter_screening_call',
        'recruiter_screening_call_date',
        'candidate_shortlisted',
        'resume_submitted_to_client',
        'radix_internal_interview_prep',
        'radix_internal_interview_prep_date',
        'client_resume_review',
        'client_interview_round_1_date',
        'client_interview_round_2_date',
        'additional_rounds',
        'client_decision',
        'client_decision_date',
        'client_confirmation_received',
        'client_confirmation_date',
        'offer_extended_to_candidate',
        'offer_extended_date',
        'background_check',
        'candidate_project_start_date',
        'final_status_placement_completion',
        'placement_completion_date',
    ];

    protected function casts(): array
    {
        return [
            'candidate_identified' => 'boolean',
            'resume_reviewed_date' => 'date',
            'recruiter_screening_call_date' => 'date',
            'candidate_shortlisted' => 'boolean',
            'radix_internal_interview_prep_date' => 'date',
            'client_interview_round_1_date' => 'date',
            'client_interview_round_2_date' => 'date',
            'additional_rounds' => 'boolean',
            'client_decision_date' => 'date',
            'client_confirmation_received' => 'boolean',
            'client_confirmation_date' => 'date',
            'offer_extended_to_candidate' => 'boolean',
            'offer_extended_date' => 'date',
            'candidate_project_start_date' => 'date',
            'placement_completion_date' => 'date',
        ];
    }

    public function trackerCandidate()
    {
        return $this->belongsTo(TrackerCandidate::class, 'tracker_candidate_id');
    }
}
