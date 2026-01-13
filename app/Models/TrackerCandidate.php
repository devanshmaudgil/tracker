<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackerCandidate extends Model
{
    protected $fillable = [
        'tracker_info_id',
        'candidate_id',
        'current_status_id',
    ];

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
