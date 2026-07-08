<?php

namespace App\Services\Attendance;

use App\Models\AttendanceRecord;
use App\Models\StaffUser;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use RuntimeException;

class AttendanceService
{
    public function timezone(): string
    {
        return config('attendance.timezone', 'America/New_York');
    }

    public function now(): Carbon
    {
        return Carbon::now($this->timezone());
    }

    public function todayDate(): Carbon
    {
        return $this->now()->copy()->startOfDay();
    }

    public function officeStartToday(): Carbon
    {
        [$hour, $minute] = explode(':', config('attendance.office_start', '09:00'));

        return $this->todayDate()->copy()->setTime((int) $hour, (int) $minute);
    }

    public function lateThreshold(): Carbon
    {
        return $this->officeStartToday()->copy()->addMinutes((int) config('attendance.grace_minutes', 15));
    }

    public function getTodayRecord(StaffUser $staff): ?AttendanceRecord
    {
        return AttendanceRecord::query()
            ->where('staff_user_id', $staff->id)
            ->where('attendance_date', $this->todayDate()->toDateString())
            ->first();
    }

    public function checkIn(StaffUser $staff, ?Request $request = null): AttendanceRecord
    {
        $existing = $this->getTodayRecord($staff);
        if ($existing?->check_in_at) {
            throw new RuntimeException('You have already checked in today.');
        }

        $now = $this->now();
        $status = $now->greaterThan($this->lateThreshold())
            ? AttendanceRecord::STATUS_LATE
            : AttendanceRecord::STATUS_PRESENT;

        $payload = [
            'check_in_at' => $this->persistTimestamp($now),
            'status' => $status,
            'check_in_ip' => $request?->ip(),
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return AttendanceRecord::create([
            'staff_user_id' => $staff->id,
            'attendance_date' => $this->todayDate()->toDateString(),
            ...$payload,
        ]);
    }

    public function checkOut(StaffUser $staff, ?Request $request = null): AttendanceRecord
    {
        $record = $this->getTodayRecord($staff);

        if (! $record?->check_in_at) {
            throw new RuntimeException('Please check in before checking out.');
        }

        if ($record->check_out_at) {
            throw new RuntimeException('You have already checked out today.');
        }

        $now = $this->now();
        $checkIn = $this->asAttendanceTime($record->check_in_at);
        $workedMinutes = (int) max(0, $checkIn->diffInMinutes($now, true));
        $status = $record->status;

        $halfDayThreshold = (int) (config('attendance.standard_hours', 8) * 60 * 0.5);
        if ($workedMinutes < $halfDayThreshold) {
            $status = AttendanceRecord::STATUS_HALF_DAY;
        }

        $record->update([
            'check_out_at' => $this->persistTimestamp($now),
            'worked_minutes' => $workedMinutes,
            'status' => $status,
            'check_out_ip' => $request?->ip(),
        ]);

        return $record->fresh();
    }

    /**
     * @return array{present: int, late: int, half_day: int, avg_hours: float|null, total_hours: float}
     */
    public function monthlyStats(StaffUser $staff, ?int $year = null, ?int $month = null): array
    {
        $anchor = $this->now();
        $year = $year ?? (int) $anchor->year;
        $month = $month ?? (int) $anchor->month;

        $records = AttendanceRecord::query()
            ->where('staff_user_id', $staff->id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->whereNotNull('check_in_at')
            ->get();

        $completed = $records->whereNotNull('check_out_at');
        $totalMinutes = $completed->sum('worked_minutes');

        return [
            'present' => $records->where('status', AttendanceRecord::STATUS_PRESENT)->count(),
            'late' => $records->where('status', AttendanceRecord::STATUS_LATE)->count(),
            'half_day' => $records->where('status', AttendanceRecord::STATUS_HALF_DAY)->count(),
            'days_marked' => $records->count(),
            'avg_hours' => $completed->count() > 0 ? round($totalMinutes / $completed->count() / 60, 1) : null,
            'total_hours' => round($totalMinutes / 60, 1),
        ];
    }

    public function recentRecords(StaffUser $staff, int $limit = 14): Collection
    {
        return AttendanceRecord::query()
            ->where('staff_user_id', $staff->id)
            ->orderByDesc('attendance_date')
            ->limit($limit)
            ->get();
    }

    public function serializeToday(?AttendanceRecord $record): array
    {
        $now = $this->now();

        return [
            'date' => $this->todayDate()->format('Y-m-d'),
            'date_label' => $this->todayDate()->format('l, d M Y'),
            'current_time' => $now->format('h:i A'),
            'can_check_in' => ! $record?->check_in_at,
            'can_check_out' => (bool) $record?->check_in_at && ! $record?->check_out_at,
            'is_complete' => (bool) $record?->check_in_at && (bool) $record?->check_out_at,
            'check_in_at' => $record?->check_in_at
                ? $this->asAttendanceTime($record->check_in_at)->format('h:i A')
                : null,
            'check_out_at' => $record?->check_out_at
                ? $this->asAttendanceTime($record->check_out_at)->format('h:i A')
                : null,
            'status' => $record?->status,
            'status_label' => $record?->statusLabel(),
            'worked_minutes' => $record?->worked_minutes,
            'worked_label' => $record?->formattedWorkedHours(),
            'check_in_iso' => $record?->check_in_at
                ? $this->asAttendanceTime($record->check_in_at)->toIso8601String()
                : null,
        ];
    }

    public function asAttendanceTime(DateTimeInterface|string|null $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value)->timezone($this->timezone());
    }

    private function persistTimestamp(Carbon $moment): Carbon
    {
        return $moment->copy()->utc();
    }
}
