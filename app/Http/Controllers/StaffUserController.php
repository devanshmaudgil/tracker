<?php

namespace App\Http\Controllers;

use App\Models\StaffUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = StaffUser::orderBy('id', 'desc')->get();
        return view('staff-users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('staff-users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|max:2048',
            'date_of_birth' => 'nullable|date',
            'phone_number' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'supabase');
            // Store ONLY the path, never URLs or signed URLs
            $data['profile_photo'] = $path;
        }
        
        // Ensure profile_photo field contains only a path, not a URL
        if (isset($data['profile_photo']) && str_contains($data['profile_photo'], 'http')) {
            unset($data['profile_photo']); // Remove invalid URL
        }

        StaffUser::create($data);

        return redirect()->route('users.index')->with('success', 'User added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = StaffUser::findOrFail($id);
        return view('staff-users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = StaffUser::findOrFail($id);
        return view('staff-users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'username' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|max:2048',
            'date_of_birth' => 'nullable|date',
            'phone_number' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $user = StaffUser::findOrFail($id);
        $data = $request->all();

        if ($request->hasFile('profile_photo')) {
            // Delete old file using path (not URL)
            if ($user->profile_photo) {
                $oldPath = $user->profile_photo;
                // Strip URL if somehow stored
                if (str_contains($oldPath, 'http')) {
                    $oldPath = preg_replace('/^.*\/object\/(?:public|sign)\/[^\/]+\//', '', $oldPath);
                }
                Storage::disk('supabase')->delete($oldPath);
            }
            $path = $request->file('profile_photo')->store('profile_photos', 'supabase');
            // Store ONLY the path, never URLs or signed URLs
            $data['profile_photo'] = $path;
        }
        
        // Ensure profile_photo field contains only a path, not a URL
        if (isset($data['profile_photo']) && str_contains($data['profile_photo'], 'http')) {
            unset($data['profile_photo']); // Remove invalid URL
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = StaffUser::findOrFail($id);
        
        if ($user->profile_photo) {
            // Delete using path (not URL)
            $path = $user->profile_photo;
            // Strip URL if somehow stored
            if (str_contains($path, 'http')) {
                $path = preg_replace('/^.*\/object\/(?:public|sign)\/[^\/]+\//', '', $path);
            }
            Storage::disk('supabase')->delete($path);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
