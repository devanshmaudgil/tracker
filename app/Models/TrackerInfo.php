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
}
