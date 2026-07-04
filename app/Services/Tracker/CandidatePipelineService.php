<?php

namespace App\Services\Tracker;

use App\Models\CandidatePipelineStatus;
use App\Models\JobStatus;
use App\Models\TrackerCandidate;

class CandidatePipelineService
{
    /** @var list<string> Pre-submission checklist order (used for cascade uncheck). */
    public const CHECKLIST_FIELD_ORDER = [
        'requirement_reviewed',
        'candidate_identified',
        'resume_reviewed',
        'screening_call',
        'doc_resume',
        'doc_govt_id_collected',
        'doc_work_auth_collected',
        'doc_linkedin_collected',
        'rtr_signed',
        'candidate_shortlisted',
        'submitted_to_client',
    ];

    /**
     * Derive the furthest pipeline stage reached from pipeline data.
     *
     * @param  int|null  $cap  When set (e.g. 6), stops at that status for checklist-only sync.
     */
    public function deriveStatusFromPipeline(
        ?CandidatePipelineStatus $pipeline,
        bool $hasResume,
        ?int $cap = null
    ): int {
        if (!$pipeline || !$pipeline->candidate_identified) {
            return 2;
        }

        $status = 2;

        if ($pipeline->requirement_reviewed
            && $pipeline->resume_reviewed_by_recruiter === 'Completed') {
            $status = 3;
        }

        if ($status >= 3 && $pipeline->recruiter_screening_call === 'Completed') {
            $status = 4;
        }

        if ($status >= 4 && $pipeline->candidate_shortlisted) {
            $status = 5;
        }

        if ($status >= 5
            && $this->resumeOnFile($pipeline, $hasResume)
            && $pipeline->doc_govt_id_collected
            && $pipeline->doc_work_auth_collected
            && $pipeline->doc_linkedin_collected
            && $pipeline->rtr_signed
            && $pipeline->resume_submitted_to_client === 'Submitted') {
            $status = 6;
        }

        if ($cap === 6) {
            return $status;
        }

        if ($status >= 6 && in_array($pipeline->radix_internal_interview_prep, ['Planned', 'Completed', 'Not Required'], true)) {
            $status = 7;
        }

        if ($status >= 7 && $pipeline->client_resume_review === 'Approved') {
            $status = 8;
        }

        if ($status >= 8 && $pipeline->client_interview_round_1_date) {
            $status = 9;
        }

        if ($status >= 9 && $pipeline->client_interview_round_2_date) {
            $status = 10;
        }

        if ($status >= 10 && $pipeline->additional_rounds) {
            $status = 11;
        }

        if ($status >= 10 && $pipeline->client_decision) {
            $status = max($status, 12);
        }

        if ($status >= 12 && $pipeline->client_decision === 'Selected') {
            $status = max($status, 12);
        }

        if ($status >= 12 && $pipeline->client_confirmation_received) {
            $status = 13;
        }

        if ($status >= 13 && $pipeline->offer_extended_to_candidate) {
            $status = 14;
        }

        if ($status >= 14 && in_array($pipeline->background_check, ['Initiated', 'Completed'], true)) {
            $status = 15;
        }

        if ($status >= 15 && $pipeline->background_check === 'Completed') {
            $status = max($status, 15);
        }

        if ($status >= 15 && $pipeline->candidate_project_start_date) {
            $status = 16;
        }

        if ($status >= 16 && $pipeline->final_status_placement_completion === 'Confirmed') {
            $status = 17;
        }

        if ($pipeline->final_status_placement_completion === 'Not Confirmed') {
            $status = 18;
        }

        return $status;
    }

    /**
     * Never regress status; apply optional checklist cap when merging.
     */
    public function resolveStatusId(
        ?CandidatePipelineStatus $pipeline,
        bool $hasResume,
        int $currentStatusId,
        bool $checklistCapOnly = false
    ): int {
        $cap = $checklistCapOnly ? 6 : null;
        $derived = $this->deriveStatusFromPipeline($pipeline, $hasResume, $cap);

        return max($currentStatusId, $derived);
    }

