@extends('layouts.app')

@section('title', 'Add User')

@section('content')
<div class="content-header">
    <h1>Add User</h1>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to Users</a>
</div>

<div class="table-container">
    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="Enter username">
            @error('username')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="profile_photo">Profile Photo</label>
            <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
            @error('profile_photo')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="date_of_birth">Date of Birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
            @error('date_of_birth')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" placeholder="+1 234 567 8900">
            @error('phone_number')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea id="remarks" name="remarks" rows="4" placeholder="Additional notes...">{{ old('remarks') }}</textarea>
            @error('remarks')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Add User</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

