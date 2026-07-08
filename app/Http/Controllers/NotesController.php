<?php

namespace App\Http\Controllers;

use App\Models\StaffUser;
use App\Models\UserNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class NotesController extends Controller
{
    public function index(Request $request)
    {
        $staff = $this->resolveStaffUser();

        if (! $staff) {
            return view('notes.index', [
                'staff' => null,
                'items' => collect(),
                'today' => Carbon::now()->toDateString(),
                'activeKind' => 'all',
            ]);
        }

        $today = Carbon::now()->toDateString();
        $activeKind = $request->input('kind', 'all');
        $search = trim((string) $request->input('q', ''));

        $searchQuery = UserNote::query()
            ->where('staff_user_id', $staff->id);

        if ($search !== '') {
            $searchQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('body', 'like', '%' . $search . '%');
            });
        }

        $itemsQuery = clone $searchQuery;
        if ($activeKind !== 'all') {
            $itemsQuery->where('kind', $activeKind);
        }

        // Daily reminders + open tasks should be computed independently from the selected tab.
        $todayReminders = (clone $searchQuery)
            ->where('kind', UserNote::KIND_REMINDER)
            ->whereDate('reminder_date', $today)
            ->where('is_completed', false)
            ->orderByDesc('created_at')
            ->get();

        $openTasks = (clone $searchQuery)
            ->where('kind', UserNote::KIND_TASK)
            ->where('is_completed', false)
            ->orderByDesc('created_at')
            ->get();

        $items = $itemsQuery
            ->orderByDesc('is_completed')
            ->orderByDesc('due_date')
            ->orderByDesc('reminder_date')
            ->orderByDesc('created_at')
            ->limit(150)
            ->get();

        // Avoid duplicates when showing reminders/tasks on top.
        $tRIds = $todayReminders->pluck('id')->all();
        $oTIds = $openTasks->pluck('id')->all();
        $pinnedIds = array_unique(array_merge($tRIds, $oTIds));

        $items = $items
            ->sortByDesc(function (UserNote $n) use ($pinnedIds) {
                return in_array($n->id, $pinnedIds, true) ? 1 : 0;
            });

        return view('notes.index', [
            'staff' => $staff,
            'items' => $items,
            'today' => $today,
            'activeKind' => $activeKind,
            'todayReminders' => $todayReminders,
            'openTasks' => $openTasks,
            'q' => $search,
        ]);
    }

    public function store(Request $request)
    {
        $staff = $this->resolveStaffUser();
        if (! $staff) {
            return back()->with('error', 'Your account is not linked to a staff profile.');
        }

        $validator = Validator::make($request->all(), [
            'kind' => ['required', 'in:' . implode(',', [UserNote::KIND_NOTE, UserNote::KIND_TASK, UserNote::KIND_REMINDER])],
            'title' => ['required', 'string', 'max:140'],
            'body' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'reminder_date' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withInput()
                ->withErrors($validator);
        }

        $kind = $request->input('kind');
        $dueDate = $request->input('due_date');
        $reminderDate = $request->input('reminder_date');

        if ($kind === UserNote::KIND_TASK) {
            $reminderDate = null;
        } elseif ($kind === UserNote::KIND_REMINDER) {
            $dueDate = null;
        } else {
            $dueDate = null;
            $reminderDate = null;
        }

        $note = UserNote::create([
            'staff_user_id' => $staff->id,
            'kind' => $kind,
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'due_date' => $dueDate,
            'reminder_date' => $reminderDate,
            'is_completed' => false,
        ]);

        return back()->with('success', "Saved {$note->kindLabel()}.");
    }

    public function edit(int $id)
    {
        $staff = $this->resolveStaffUser();
        if (! $staff) {
            abort(404);
        }

        $note = $this->findForStaff($id, $staff);
        return view('notes.edit', [
            'staff' => $staff,
            'note' => $note,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $staff = $this->resolveStaffUser();
        if (! $staff) {
            return back()->with('error', 'Your account is not linked to a staff profile.');
        }

        $note = $this->findForStaff($id, $staff);

        $validator = Validator::make($request->all(), [
            'kind' => ['required', 'in:' . implode(',', [UserNote::KIND_NOTE, UserNote::KIND_TASK, UserNote::KIND_REMINDER])],
            'title' => ['required', 'string', 'max:140'],
            'body' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'reminder_date' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withInput()
                ->withErrors($validator);
        }

        $kind = $request->input('kind');
        $dueDate = $request->input('due_date');
        $reminderDate = $request->input('reminder_date');

        if ($kind === UserNote::KIND_TASK) {
            $reminderDate = null;
        } elseif ($kind === UserNote::KIND_REMINDER) {
            $dueDate = null;
        } else {
            $dueDate = null;
            $reminderDate = null;
        }

        $note->update([
            'kind' => $kind,
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'due_date' => $dueDate,
            'reminder_date' => $reminderDate,
        ]);

        return redirect()->route('notes.index')->with('success', 'Updated successfully.');
    }

    public function toggle(Request $request, int $id)
    {
        $staff = $this->resolveStaffUser();
        if (! $staff) {
            abort(404);
        }

        $note = $this->findForStaff($id, $staff);

        if (! in_array($note->kind, [UserNote::KIND_TASK, UserNote::KIND_REMINDER], true)) {
            abort(404);
        }

        $note->is_completed = ! $note->is_completed;
        $note->completed_at = $note->is_completed ? Carbon::now() : null;
        $note->save();

        return back()->with('success', $note->is_completed ? 'Marked done.' : 'Re-opened.');
    }

    public function destroy(Request $request, int $id)
    {
        $staff = $this->resolveStaffUser();
        if (! $staff) {
            abort(404);
        }

        $note = $this->findForStaff($id, $staff);
        $note->delete();

        return back()->with('success', 'Deleted.');
    }

    private function resolveStaffUser(): ?StaffUser
    {
        $login = auth()->user();
        if (! $login?->staff_user_id) {
            return null;
        }

        return StaffUser::find($login->staff_user_id);
    }

    private function findForStaff(int $noteId, StaffUser $staff): UserNote
    {
        $note = UserNote::query()
            ->where('staff_user_id', $staff->id)
            ->where('id', $noteId)
            ->first();

        if (! $note) {
            throw new RuntimeException('Note not found.');
        }

        return $note;
    }
}

