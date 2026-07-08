<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\UserLogin;
use App\Services\Attendance\AttendanceService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $login = UserLogin::query()->where('username', 'DevanshMaudgil')->first();

        if (! $login?->staff_user_id) {
            $this->command?->warn('DevanshMaudgil login not found — skipping attendance seed.');

            return;
        }

        $staffUserId = $login->staff_user_id;
        $service = app(AttendanceService::class);
        $tz = $service->timezone();

        AttendanceRecord::query()
            ->where('staff_user_id', $staffUserId)
            ->delete();

        $samples = [
            ['in' => '08:55', 'out' => '17:05', 'status' => AttendanceRecord::STATUS_PRESENT],
            ['in' => '09:02', 'out' => '17:30', 'status' => AttendanceRecord::STATUS_PRESENT],
            ['in' => '09:22', 'out' => '17:15', 'status' => AttendanceRecord::STATUS_LATE],
            ['in' => '08:48', 'out' => '17:00', 'status' => AttendanceRecord::STATUS_PRESENT],
            ['in' => '09:10', 'out' => '13:05', 'status' => AttendanceRecord::STATUS_HALF_DAY],
            ['in' => '08:59', 'out' => '17:10', 'status' => AttendanceRecord::STATUS_PRESENT],
            ['in' => '09:35', 'out' => '17:45', 'status' => AttendanceRecord::STATUS_LATE],
            ['in' => '09:00', 'out' => '17:02', 'status' => AttendanceRecord::STATUS_PRESENT],
            ['in' => '08:52', 'out' => '17:20', 'status' => AttendanceRecord::STATUS_PRESENT],
            ['in' => '09:18', 'out' => '17:08', 'status' => AttendanceRecord::STATUS_LATE],
        ];

        $cursor = Carbon::now($tz)->subDay()->startOfDay();
        $seeded = 0;

        foreach ($samples as $sample) {
            while ($cursor->isWeekend()) {
                $cursor->subDay();
            }

            [$inHour, $inMin] = explode(':', $sample['in']);
            [$outHour, $outMin] = explode(':', $sample['out']);

            $checkIn = $cursor->copy()->setTime((int) $inHour, (int) $inMin);
            $checkOut = $cursor->copy()->setTime((int) $outHour, (int) $outMin);
            $workedMinutes = (int) max(0, $checkIn->diffInMinutes($checkOut, true));

            AttendanceRecord::create([
                'staff_user_id' => $staffUserId,
                'attendance_date' => $cursor->toDateString(),
                'check_in_at' => $checkIn->copy()->utc(),
                'check_out_at' => $checkOut->copy()->utc(),
                'status' => $sample['status'],
                'worked_minutes' => $workedMinutes,
                'check_in_ip' => '127.0.0.1',
                'check_out_ip' => '127.0.0.1',
            ]);

            $seeded++;
            $cursor->subDay();
        }

        $this->command?->info("Seeded {$seeded} attendance records for DevanshMaudgil (EST).");
    }
}
