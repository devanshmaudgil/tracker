@extends('layouts.app')

@section('title', 'Edit Month')

@section('content')
<div class="content-header">
    <h1>Edit Month</h1>
    <a href="{{ route('months.index') }}" class="btn btn-secondary">Back to Months</a>
</div>

<div class="table-container">
    <form method="POST" action="{{ route('months.update', $month->id) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="month">Month (e.g., January 2025)</label>
            <input type="text" id="month" name="month" value="{{ old('month', $month->month) }}" required placeholder="January 2025">
            @error('month')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update Month</button>
            <a href="{{ route('months.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

