@extends('layouts.app')

@section('title', 'Edit User')
@section('page_heading', 'User Management')

@section('content')
<div class="users-page users-form-page">
    @include('staff-users._toast')

    <div class="users-enter users-enter-1">
        <div class="users-toolbar" style="margin-bottom: 20px;">
            <div class="users-toolbar__title">
                <span>Edit User</span>
                <span>{{ $user->username }}</span>
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Update User
                </h2>
            </div>

            <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <h3 class="form-section__title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Login Credentials
                    </h3>
                    @if($user->loginAccount)
                        <p class="form-hint" style="margin: 0 0 14px;">Login account is active. Leave password blank to keep the current one.</p>
                    @else
                        <div style="margin:0 0 14px;padding:12px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:13px;color:#991b1b;">
                            <strong>No login account.</strong> This profile exists but cannot sign in yet. Set a password below (min. 8 characters) to enable access.
                        </div>
                    @endif
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="username">Username <span class="req">*</span></label>
                            <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required autocomplete="off">
                            @error('username')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-field"></div>
                        <div class="form-field">
                            <label for="password">{{ $user->loginAccount ? 'New Password' : 'Password' }} @if(!$user->loginAccount)<span class="req">*</span>@endif</label>
                            <input type="password" id="password" name="password" autocomplete="new-password" placeholder="{{ $user->loginAccount ? 'Leave blank to keep current' : 'Min. 8 characters' }}" @if(!$user->loginAccount) required @endif>
                            @error('password')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-field">
                            <label for="password_confirmation">Confirm Password @if(!$user->loginAccount)<span class="req">*</span>@endif</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" @if(!$user->loginAccount) required @endif>
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
                            @if($user->profile_photo_url)
                                <div style="margin-bottom: 8px;">
                                    <img src="{{ $user->profile_photo_url }}" alt="" style="width: 64px; height: 64px; border-radius: 10px; object-fit: cover; border: 2px solid #f1cd86;">
                                </div>
                            @endif
                            <input type="file" id="profile_photo" name="profile_photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                            <span class="form-hint">JPG or PNG only, max 2 MB.</span>
                            @error('profile_photo')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-field">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth) }}">
                            @error('date_of_birth')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-field">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}">
                            @error('phone_number')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-field">
                            <label for="email">Official Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="name@rinfinite.com" autocomplete="email">
                            @error('email')<span class="form-error">{{ $message }}</span>@enderror
                            <span class="form-hint">Must end with @rinfinite.com. Used as the sender address for candidate initialization emails.</span>
                        </div>
                        <div class="form-field form-field--full">
                            <label for="remarks">Remarks</label>
                            <textarea id="remarks" name="remarks">{{ old('remarks', $user->remarks) }}</textarea>
                            @error('remarks')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-form btn-form--primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Changes
                    </button>
                    <a href="{{ route('users.show', $user->id) }}" class="btn-form btn-form--ghost">View Profile</a>
                    <a href="{{ route('users.index') }}" class="btn-form btn-form--ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@include('staff-users._theme')
@endsection
