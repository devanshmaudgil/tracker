<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'region',
        'city',
    ];

    public function trackerInfos()
    {
        return $this->belongsToMany(TrackerInfo::class, 'tracker_info_region', 'region_id', 'tracker_info_id')
                    ->withPivot('openings_count')
                    ->withTimestamps();
    }
}
