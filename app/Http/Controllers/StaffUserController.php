<?php

namespace App\Http\Controllers;

use App\Models\StaffUser;
use App\Models\UserLogin;
use App\Rules\StrongPassword;
use App\Services\Storage\ToolStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffUserController extends Controller
{
    public function index()
    {
        $users = StaffUser::with('loginAccount')->orderBy('id', 'desc')->get();
        $withLoginCount = $users->filter(fn ($u) => $u->loginAccount)->count();

        return view('staff-users.index', compact('users', 'withLoginCount'));
    }

    public function create()
    {
        return view('staff-users.create');
    }

    public function store(Request $request)
    {
        $existingStaff = StaffUser::with('loginAccount')
            ->where('username', $request->input('username'))
            ->first();

        if ($existingStaff?->loginAccount) {
            return back()
                ->withInput()
                ->withErrors(['username' => 'This username already has login credentials. Edit the user to change the password.']);
        }

        $rules = [
            'username' => 'required|string|max:255|unique:user_login,username',
            'password' => ['required', 'string', 'confirmed', new StrongPassword()],
            'profile_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'date_of_birth' => 'nullable|date',
            'phone_number' => 'nullable|string|max:50',
            'email' => ['nullable', 'email', 'max:255', 'regex:/@rinfinite\.com$/i'],
            'remarks' => 'nullable|string|max:2000',
        ];

        if (!$existingStaff) {
            $rules['username'] .= '|unique:staff_users,username';
        }

        $request->validate($rules);

        $data = $this->staffDataFromRequest($request, $existingStaff);

        DB::transaction(function () use ($data, $request, $existingStaff) {
            if ($existingStaff) {
                $existingStaff->update($data);
                $staff = $existingStaff->fresh();
            } else {
                $staff = StaffUser::create($data);
            }

            UserLogin::create([
                'staff_user_id' => $staff->id,
                'username' => $staff->username,
                'password' => $request->input('password'),
                'password_policy_compliant' => true,
                'remarks' => $staff->remarks,
                'created_by' => $this->actorName(),
                'updated_by' => $this->actorName(),
            ]);
        });

        $message = $existingStaff
            ? 'Login credentials enabled for existing user.'
            : 'User created with login credentials successfully.';

        return redirect()->route('users.index')->with('success', $message);
    }

    public function show(string $id)
    {
        $user = StaffUser::with('loginAccount')->findOrFail($id);

        return view('staff-users.show', compact('user'));
    }

    public function edit(string $id)
    {
        $user = StaffUser::with('loginAccount')->findOrFail($id);

        return view('staff-users.edit', compact('user'));
    }

    public function update(Request $request, string $id)
    {
        $user = StaffUser::with('loginAccount')->findOrFail($id);
        $loginId = $user->loginAccount?->id;
        $needsLogin = !$user->loginAccount;

        $passwordRules = [$needsLogin ? 'required' : 'nullable'];
        if ($needsLogin || $request->filled('password')) {
            $passwordRules[] = 'string';
            $passwordRules[] = 'confirmed';
            $passwordRules[] = new StrongPassword();
        }

        $request->validate([
            'username' => 'required|string|max:255|unique:staff_users,username,' . $id . '|unique:user_login,username,' . ($loginId ?? 'NULL'),
            'password' => $passwordRules,
            'profile_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'date_of_birth' => 'nullable|date',
            'phone_number' => 'nullable|string|max:50',
            'email' => ['nullable', 'email', 'max:255', 'regex:/@rinfinite\.com$/i'],
            'remarks' => 'nullable|string|max:2000',
        ], [
            'password.required' => 'A password is required to enable login for this user.',
        ]);

        $data = $this->staffDataFromRequest($request, $user);

        DB::transaction(function () use ($user, $data, $request) {
            $user->update($data);

            $loginPayload = [
                'username' => $user->username,
                'remarks' => $user->remarks,
                'updated_by' => $this->actorName(),
            ];

            if ($request->filled('password')) {
                $loginPayload['password'] = $request->input('password');
                $loginPayload['password_policy_compliant'] = true;
            }

            if ($user->loginAccount) {
                $user->loginAccount->update($loginPayload);
            } elseif ($request->filled('password')) {
                UserLogin::create(array_merge($loginPayload, [
                    'staff_user_id' => $user->id,
                    'password' => $request->input('password'),
                    'password_policy_compliant' => true,
                    'created_by' => $this->actorName(),
                ]));
            }
        });

        return redirect()->route('users.index')
            ->with('success', $needsLogin && $request->filled('password')
                ? 'Login credentials enabled successfully.'
                : 'User updated successfully.');
    }

    public function destroy(string $id)
    {
        $user = StaffUser::with('loginAccount')->findOrFail($id);

        ToolStorage::deleteIfExists($user->profile_photo);

        DB::transaction(function () use ($user) {
            $user->loginAccount?->delete();
            $user->delete();
        });

        return redirect()->route('users.index')
            ->with('success', 'User and login credentials removed successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function staffDataFromRequest(Request $request, ?StaffUser $existing = null): array
    {
        $data = $request->only(['username', 'email', 'date_of_birth', 'phone_number', 'remarks']);

        if ($request->hasFile('profile_photo')) {
            $username = $request->input('username', $existing?->username ?? 'user');
            $data['profile_photo'] = ToolStorage::storeUserProfilePhoto(
                $request->file('profile_photo'),
                $username,
                $existing?->profile_photo
            );
        }

        return $data;
    }

    private function actorName(): string
    {
        return Auth::user()?->username ?? 'system';
    }
}
