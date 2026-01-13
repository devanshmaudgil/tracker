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
        'type_of_job',
        'bill_rate_salary_range',
        'priority',
        'submission_deadline',
        'lr',
        'csi',
        'job_status_FK',
    ];

    protected function casts(): array
    {
        return [
            'prd' => 'date',
            'submission_deadline' => 'date',
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

    public function updateStatusFromCandidates()
    {
        $candidates = $this->trackerCandidates;
        $totalCandidates = $candidates->count();
        
        if ($totalCandidates == 0) {
            if ($this->job_status_FK != 1) {
                $this->update(['job_status_FK' => 1]); // Demand Raised
            }
            return;
        }

        $statusCounts = $candidates->groupBy('current_status_id')
            ->map(function ($group) {
                return $group->count();
            });

        $majorityThreshold = $totalCandidates / 2;
        $newStatusId = null;

        foreach ($statusCounts as $statusId => $count) {
            if ($count > $majorityThreshold) {
                $newStatusId = $statusId;
                break;
            }
        }

        // If a status has majority, update the job status
        if ($newStatusId && $this->job_status_FK != $newStatusId) {
            $this->update(['job_status_FK' => $newStatusId]);
        }
    }
}
