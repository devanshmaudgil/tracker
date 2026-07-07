<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackerInfo extends Model
{
    protected $table = 'tracker_info';

    protected $fillable = [
        'month_id',
        'client_id',
        'region_id',
        'prd',
        'cf',
        'country',
        'position',
        'job_description',
        'notes',
        'type_of_job',
        'bill_rate_salary_range',
        'priority',
        'submission_deadline',
        'lr',
        'is_unserved',
        'csi',
        'job_status_FK',
        'remarks',
    ];

    protected $attributes = [
        'is_unserved' => false,
    ];

    protected function casts(): array
    {
        return [
            'prd' => 'date',
            'submission_deadline' => 'date',
            'is_unserved' => 'boolean',
        ];
    }

    public function month()
    {
        return $this->belongsTo(Month::class, 'month_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function regions()
    {
        return $this->belongsToMany(Region::class, 'tracker_info_region', 'tracker_info_id', 'region_id')
                    ->withPivot('openings_count')
                    ->withTimestamps();
    }

    public function leadRecruiter()
    {
        return $this->belongsTo(StaffUser::class, 'lr');
    }

    public function trackerCandidates()
    {
        return $this->hasMany(TrackerCandidate::class, 'tracker_info_id');
    }

    public function candidates()
    {
        return $this->belongsToMany(Candidate::class, 'tracker_candidates', 'tracker_info_id', 'candidate_id')
                    ->withPivot('id')
                    ->withTimestamps();
    }
    public function jobStatus()
    {
        return $this->belongsTo(JobStatus::class, 'job_status_FK');
    }

    public function isUnserved(): bool
    {
        return (int) $this->job_status_FK === JobStatus::unservedId();
    }

    public function updateStatusFromCandidates()
    {
        if ($this->isUnserved()) {
            return;
        }
        $candidates = $this->trackerCandidates()
            ->with('pipelineStatus')
            ->whereNull('rejected_at')
            ->get();

        $newStatusId = $this->deriveJobStatusId($candidates);

        if ($this->job_status_FK != $newStatusId) {
            $this->update(['job_status_FK' => $newStatusId]);
        }
    }

    /**
     * Derive the single job status from candidate pipelines.
     *
     * Priority: any placement wins immediately; otherwise take the furthest
     * stage reached among non-placed candidates. Falls back to Demand Raised
     * when there are no active candidates.
     *
     * @param  \Illuminate\Support\Collection<int, TrackerCandidate>  $candidates
     */
    public function deriveJobStatusId($candidates): int
    {
        $placementCompletedId = JobStatus::placementCompletedId();

        if ($candidates->isEmpty()) {
            return 1; // Demand Raised
        }

        // Any confirmed placement moves the whole job to Accepted immediately.
        if ($candidates->contains(fn ($tc) => $tc->isPipelinePlaced())) {
            return $placementCompletedId;
        }

        $activeStatuses = $candidates
            ->map(fn ($tc) => (int) $tc->current_status_id)
            ->filter(fn ($statusId) => $statusId > 0 && $statusId !== $placementCompletedId);

        if ($activeStatuses->isEmpty()) {
            return 1; // Demand Raised
        }

        // All remaining candidates marked as placement rejected.
        if ($activeStatuses->every(fn ($statusId) => $statusId === 18)) {
            return 18;
        }

        // Furthest stage reached, ignoring placement-rejected candidates.
        return (int) $activeStatuses
            ->reject(fn ($statusId) => $statusId === 18)
            ->max();
    }
}
