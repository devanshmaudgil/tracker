@extends('layouts.app')

@section('title', 'Edit Candidate')

@section('content')
@include('partials._crud_theme')

<div class="crud-page">
    <header class="crud-hero crud-enter crud-enter-1">
        <div>
            <div class="crud-hero__eyebrow">RADiiX INFINITEii</div>
            <h1 class="crud-hero__title">Edit Candidate</h1>
        </div>
        <a href="{{ route('candidates.index') }}" class="crud-btn crud-btn--ghost">Back to Candidates</a>
    </header>

    <div class="crud-form-card crud-enter crud-enter-2" style="max-width: 860px;">
        <div class="crud-form-card__head">
            <h2>{{ $candidate->full_name }}</h2>
            <p>Update this candidate's profile, agency, and resume details.</p>
        </div>
        <div class="crud-form-card__body">
            <form method="POST" action="{{ route('candidates.update', $candidate->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="crud-form-grid">
                    <div class="crud-field">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $candidate->full_name) }}" required>
                        @error('full_name')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $candidate->email) }}" required>
                        @error('email')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $candidate->phone) }}">
                        @error('phone')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field">
                        <label for="work_status">Work Status</label>
                        <select id="work_status" name="work_status">
                            <option value="">Select</option>
                            @foreach(['GC','PR','Citizen','H1B','OPT'] as $ws)
                                <option value="{{ $ws }}" @selected(old('work_status', $candidate->work_status) == $ws)>{{ $ws }}</option>
                            @endforeach
                        </select>
                        @error('work_status')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field">
                        <label for="location_id">Location</label>
                        <select id="location_id" name="location_id">
                            <option value="">Select location</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" @selected(old('location_id', $candidate->location_id) == $region->id)>
                                    {{ $region->city ? $region->city . ', ' : '' }}{{ $region->region }}
                                </option>
                            @endforeach
                        </select>
                        @error('location_id')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field">
                        <label for="location_text">Location (free text)</label>
                        <input type="text" id="location_text" name="location_text" value="{{ old('location_text', $candidate->location_text) }}" placeholder="e.g. Hyderabad, India">
                        @error('location_text')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field">
                        <label for="current_company">Current Company</label>
                        <input type="text" id="current_company" name="current_company" value="{{ old('current_company', $candidate->current_company) }}">
                        @error('current_company')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field">
                        <label for="pay_rate">Pay Rate</label>
                        <input type="text" id="pay_rate" name="pay_rate" value="{{ old('pay_rate', $candidate->pay_rate) }}" placeholder="e.g. $50/hr">
                        @error('pay_rate')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field">
                        <label for="placement_pay_rate">Placement Pay Rate</label>
                        <input type="text" id="placement_pay_rate" name="placement_pay_rate" value="{{ old('placement_pay_rate', $candidate->placement_pay_rate) }}" placeholder="e.g. 30 LPA or $65/hr">
                        @error('placement_pay_rate')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field">
                        <label for="agency_name">Agency Name</label>
                        <input type="text" id="agency_name" name="agency_name" value="{{ old('agency_name', $candidate->agency_name) }}">
                        @error('agency_name')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field">
                        <label for="agency_poc">Agency POC</label>
                        <input type="text" id="agency_poc" name="agency_poc" value="{{ old('agency_poc', $candidate->agency_poc) }}">
                        @error('agency_poc')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field">
                        <label for="agency_poc_phone">POC Phone</label>
                        <input type="text" id="agency_poc_phone" name="agency_poc_phone" value="{{ old('agency_poc_phone', $candidate->agency_poc_phone) }}">
                        @error('agency_poc_phone')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field span-2">
                        <label for="summary">Summary</label>
                        <textarea id="summary" name="summary" rows="3" placeholder="Skills, experience, notice period…">{{ old('summary', $candidate->summary) }}</textarea>
                        @error('summary')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field span-2">
                        <label for="remarks">Remarks</label>
                        <textarea id="remarks" name="remarks" rows="3" placeholder="Internal notes about this candidate…">{{ old('remarks', $candidate->remarks) }}</textarea>
                        @error('remarks')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                    <div class="crud-field span-2">
                        <label for="resume_file">Resume (PDF / JPG / PNG)</label>
                        @if($candidate->resume_file_url)
                            <div style="margin-bottom: 8px;">
                                <a href="{{ $candidate->resume_file_url }}" target="_blank" class="crud-badge crud-badge--gold" style="text-decoration:none;">View Current Resume</a>
                            </div>
                        @endif
                        <input type="file" id="resume_file" name="resume_file" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="crud-field__hint">Leave empty to keep the current resume.</div>
                        @error('resume_file')<div class="crud-field__error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="crud-modal__foot">
                    <a href="{{ route('candidates.index') }}" class="crud-btn crud-btn--ghost">Cancel</a>
                    <button type="submit" class="crud-btn crud-btn--primary">Update Candidate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
