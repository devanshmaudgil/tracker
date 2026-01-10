<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Candidate extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'location_id',
        'work_status',
        'current_company',
        'pay_rate',
        'agency_name',
        'agency_poc',
        'agency_poc_phone',
        'resume_file',
    ];

    protected $hidden = ['resume_file'];

    protected function getCleanResumeFilePath()
    {
        if (!$this->resume_file) {
            return null;
        }
        
        $path = $this->resume_file;
        
        if (str_contains($path, 'http')) {
            if (preg_match('/\/object\/(?:public|sign)\/[^\/]+\/(.+)$/', $path, $matches)) {
                $path = $matches[1];
                $this->updateQuietly(['resume_file' => $path]);
            }
        }
        
        return $path;
    }

    public function getResumeFileUrlAttribute()
    {
        $path = $this->getCleanResumeFilePath();
        if (!$path) {
            return null;
        }
        $isPublic = config('filesystems.disks.supabase.public', true);
        if ($isPublic) {
            return Storage::disk('supabase')->url($path);
        } else {
            try {
                return Storage::disk('supabase')->temporaryUrl($path, now()->addMinutes(60));
            } catch (\Exception $e) {
                return Storage::disk('supabase')->url($path);
            }
        }
    }

    protected $appends = ['resume_file_url'];

    public function location()
    {
        return $this->belongsTo(Region::class, 'location_id');
    }

    public function trackerCandidates()
    {
        return $this->hasMany(TrackerCandidate::class, 'candidate_id');
    }

    public function trackerInfos()
    {
        return $this->belongsToMany(TrackerInfo::class, 'tracker_candidates', 'candidate_id', 'tracker_info_id')
                    ->withPivot('id')
                    ->withTimestamps();
    }
}
