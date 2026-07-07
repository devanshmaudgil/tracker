@extends('layouts.app')

@section('title', 'Add Month')

@section('content')
@include('partials._crud_theme')

<div class="crud-page">
    <header class="crud-hero crud-enter crud-enter-1">
        <div>
            <div class="crud-hero__eyebrow">RADiiX INFINITEii</div>
            <h1 class="crud-hero__title">Add Month</h1>
        </div>
        <a href="{{ route('months.index') }}" class="crud-btn crud-btn--ghost">Back to Months</a>
    </header>

    <div class="crud-form-card crud-enter crud-enter-2">
        <div class="crud-form-card__head">
            <h2>New Tracking Month</h2>
            <p>Create a new period for organising job demands.</p>
        </div>
        <div class="crud-form-card__body">
            <form method="POST" action="{{ route('months.store') }}">
                @csrf
                <div class="crud-field">
                    <label for="month">Month *</label>
                    <input type="text" id="month" name="month" value="{{ old('month') }}" required placeholder="e.g. August 2026">
                    <div class="crud-field__hint">Use the format "Month Year", e.g. August 2026.</div>
                    @error('month')<div class="crud-field__error">{{ $message }}</div>@enderror
                </div>
                <div class="crud-modal__foot">
                    <a href="{{ route('months.index') }}" class="crud-btn crud-btn--ghost">Cancel</a>
                    <button type="submit" class="crud-btn crud-btn--primary">Save Month</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
