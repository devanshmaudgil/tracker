@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="content-header">
    <h1>Users</h1>
    <a href="{{ route('users.create') }}" class="btn btn-primary">Add User</a>
</div>

<style>
    .table-container table {
        font-size: 12px;
    }
    .table-container th,
    .table-container td {
        text-align: center;
        padding: 6px 8px;
    }
    .table-container th {
        font-size: 11px;
        white-space: nowrap;
    }
</style>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Profile Photo</th>
                <th>Date of Birth</th>
                <th>Phone Number</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->username ?? 'N/A' }}</td>
                    <td>
                        @if($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        @else
                            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #ddd; display: inline-flex; align-items: center; justify-content: center; color: #999; font-size: 10px;">No Photo</div>
                        @endif
                    </td>
                    <td>{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $user->phone_number ?? 'N/A' }}</td>
                    <td>{{ Str::limit($user->remarks, 50) ?? 'N/A' }}</td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('users.show', $user->id) }}" class="btn btn-secondary btn-sm">View</a>
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                            <form method="POST" action="{{ route('users.destroy', $user->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #666;">No users found. <a href="{{ route('users.create') }}" style="color: #f1cd86;">Add your first user</a></td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

