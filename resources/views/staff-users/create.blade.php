@extends('layouts.app')

@section('title', 'Add User')
@section('page_heading', 'User Management')

@section('content')
<div class="users-page users-form-page">
    @include('staff-users._toast')

    <div class="users-enter users-enter-1">
        <div class="users-toolbar" style="margin-bottom: 20px;">
            <div class="users-toolbar__title">
                <span>New Staff User</span>
                <span>Profile &amp; login credentials</span>
            </div>
            <div class="users-toolbar__spacer"></div>
            <a href="{{ route('users.index') }}" class="toolbar-btn toolbar-btn--ghost">
                <span class="toolbar-btn__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                </span>
                <span class="toolbar-btn__label">Back to Users</span>
            </a>
        </div>

        <div class="users-card">
            <div class="users-card__header">
                <h2>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    Create User
                </h2>
            </div>

            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-section">
                    <h3 class="form-section__title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Login Credentials
                    </h3>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="username">Username <span class="req">*</span></label>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" required autocomplete="off" placeholder="e.g. JaneRecruiter">
                            @error('username')<span class="form-error">{{ $message }}</span>@enderror
                            <span class="form-hint">Used to sign in to the system. If this person already exists as a profile without login, saving will enable their access.</span>
                        </div>
                        <div class="form-field"></div>
                        <div class="form-field form-field--full">
                            <label for="password">Password <span class="req">*</span></label>
                            <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Create a strong password">
                            @error('password')<span class="form-error">{{ $message }}</span>@enderror
                            @include('staff-users._password_strength')
                            <span class="form-hint">Must be 8+ characters with an uppercase letter, number, and special character.</span>
                        </div>
                        <div class="form-field">
                            <label for="password_confirmation">Confirm Password <span class="req">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section__title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Profile Details
                    </h3>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="profile_photo">Profile Photo</label>
                            <input type="file" id="profile_photo" name="profile_photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                            <span class="form-hint">JPG or PNG only, max 2 MB.</span>
                            @error('profile_photo')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-field">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                            @error('date_of_birth')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-field">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" placeholder="+1 555 000 0000">
                            @error('phone_number')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-field">
                            <label for="email">Official Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@rinfinite.com" autocomplete="email">
                            @error('email')<span class="form-error">{{ $message }}</span>@enderror
                            <span class="form-hint">Must end with @rinfinite.com. Used as the sender address for candidate initialization emails.</span>
                        </div>
                        <div class="form-field form-field--full">
                            <label for="remarks">Remarks</label>
                            <textarea id="remarks" name="remarks" placeholder="Optional notes about this user">{{ old('remarks') }}</textarea>
                            @error('remarks')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-form btn-form--primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Create User &amp; Credentials
                    </button>
                    <a href="{{ route('users.index') }}" class="btn-form btn-form--ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@include('staff-users._theme')
@endsection
