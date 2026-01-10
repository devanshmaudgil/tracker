@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="content-header">
    <h1>Edit User</h1>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to Users</a>
</div>

<div class="table-container">
    <form method="POST" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" placeholder="Enter username">
            @error('username')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        @if($user->profile_photo_url)
            <div class="form-group">
                <label>Current Profile Photo</label>
                <img src="{{ $user->profile_photo_url }}" alt="Profile" style="width: 100px; height: 100px; border-radius: 8px; object-fit: cover; display: block; margin-top: 10px;">
            </div>
        @endif

        <div class="form-group">
            <label for="profile_photo">Profile Photo</label>
            <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
            <small style="color: #666; font-size: 12px;">Leave empty to keep current photo</small>
            @error('profile_photo')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="date_of_birth">Date of Birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth) }}">
            @error('date_of_birth')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" placeholder="+1 234 567 8900">
            @error('phone_number')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea id="remarks" name="remarks" rows="4" placeholder="Additional notes...">{{ old('remarks', $user->remarks) }}</textarea>
            @error('remarks')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

