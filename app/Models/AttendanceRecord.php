<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    public const STATUS_PRESENT = 'present';
    public const STATUS_LATE = 'late';
    public const STATUS_HALF_DAY = 'half_day';

    protected $fillable = [
        'staff_user_id',
        'attendance_date',
        'check_in_at',
        'check_out_at',
        'status',
        'worked_minutes',
        'check_in_ip',
        'check_out_ip',
        'day_remarks',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'worked_minutes' => 'integer',
        ];
    }

    public function staffUser(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'staff_user_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_LATE => 'Late',
            self::STATUS_HALF_DAY => 'Half Day',
            default => 'Present',
        };
    }

    public function formattedWorkedHours(): ?string
    {
        if ($this->worked_minutes === null) {
            return null;
        }

        $hours = intdiv($this->worked_minutes, 60);
        $mins = $this->worked_minutes % 60;

        return sprintf('%dh %02dm', $hours, $mins);
    }
}
