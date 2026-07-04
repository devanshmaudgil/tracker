@extends('layouts.app')

@section('title', 'View User')
@section('page_heading', 'User Management')

@section('content')
<div class="users-page users-form-page">
    @include('staff-users._toast')

    <div class="users-enter users-enter-1">
        <div class="users-toolbar" style="margin-bottom: 20px;">
            <div class="users-toolbar__title">
                <span>User Profile</span>
                <span>{{ $user->username ?? 'Staff member' }}</span>
            </div>
            <div class="users-toolbar__spacer"></div>
            <a href="{{ route('users.edit', $user->id) }}" class="toolbar-btn toolbar-btn--primary">
                <span class="toolbar-btn__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </span>
                <span class="toolbar-btn__label">Edit User</span>
            </a>
            <a href="{{ route('users.index') }}" class="toolbar-btn toolbar-btn--ghost">
                <span class="toolbar-btn__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                </span>
                <span class="toolbar-btn__label">Back</span>
            </a>
        </div>

        <div class="users-card">
            <div class="users-card__header">
                <h2>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Staff Details
                </h2>
            </div>

            <div class="profile-hero">
                @if($user->profile_photo_url)
                    <img src="{{ $user->profile_photo_url }}" alt="Profile" class="profile-photo-lg">
                @else
                    <div class="profile-photo-lg profile-photo-lg--placeholder">No Photo</div>
                @endif
                <div>
                    <h2 class="profile-name">{{ $user->username ?? 'Unnamed User' }}</h2>
                    <div class="profile-meta">
                        @if($user->loginAccount)
                            <span class="status-pill status-pill--active">
                                <span class="status-pill__dot"></span> Login Active
                            </span>
                        @else
                            <span class="status-pill status-pill--inactive">
                                <span class="status-pill__dot"></span> No Login
                            </span>
                        @endif
                        <span class="status-pill" style="background:#f3f4f6;color:#6b7280;">ID #{{ $user->id }}</span>
                    </div>
                    @if($user->remarks)
                        <p style="margin:0;font-size:13px;color:#6b7280;line-height:1.5;">{{ $user->remarks }}</p>
                    @endif
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Username</span>
                    <span class="detail-value">{{ $user->username ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">{{ $user->phone_number ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Date of Birth</span>
                    <span class="detail-value">{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('F d, Y') : '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Login Username</span>
                    <span class="detail-value">{{ $user->loginAccount?->username ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Account Created By</span>
                    <span class="detail-value muted">{{ $user->loginAccount?->created_by ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Last Updated By</span>
                    <span class="detail-value muted">{{ $user->loginAccount?->updated_by ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@include('staff-users._theme')
@endsection
