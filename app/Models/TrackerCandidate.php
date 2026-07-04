<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackerCandidate extends Model
{
    public const APPROVED_STAGE_ON_HOLD = 'on_hold';

    public const APPROVED_STAGE_IN_PROGRESS = 'in_progress';

    public const APPROVED_STAGE_SUBMITTED = 'submitted_to_client';

    public const APPROVED_STAGE_AWAITED = 'awaited_from_client';

    /** @var list<string> */
    public const APPROVED_STAGES = [
        self::APPROVED_STAGE_ON_HOLD,
        self::APPROVED_STAGE_IN_PROGRESS,
        self::APPROVED_STAGE_SUBMITTED,
        self::APPROVED_STAGE_AWAITED,
    ];

    /** @var list<string> */
    public const PIPELINE_APPROVED_STAGES = [
        self::APPROVED_STAGE_ON_HOLD,
        self::APPROVED_STAGE_IN_PROGRESS,
    ];

    /** @var list<string> */
    public const SUBMITTED_APPROVED_STAGES = [
        self::APPROVED_STAGE_SUBMITTED,
        self::APPROVED_STAGE_AWAITED,
    ];

    protected $fillable = [
        'tracker_info_id',
        'candidate_id',
        'current_status_id',
        'rejection_reason',
        'rejected_at',
        'approved_at',
        'approved_stage',
    ];

    protected $casts = [
        'rejected_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function isPipelinePlaced(): bool
    {
        return $this->pipelineStatus?->final_status_placement_completion === 'Confirmed';
    }

    /**
     * @return array<string, string>
     */
    public static function approvedStageLabels(): array
    {
        return [
            self::APPROVED_STAGE_ON_HOLD => 'On Hold',
            self::APPROVED_STAGE_IN_PROGRESS => 'In Progress',
            self::APPROVED_STAGE_SUBMITTED => 'Submitted to Client',
            self::APPROVED_STAGE_AWAITED => 'Awaited from Client',
        ];
    }

    public function approvedStageLabel(): string
    {
        $stage = $this->approved_stage ?? self::APPROVED_STAGE_IN_PROGRESS;

        return self::approvedStageLabels()[$stage] ?? 'In Progress';
    }

    public function isApprovedInPipeline(): bool
    {
        if (!$this->approved_at || $this->isPipelinePlaced()) {
            return false;
        }

        $stage = $this->approved_stage ?? self::APPROVED_STAGE_IN_PROGRESS;

        return in_array($stage, self::PIPELINE_APPROVED_STAGES, true);
    }

    public function isInSubmittedSection(): bool
    {
        if (!$this->approved_at || $this->isPipelinePlaced()) {
            return false;
        }

        return in_array($this->approved_stage, self::SUBMITTED_APPROVED_STAGES, true);
    }

    public function trackerInfo()
    {
        return $this->belongsTo(TrackerInfo::class, 'tracker_info_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function pipelineStatus()
    {
        return $this->hasOne(CandidatePipelineStatus::class, 'tracker_candidate_id');
    }

    public function status()
    {
        return $this->belongsTo(JobStatus::class, 'current_status_id');
    }
}
