@extends('layouts.app')

@section('title', 'Candidates')

@section('content')
<div class="content-header">
    <h1>Candidates</h1>
    <button type="button" class="btn btn-primary" onclick="openModal()">Add</button>
</div>

<style>
    .table-container {
        overflow-x: hidden;
        overflow-y: hidden;
    }
    .table-container table {
        font-size: 12px;
        width: 100%;
        table-layout: fixed;
    }
    .table-container th,
    .table-container td {
        text-align: center;
        padding: 6px 8px;
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .table-container th {
        font-size: 11px;
    }
    .table-container td {
        max-width: 120px;
    }
    body {
        overflow-x: hidden;
    }
    .content-wrapper {
        overflow-x: hidden;
    }
</style>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Location</th>
                <th>Work Status</th>
                <th>Current Company</th>
                <th>Pay-Rate</th>
                <th>Agency Name</th>
                <th>Agency POC</th>
                <th>Agency POC Phone</th>
                <th>Job ID</th>
                <th>Resume</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($candidates as $index => $candidate)
                <tr data-id="{{ $candidate->id }}"
                    data-full-name="{{ $candidate->full_name }}"
                    data-email="{{ $candidate->email }}"
                    data-phone="{{ $candidate->phone ?? '' }}"
                    data-location-id="{{ $candidate->location_id ?? '' }}"
                    data-work-status="{{ $candidate->work_status ?? '' }}"
                    data-current-company="{{ $candidate->current_company ?? '' }}"
                    data-pay-rate="{{ $candidate->pay_rate ?? '' }}"
                    data-agency-name="{{ $candidate->agency_name ?? '' }}"
                    data-agency-poc="{{ $candidate->agency_poc ?? '' }}"
                    data-agency-poc-phone="{{ $candidate->agency_poc_phone ?? '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $candidate->full_name }}</td>
                    <td>{{ $candidate->email }}</td>
                    <td>{{ $candidate->phone ?? 'N/A' }}</td>
                    <td>
                        @if($candidate->location)
                            @if($candidate->location->city)
                                {{ $candidate->location->city }}, {{ $candidate->location->region }}
                            @else
                                {{ $candidate->location->region }}
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $candidate->work_status ?? 'N/A' }}</td>
                    <td>{{ $candidate->current_company ?? 'N/A' }}</td>
                    <td>{{ $candidate->pay_rate ?? 'N/A' }}</td>
                    <td>{{ $candidate->agency_name ?? 'N/A' }}</td>
                    <td>{{ $candidate->agency_poc ?? 'N/A' }}</td>
                    <td>{{ $candidate->agency_poc_phone ?? 'N/A' }}</td>
                    <td>
                        @if($candidate->trackerCandidates->count() > 0)
                            @foreach($candidate->trackerCandidates as $index => $trackerCandidate)
                                <a href="{{ route('tracker.info', $trackerCandidate->tracker_info_id) }}" style="color: #f1cd86; text-decoration: none;">#{{ $trackerCandidate->tracker_info_id }}</a>@if($index < $candidate->trackerCandidates->count() - 1), @endif
                            @endforeach
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($candidate->resume_file_url)
                            <a href="{{ $candidate->resume_file_url }}" target="_blank" style="color: #f1cd86;">View Resume</a>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('candidates.edit', $candidate->id) }}" class="btn btn-secondary btn-sm" title="Edit">Edit</a>
                            <form method="POST" action="{{ route('candidates.destroy', $candidate->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this candidate?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" style="text-align: center; padding: 30px; color: #666;">No candidates found. <a href="#" onclick="openModal(); return false;" style="color: #f1cd86;">Add your first candidate</a></td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="candidateModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add Candidate</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form id="candidateForm" method="POST" action="{{ route('candidates.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label for="full_name">Candidate Full Name *</label>
                <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                @error('full_name')
                    <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Candidate Email Id *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                @error('email')
                    <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone">Candidate Phone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                @error('phone')
                    <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="location_id">Candidate Location (City, State)</label>
                <select id="location_id" name="location_id" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Select Location</option>
                    @foreach(\App\Models\Region::orderBy('region', 'asc')->get() as $region)
                        <option value="{{ $region->id }}" {{ old('location_id') == $region->id ? 'selected' : '' }}>
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
                    <option value="GC" {{ old('work_status') == 'GC' ? 'selected' : '' }}>GC</option>
                    <option value="PR" {{ old('work_status') == 'PR' ? 'selected' : '' }}>PR</option>
                    <option value="Citizen" {{ old('work_status') == 'Citizen' ? 'selected' : '' }}>Citizen</option>
                    <option value="H1B" {{ old('work_status') == 'H1B' ? 'selected' : '' }}>H1B</option>
                    <option value="OPT" {{ old('work_status') == 'OPT' ? 'selected' : '' }}>OPT</option>
                </select>
                @error('work_status')
                    <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="current_company">Current Company</label>
                <input type="text" id="current_company" name="current_company" value="{{ old('current_company') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                @error('current_company')
                    <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="pay_rate">Candidate Pay-Rate</label>
                <input type="text" id="pay_rate" name="pay_rate" value="{{ old('pay_rate') }}" placeholder="e.g., $50/hr or $100k/year" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                @error('pay_rate')
                    <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="agency_name">Candidate Agency Name</label>
                <input type="text" id="agency_name" name="agency_name" value="{{ old('agency_name') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                @error('agency_name')
                    <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="agency_poc">Candidate Agency POC (Point-of-Contact)</label>
                <input type="text" id="agency_poc" name="agency_poc" value="{{ old('agency_poc') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                @error('agency_poc')
                    <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="agency_poc_phone">Candidate Agency POC Phone Number</label>
                <input type="text" id="agency_poc_phone" name="agency_poc_phone" value="{{ old('agency_poc_phone') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                @error('agency_poc_phone')
                    <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="resume_file">Resume Link / File Name (PDF, JPG, PNG)</label>
                <input type="file" id="resume_file" name="resume_file" accept=".pdf,.jpg,.jpeg,.png" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                @error('resume_file')
                    <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Add</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: white;
        margin: 3% auto;
        padding: 0;
        border-radius: 8px;
        width: 90%;
        max-width: 700px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #0a2d29;
        color: white;
        border-radius: 8px 8px 0 0;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .modal-header h2 {
        margin: 0;
        color: white;
        font-size: 24px;
    }

    .close {
        color: white;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 20px;
    }

    .close:hover {
        opacity: 0.7;
    }

    #candidateForm {
        padding: 20px;
    }

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

<script>
    function openModal() {
        document.getElementById('candidateModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('candidateModal').style.display = 'none';
        document.getElementById('candidateForm').reset();
    }

    window.onclick = function(event) {
        const modal = document.getElementById('candidateModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
@endsection

