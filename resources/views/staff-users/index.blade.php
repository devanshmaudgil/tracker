@extends('layouts.app')

@section('title', 'User Management')
@section('page_heading', 'User Management')

@section('content')
<div class="users-page">
    @include('staff-users._toast')

    <div class="users-enter users-enter-1">
        <div class="users-toolbar">
            <div class="users-toolbar__title">
                <span>Staff Directory</span>
                <span>Manage team members &amp; access</span>
            </div>
            <div class="users-toolbar__divider"></div>
            <div class="users-stat-pill">
                <span class="users-stat-pill__label">Total Users</span>
                <span class="users-stat-pill__value">{{ $users->count() }}</span>
            </div>
            <div class="users-stat-pill">
                <span class="users-stat-pill__label">Login Enabled</span>
                <span class="users-stat-pill__value">{{ $withLoginCount }}</span>
            </div>
            <div class="users-toolbar__spacer"></div>
            <a href="{{ route('users.create') }}" class="toolbar-btn toolbar-btn--primary">
                <span class="toolbar-btn__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </span>
                <span class="toolbar-btn__label">Add User</span>
            </a>
        </div>
    </div>

    <div class="users-enter users-enter-2">
        <div class="users-card">
            <div class="users-card__header">
                <h2>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    All Users
                </h2>
            </div>

            @if($users->isEmpty())
                <div class="users-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <p>No staff users yet. Create the first user with login credentials.</p>
                    <a href="{{ route('users.create') }}" class="toolbar-btn toolbar-btn--primary">
                        <span class="toolbar-btn__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </span>
                        <span class="toolbar-btn__label">Add User</span>
                    </a>
                </div>
            @else
                <div class="users-table-wrap">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Username</th>
                                <th>Phone</th>
                                <th>Date of Birth</th>
                                <th>Login Access</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        @if($user->profile_photo_url)
                                            <img src="{{ $user->profile_photo_url }}" alt="" class="user-avatar">
                                        @else
                                            <span class="user-avatar user-avatar--placeholder">{{ strtoupper(substr($user->username ?? '?', 0, 1)) }}</span>
                                        @endif
                                    </td>
                                    <td class="username-cell">{{ $user->username ?? '—' }}</td>
                                    <td>{{ $user->phone_number ?? '—' }}</td>
                                    <td>{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') : '—' }}</td>
                                    <td>
                                        @if($user->loginAccount)
                                            <span class="status-pill status-pill--active">
                                                <span class="status-pill__dot"></span> Active
                                            </span>
                                        @else
                                            <span class="status-pill status-pill--inactive">
                                                <span class="status-pill__dot"></span> No login
                                            </span>
                                            <a href="{{ route('users.edit', $user->id) }}" class="row-btn" title="Enable login" style="width:auto;padding:0 10px;font-size:11px;font-weight:700;">
                                                Enable
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a href="{{ route('users.show', $user->id) }}" class="row-btn" title="View">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            <a href="{{ route('users.edit', $user->id) }}" class="row-btn" title="Edit">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this user and their login credentials?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="row-btn row-btn--danger" title="Delete">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@include('staff-users._theme')
@endsection