    /**
     * Bidirectional status for checklist changes (pre-submission only, cap 6).
     */
    public function resolveChecklistStatusId(?CandidatePipelineStatus $pipeline, bool $hasResume): int
    {
        return $this->deriveStatusFromPipeline($pipeline, $hasResume, 6);
    }

    /**
     * @return list<string>
     */
    public function downstreamChecklistFields(string $field): array
    {
        $index = array_search($field, self::CHECKLIST_FIELD_ORDER, true);
        if ($index === false) {
            return [];
        }

        return array_slice(self::CHECKLIST_FIELD_ORDER, $index + 1);
    }

    /**
     * @return list<string> Field keys that would be cleared (excluding skipped readonly fields).
     */
    public function cascadeUncheckPreview(string $field, bool $hasResume): array
    {
        $affected = [$field];

        foreach ($this->downstreamChecklistFields($field) as $downstream) {
            if ($downstream === 'doc_resume' && $hasResume) {
                continue;
            }
            $affected[] = $downstream;
        }

        return $affected;
    }

    /**
     * Apply a checklist toggle; unchecking cascades to all downstream pre-submission steps.
     *
     * @return array<string, mixed>
     */
    public function applyChecklistUpdate(string $field, bool $checked, bool $hasResume): array
    {
        $updates = $this->applyChecklistField($field, $checked);

        if (!$checked) {
            foreach ($this->downstreamChecklistFields($field) as $downstream) {
                if ($downstream === 'doc_resume' && $hasResume) {
                    continue;
                }
                $updates = array_merge($updates, $this->applyChecklistField($downstream, false));
            }
        }

        return $updates;
    }

    /**
     * @return array<string, string>
     */
    public function checklistFieldLabels(): array
    {
        return [
            'requirement_reviewed' => 'Requirement understood (JD reviewed)',
            'candidate_identified' => 'Candidate sourced & identified',
            'resume_reviewed' => 'Resume reviewed by recruiter',
            'screening_call' => 'Initial screening call completed',
            'doc_resume' => 'Updated resume on file',
            'doc_govt_id_collected' => 'Government photo ID collected',
            'doc_work_auth_collected' => 'Work authorization copy collected',
            'doc_linkedin_collected' => 'LinkedIn profile link collected',
            'rtr_signed' => 'Signed RTR (Right to Represent) obtained',
            'candidate_shortlisted' => 'Candidate shortlisted for submission',
            'submitted_to_client' => 'Submitted to client',
        ];
    }

