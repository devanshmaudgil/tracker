<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobStatus extends Model
{
    protected $table = 'job_status';

    protected $fillable = [
        'status',
        'status_initial',
    ];
}
