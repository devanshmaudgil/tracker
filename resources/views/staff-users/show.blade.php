@extends('layouts.app')

@section('title', 'View User')

@section('content')
<div class="content-header">
    <h1>View User</h1>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to Users</a>
</div>

<div class="table-container">
    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 30px; align-items: start;">
        <div>
            @if($user->profile_photo_url)
                <img src="{{ $user->profile_photo_url }}" alt="Profile" style="width: 200px; height: 200px; border-radius: 8px; object-fit: cover; border: 2px solid #f1cd86;">
            @else
                <div style="width: 200px; height: 200px; border-radius: 8px; background-color: #ddd; display: flex; align-items: center; justify-content: center; color: #999; border: 2px solid #f1cd86;">No Photo</div>
            @endif
        </div>
        <div>
            <div class="form-group">
                <label>ID</label>
                <div style="padding: 10px; background-color: #f9f9f9; border-radius: 4px;">{{ $user->id }}</div>
            </div>
            <div class="form-group">
                <label>Username</label>
                <div style="padding: 10px; background-color: #f9f9f9; border-radius: 4px;">{{ $user->username ?? 'N/A' }}</div>
            </div>
            <div class="form-group">
                <label>Date of Birth</label>
                <div style="padding: 10px; background-color: #f9f9f9; border-radius: 4px;">{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('F d, Y') : 'N/A' }}</div>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <div style="padding: 10px; background-color: #f9f9f9; border-radius: 4px;">{{ $user->phone_number ?? 'N/A' }}</div>
            </div>
            <div class="form-group">
                <label>Remarks</label>
                <div style="padding: 10px; background-color: #f9f9f9; border-radius: 4px; min-height: 100px;">{{ $user->remarks ?? 'N/A' }}</div>
            </div>
            <div class="action-buttons">
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">Edit User</a>
            </div>
        </div>
    </div>
</div>
@endsection

