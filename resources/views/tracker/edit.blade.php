@extends('layouts.app')

@section('title', 'Edit Demand')

@section('content')
<div class="content-header">
    <h1>Edit Demand</h1>
    <a href="{{ route('tracker.index') }}" class="btn btn-secondary">Back</a>
</div>

<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <form method="POST" action="{{ route('tracker.update', $trackerInfo->id) }}">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="month_id">Month *</label>
            <select id="month_id" name="month_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Month</option>
                @foreach($months as $month)
                    <option value="{{ $month->id }}" {{ (old('month_id', $trackerInfo->month_id) == $month->id) ? 'selected' : '' }}>{{ $month->month }}</option>
                @endforeach
            </select>
            @error('month_id')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="client_id">Client Name</label>
            <select id="client_id" name="client_id" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Client</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ (old('client_id', $trackerInfo->client_id) == $client->id) ? 'selected' : '' }}>{{ $client->client }}</option>
                @endforeach
            </select>
            @error('client_id')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        @include('tracker._region_multiselect')

        <div class="form-group">
            <label for="type_of_job">Type of Job</label>
            <select id="type_of_job" name="type_of_job" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Type</option>
                <option value="onsite" {{ (old('type_of_job', $trackerInfo->type_of_job) == 'onsite') ? 'selected' : '' }}>Onsite</option>
                <option value="remote" {{ (old('type_of_job', $trackerInfo->type_of_job) == 'remote') ? 'selected' : '' }}>Remote</option>
                <option value="hybrid" {{ (old('type_of_job', $trackerInfo->type_of_job) == 'hybrid') ? 'selected' : '' }}>Hybrid</option>
            </select>
            @error('type_of_job')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="bill_rate_salary_range">Bill Rate / Salary Range</label>
            <input type="text" id="bill_rate_salary_range" name="bill_rate_salary_range" value="{{ old('bill_rate_salary_range', $trackerInfo->bill_rate_salary_range) }}" placeholder="Enter bill rate or salary range" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('bill_rate_salary_range')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Priority</option>
                <option value="Urgent" {{ (old('priority', $trackerInfo->priority) == 'Urgent') ? 'selected' : '' }}>Urgent</option>
                <option value="High" {{ (old('priority', $trackerInfo->priority) == 'High') ? 'selected' : '' }}>High</option>
                <option value="Intermediate" {{ (old('priority', $trackerInfo->priority) == 'Intermediate') ? 'selected' : '' }}>Intermediate</option>
                <option value="Medium" {{ (old('priority', $trackerInfo->priority) == 'Medium') ? 'selected' : '' }}>Medium</option>
                <option value="Low" {{ (old('priority', $trackerInfo->priority) == 'Low') ? 'selected' : '' }}>Low</option>
            </select>
            @error('priority')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="submission_deadline">Target Date</label>
            <input type="date" id="submission_deadline" name="submission_deadline" value="{{ old('submission_deadline', $trackerInfo->submission_deadline ? $trackerInfo->submission_deadline->format('Y-m-d') : '') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('submission_deadline')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="notes">Job Notes</label>
            <textarea id="notes" name="notes" rows="3" placeholder="Additional remarks about this requisition…" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">{{ old('notes', $trackerInfo->notes) }}</textarea>
            @error('notes')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="prd">PRD (Position Receiving Date)</label>
            <input type="date" id="prd" name="prd" value="{{ old('prd', $trackerInfo->prd ? $trackerInfo->prd->format('Y-m-d') : '') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('prd')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="cf">CF (Country of Position Fulfillment)</label>
            <select id="cf" name="cf" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Country</option>
                <option value="Canada" {{ (old('cf', $trackerInfo->cf) == 'Canada') ? 'selected' : '' }}>Canada</option>
                <option value="USA" {{ (old('cf', $trackerInfo->cf) == 'USA') ? 'selected' : '' }}>USA</option>
                <option value="India" {{ (old('cf', $trackerInfo->cf) == 'India') ? 'selected' : '' }}>India</option>
            </select>
            @error('cf')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="position">Position</label>
            <input type="text" id="position" name="position" value="{{ old('position', $trackerInfo->position) }}" placeholder="Enter position" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('position')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="job_description">Insert Job Description</label>
            <textarea id="job_description" name="job_description" rows="10" placeholder="Paste the full job description here (role details, skills, responsibilities, etc.)" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; font-size: 14px; line-height: 1.5; resize: vertical;">{{ old('job_description', $trackerInfo->job_description) }}</textarea>
            @error('job_description')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="lr">LR (Lead Recruiter)</label>
            <select id="lr" name="lr" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Lead Recruiter</option>
                @foreach($leadRecruiters as $recruiter)
                    <option value="{{ $recruiter->id }}" {{ (old('lr', $trackerInfo->lr) == $recruiter->id) ? 'selected' : '' }}>{{ $recruiter->username ?? 'ID: ' . $recruiter->id }}</option>
                @endforeach
            </select>
            @error('lr')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="csi">CSI (Candidate Source Info)</label>
            <select id="csi" name="csi" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Source</option>
                <option value="Internal" {{ (old('csi', $trackerInfo->csi) == 'Internal') ? 'selected' : '' }}>Internal</option>
                <option value="External" {{ (old('csi', $trackerInfo->csi) == 'External') ? 'selected' : '' }}>External</option>
                <option value="Dice" {{ (old('csi', $trackerInfo->csi) == 'Dice') ? 'selected' : '' }}>Dice</option>
                <option value="Linkedin" {{ (old('csi', $trackerInfo->csi) == 'Linkedin') ? 'selected' : '' }}>Linkedin</option>
                <option value="Others" {{ (old('csi', $trackerInfo->csi) == 'Others') ? 'selected' : '' }}>Others</option>
            </select>
            @error('csi')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('tracker.index') }}" class="btn btn-secondary">Cancel</a>
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