    /**
     * Pre-submission pipeline fields for frontend row/drawer sync.
     *
     * @return array<string, mixed>
     */
    public function preSubmissionPipelineSnapshot(?CandidatePipelineStatus $pipeline, bool $hasResume): array
    {
        $p = $pipeline;

        return [
            'candidate_identified' => (bool) ($p?->candidate_identified),
            'requirement_reviewed' => (bool) ($p?->requirement_reviewed),
            'doc_resume_collected' => (bool) ($p?->doc_resume_collected),
            'doc_govt_id_collected' => (bool) ($p?->doc_govt_id_collected),
            'doc_work_auth_collected' => (bool) ($p?->doc_work_auth_collected),
            'doc_linkedin_collected' => (bool) ($p?->doc_linkedin_collected),
            'rtr_signed' => (bool) ($p?->rtr_signed),
            'has_resume' => $hasResume,
            'resume_reviewed_by_recruiter' => $p?->resume_reviewed_by_recruiter ?? 'Pending',
            'resume_reviewed_date' => $p?->resume_reviewed_date?->format('Y-m-d'),
            'recruiter_screening_call' => $p?->recruiter_screening_call ?? 'Pending',
            'recruiter_screening_call_date' => $p?->recruiter_screening_call_date?->format('Y-m-d'),
            'candidate_shortlisted' => (bool) ($p?->candidate_shortlisted),
            'resume_submitted_to_client' => $p?->resume_submitted_to_client ?? 'Not Submitted',
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, done: bool, readonly?: bool}>
     */
    public function checklistItems(?CandidatePipelineStatus $pipeline, bool $hasResume): array
    {
        $p = $pipeline;

        return [
            ['key' => 'requirement_reviewed', 'label' => 'Requirement understood (JD reviewed)', 'done' => (bool) ($p?->requirement_reviewed)],
            ['key' => 'candidate_identified', 'label' => 'Candidate sourced & identified', 'done' => (bool) ($p?->candidate_identified)],
            ['key' => 'resume_reviewed', 'label' => 'Resume reviewed by recruiter', 'done' => $p?->resume_reviewed_by_recruiter === 'Completed'],
            ['key' => 'screening_call', 'label' => 'Initial screening call completed', 'done' => $p?->recruiter_screening_call === 'Completed'],
            ['key' => 'doc_resume', 'label' => 'Updated resume on file', 'done' => $this->resumeOnFile($p, $hasResume), 'readonly' => $hasResume],
            ['key' => 'doc_govt_id_collected', 'label' => 'Government photo ID collected', 'done' => (bool) ($p?->doc_govt_id_collected)],
            ['key' => 'doc_work_auth_collected', 'label' => 'Work authorization copy collected', 'done' => (bool) ($p?->doc_work_auth_collected)],
            ['key' => 'doc_linkedin_collected', 'label' => 'LinkedIn profile link collected', 'done' => (bool) ($p?->doc_linkedin_collected)],
            ['key' => 'rtr_signed', 'label' => 'Signed RTR (Right to Represent) obtained', 'done' => (bool) ($p?->rtr_signed)],
            ['key' => 'candidate_shortlisted', 'label' => 'Candidate shortlisted for submission', 'done' => (bool) ($p?->candidate_shortlisted)],
            ['key' => 'submitted_to_client', 'label' => 'Submitted to client', 'done' => $p?->resume_submitted_to_client === 'Submitted'],
        ];
    }

    public function checklistProgress(array $items): int
    {
        $total = count($items);
        if ($total === 0) {
            return 0;
        }

        $done = count(array_filter($items, fn ($i) => $i['done']));

        return (int) round(($done / $total) * 100);
    }

    public function isChecklistComplete(?CandidatePipelineStatus $pipeline, bool $hasResume): bool
    {
        return $this->checklistProgress($this->checklistItems($pipeline, $hasResume)) === 100;
    }

    public function isPipelinePlaced(?CandidatePipelineStatus $pipeline): bool
    {
        return $pipeline?->final_status_placement_completion === 'Confirmed';
    }

    /**
     * Apply a checklist toggle to pipeline fields.
     *
     * @return array<string, mixed>
     */
    public function applyChecklistField(string $field, bool $checked): array
    {
        return match ($field) {
            'requirement_reviewed' => ['requirement_reviewed' => $checked],
            'candidate_identified' => ['candidate_identified' => $checked],
            'resume_reviewed' => [
                'resume_reviewed_by_recruiter' => $checked ? 'Completed' : 'Pending',
                'resume_reviewed_date' => $checked ? now()->toDateString() : null,
            ],
            'screening_call' => [
                'recruiter_screening_call' => $checked ? 'Completed' : 'Pending',
                'recruiter_screening_call_date' => $checked ? now()->toDateString() : null,
            ],
            'doc_resume' => ['doc_resume_collected' => $checked],
            'doc_govt_id_collected' => ['doc_govt_id_collected' => $checked],
            'doc_work_auth_collected' => ['doc_work_auth_collected' => $checked],
            'doc_linkedin_collected' => ['doc_linkedin_collected' => $checked],
            'rtr_signed' => ['rtr_signed' => $checked],
            'candidate_shortlisted' => ['candidate_shortlisted' => $checked],
            'submitted_to_client' => [
                'resume_submitted_to_client' => $checked ? 'Submitted' : 'Not Submitted',
            ],
            default => [],
        };
    }

    public function isChecklistField(string $field): bool
    {
        return in_array($field, [
            'requirement_reviewed',
            'candidate_identified',
            'resume_reviewed',
            'screening_call',
            'doc_resume',
            'doc_govt_id_collected',
            'doc_work_auth_collected',
            'doc_linkedin_collected',
            'rtr_signed',
            'candidate_shortlisted',
            'submitted_to_client',
        ], true);
    }

    public function isChecklistFieldDone(string $field, ?CandidatePipelineStatus $pipeline, bool $hasResume): bool
    {
        foreach ($this->checklistItems($pipeline, $hasResume) as $item) {
            if ($item['key'] === $field) {
                return $item['done'];
            }
        }

        return false;
    }

    /**
     * Checklist fields that expose inline pipeline controls (status, dates, etc.).
     *
     * @return list<string>
     */
    public function checklistFieldsWithPipelineCards(): array
    {
        return ['resume_reviewed', 'screening_call', 'submitted_to_client'];
    }

    /**
     * Apply inline pipeline edits from a checklist card (direct fields only).
     *
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    public function buildChecklistDetailUpdates(string $field, array $details): array
    {
        return match ($field) {
            'resume_reviewed' => $this->resumeReviewDetails($details),
            'screening_call' => $this->screeningCallDetails($details),
            'submitted_to_client' => $this->submittedToClientDetails($details),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function downstreamCascadeUpdates(string $field, bool $hasResume): array
    {
        $merged = [];

        foreach ($this->downstreamChecklistFields($field) as $downstream) {
            if ($downstream === 'doc_resume' && $hasResume) {
                continue;
            }
            $merged = array_merge($merged, $this->applyChecklistField($downstream, false));
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    private function resumeReviewDetails(array $details): array
    {
        $updates = [];

        if (array_key_exists('resume_reviewed_by_recruiter', $details)) {
            $status = $details['resume_reviewed_by_recruiter'];
            $updates['resume_reviewed_by_recruiter'] = $status;
            if ($status !== 'Completed') {
                $updates['resume_reviewed_date'] = null;
            } elseif (!array_key_exists('resume_reviewed_date', $details)) {
                $updates['resume_reviewed_date'] = now()->toDateString();
            }
        }

        if (array_key_exists('resume_reviewed_date', $details)) {
            $updates['resume_reviewed_date'] = $details['resume_reviewed_date'] ?: null;
        }

        return $updates;
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    private function screeningCallDetails(array $details): array
    {
        $updates = [];

        if (array_key_exists('recruiter_screening_call', $details)) {
            $status = $details['recruiter_screening_call'];
            $updates['recruiter_screening_call'] = $status;
            if ($status !== 'Completed') {
                $updates['recruiter_screening_call_date'] = null;
            } elseif (!array_key_exists('recruiter_screening_call_date', $details)) {
                $updates['recruiter_screening_call_date'] = now()->toDateString();
            }
        }

        if (array_key_exists('recruiter_screening_call_date', $details)) {
            $updates['recruiter_screening_call_date'] = $details['recruiter_screening_call_date'] ?: null;
        }

        return $updates;
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    private function submittedToClientDetails(array $details): array
    {
        if (!array_key_exists('resume_submitted_to_client', $details)) {
            return [];
        }

        return ['resume_submitted_to_client' => $details['resume_submitted_to_client']];
    }

    private function resumeOnFile(?CandidatePipelineStatus $pipeline, bool $hasResume): bool
    {
        return $hasResume || (bool) ($pipeline?->doc_resume_collected);
    }

    /**
     * Clear rejection/approval and delete pipeline so checklist & journey restart from scratch.
     */
    public function resetCandidateToFreshStart(TrackerCandidate $trackerCandidate): void
    {
        if ($trackerCandidate->pipelineStatus) {
            $trackerCandidate->pipelineStatus->delete();
            $trackerCandidate->unsetRelation('pipelineStatus');
        }

        $identifiedStatusId = JobStatus::where('status', 'Candidate Identified')->value('id') ?? 2;

        $trackerCandidate->update([
            'rejected_at' => null,
            'rejection_reason' => null,
            'approved_at' => null,
            'approved_stage' => null,
            'current_status_id' => $identifiedStatusId,
        ]);
    }
}
