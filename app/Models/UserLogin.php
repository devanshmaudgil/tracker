<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserLogin extends Authenticatable
{
    use Notifiable;

    protected $table = 'user_login';

    protected $fillable = [
        'staff_user_id',
        'username',
        'password',
        'remember_token',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function staffUser()
    {
        return $this->belongsTo(StaffUser::class, 'staff_user_id');
    }
}
