@extends('layouts.app')

@section('title', 'Edit Candidate')

@section('content')
<div class="content-header">
    <h1>Edit Candidate</h1>
    <a href="{{ route('candidates.index') }}" class="btn btn-secondary">Back</a>
</div>

<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <form method="POST" action="{{ route('candidates.update', $candidate->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="full_name">Candidate Full Name *</label>
            <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $candidate->full_name) }}" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('full_name')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Candidate Email Id *</label>
            <input type="email" id="email" name="email" value="{{ old('email', $candidate->email) }}" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('email')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="phone">Candidate Phone</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $candidate->phone) }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('phone')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="location_id">Candidate Location (City, State)</label>
            <select id="location_id" name="location_id" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Location</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}" {{ (old('location_id', $candidate->location_id) == $region->id) ? 'selected' : '' }}>
                        @if($region->city)
                            {{ $region->city }}, {{ $region->region }}
                        @else
                            {{ $region->region }}
                        @endif
                    </option>
                @endforeach
            </select>
            @error('location_id')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="work_status">Candidate Work Status</label>
            <select id="work_status" name="work_status" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Work Status</option>
                <option value="GC" {{ (old('work_status', $candidate->work_status) == 'GC') ? 'selected' : '' }}>GC</option>
                <option value="PR" {{ (old('work_status', $candidate->work_status) == 'PR') ? 'selected' : '' }}>PR</option>
                <option value="Citizen" {{ (old('work_status', $candidate->work_status) == 'Citizen') ? 'selected' : '' }}>Citizen</option>
                <option value="H1B" {{ (old('work_status', $candidate->work_status) == 'H1B') ? 'selected' : '' }}>H1B</option>
                <option value="OPT" {{ (old('work_status', $candidate->work_status) == 'OPT') ? 'selected' : '' }}>OPT</option>
            </select>
            @error('work_status')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="current_company">Current Company</label>
            <input type="text" id="current_company" name="current_company" value="{{ old('current_company', $candidate->current_company) }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('current_company')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="pay_rate">Candidate Pay-Rate</label>
            <input type="text" id="pay_rate" name="pay_rate" value="{{ old('pay_rate', $candidate->pay_rate) }}" placeholder="e.g., $50/hr or $100k/year" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('pay_rate')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="agency_name">Candidate Agency Name</label>
            <input type="text" id="agency_name" name="agency_name" value="{{ old('agency_name', $candidate->agency_name) }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('agency_name')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="agency_poc">Candidate Agency POC (Point-of-Contact)</label>
            <input type="text" id="agency_poc" name="agency_poc" value="{{ old('agency_poc', $candidate->agency_poc) }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('agency_poc')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="agency_poc_phone">Candidate Agency POC Phone Number</label>
            <input type="text" id="agency_poc_phone" name="agency_poc_phone" value="{{ old('agency_poc_phone', $candidate->agency_poc_phone) }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('agency_poc_phone')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="resume_file">Resume Link / File Name (PDF, JPG, PNG)</label>
            @if($candidate->resume_file_url)
                <div style="margin-bottom: 10px;">
                    <a href="{{ $candidate->resume_file_url }}" target="_blank" style="color: #f1cd86;">Current Resume</a>
                </div>
            @endif
            <input type="file" id="resume_file" name="resume_file" accept=".pdf,.jpg,.jpeg,.png" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            <small style="color: #666;">Leave empty to keep current resume</small>
            @error('resume_file')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('candidates.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #0a2d29;
    }
</style>
@endsection

