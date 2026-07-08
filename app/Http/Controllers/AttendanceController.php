<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\StaffUser;
use App\Services\Attendance\AttendanceService;
use Illuminate\Http\Request;
use RuntimeException;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendance,
    ) {}

    public function index(Request $request)
    {
        $staff = $this->resolveStaffUser();

        if (! $staff) {
            return view('attendance.index', [
                'staff' => null,
                'today' => null,
                'stats' => null,
                'records' => collect(),
                'policy' => $this->policyMeta(),
            ]);
        }

        $today = $this->attendance->getTodayRecord($staff);
        $month = (int) $request->input('month', $this->attendance->now()->month);
        $year = (int) $request->input('year', $this->attendance->now()->year);

        return view('attendance.index', [
            'staff' => $staff,
            'today' => $this->attendance->serializeToday($today),
            'stats' => $this->attendance->monthlyStats($staff, $year, $month),
            'records' => $this->attendance->recentRecords($staff, 21),
            'policy' => $this->policyMeta(),
            'selectedMonth' => $month,
            'selectedYear' => $year,
        ]);
    }

    public function checkIn(Request $request)
    {
        return $this->punch($request, 'checkIn', 'Checked in successfully.');
    }

    public function checkOut(Request $request)
    {
        return $this->punch($request, 'checkOut', 'Checked out successfully.');
    }

    public function updateRemarks(Request $request, int $id)
    {
        $staff = $this->resolveStaffUser();
        if (! $staff) {
            abort(404);
        }

        $data = $request->validate([
            'day_remarks' => ['nullable', 'string'],
        ]);

        $record = AttendanceRecord::query()
            ->where('staff_user_id', $staff->id)
            ->where('id', $id)
            ->firstOrFail();

        $record->update([
            'day_remarks' => $data['day_remarks'] ?? null,
        ]);

        return back()->with('success', 'Remark saved for this day.');
    }

    private function punch(Request $request, string $action, string $successMessage)
    {
        $staff = $this->resolveStaffUser();

        if (! $staff) {
            return $this->respond($request, false, 'Your account is not linked to a staff profile. Contact an administrator.');
        }

        try {
            $record = $this->attendance->{$action}($staff, $request);
            $payload = [
                'success' => true,
                'message' => $successMessage,
                'today' => $this->attendance->serializeToday($record),
                'stats' => $this->attendance->monthlyStats($staff),
            ];

            return $this->respond($request, true, $successMessage, $payload);
        } catch (RuntimeException $e) {
            return $this->respond($request, false, $e->getMessage());
        }
    }

    private function respond(Request $request, bool $ok, string $message, array $extra = [])
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(array_merge([
                'success' => $ok,
                'message' => $message,
            ], $extra), $ok ? 200 : 422);
        }

        return redirect()
            ->route('attendance.index')
            ->with($ok ? 'success' : 'error', $message);
    }

    private function resolveStaffUser(): ?StaffUser
    {
        $login = auth()->user();
        if (! $login?->staff_user_id) {
            return null;
        }

        return StaffUser::find($login->staff_user_id);
    }

    /**
     * @return array<string, mixed>
     */
    private function policyMeta(): array
    {
        $officeStart = $this->attendance->officeStartToday();
        $lateAfter = $this->attendance->lateThreshold();

        return [
            'timezone' => $this->attendance->timezone(),
            'timezone_label' => config('attendance.timezone_label', 'EST'),
            'office_start' => $officeStart->format('h:i A'),
            'late_after' => $lateAfter->format('h:i A'),
            'standard_hours' => config('attendance.standard_hours', 8),
        ];
    }
}
