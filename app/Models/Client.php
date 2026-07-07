<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'client',
        'type',
    ];

    public function trackers(): HasMany
    {
        return $this->hasMany(TrackerInfo::class, 'client_id');
    }
}
