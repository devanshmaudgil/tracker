<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNote extends Model
{
    public const KIND_NOTE = 'note';
    public const KIND_TASK = 'task';
    public const KIND_REMINDER = 'reminder';

    protected $table = 'user_notes';

    protected $fillable = [
        'staff_user_id',
        'kind',
        'title',
        'body',
        'due_date',
        'reminder_date',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'due_date' => 'date',
        'reminder_date' => 'date',
    ];

    public function staffUser(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'staff_user_id');
    }

    public function kindLabel(): string
    {
        return match ($this->kind) {
            self::KIND_TASK => 'Task',
            self::KIND_REMINDER => 'Reminder',
            default => 'Note',
        };
    }
}

