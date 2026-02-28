@extends('layouts.app')

@section('title', 'Candidates')

@section('content')
<div class="content-header" style="margin-bottom: 15px;">
    <h1 style="font-size: 20px;">Candidates</h1>
    <button type="button" class="btn btn-primary btn-sm" onclick="openModal()">+ Add Candidate</button>
</div>

<style>
    .table-container {
        overflow-x: auto;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .table-container table {
        font-size: 11px;
        width: 100%;
        border-collapse: collapse;
    }
    .table-container th {
        background-color: #0a2d29;
        color: white;
        padding: 8px 4px;
        text-align: center;
        white-space: nowrap;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table-container td {
        padding: 6px 4px;
        text-align: center;
        border-bottom: 1px solid #eee;
        color: #444;
        word-break: break-all;
    }
    .btn-compact {
        padding: 4px 8px;
        font-size: 10px;
        margin: 2px;
    }
    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 4px;
    }
    .resume-link {
        color: #0a2d29;
        text-decoration: none;
        font-weight: 600;
    }
    .resume-link:hover {
        text-decoration: underline;
    }
    .job-id-link {
        color: #f1cd86;
        background: #0a2d29;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        margin: 1px;
        display: inline-block;
    }
</style>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Location</th>
                <th>Work Status</th>
                <th>Company</th>
                <th>Rate</th>
                <th>Agency</th>
                <th>POC</th>
                <th>POC Phone</th>
                <th>Jobs</th>
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
                    <td style="font-size: 10px;">{{ $candidate->email }}</td>
                    <td>{{ $candidate->phone ?? '-' }}</td>
                    <td>
                        @if($candidate->location)
                            @if($candidate->location->city)
                                {{ $candidate->location->city }}
                            @else
                                {{ $candidate->location->region }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td><span style="font-size: 10px; background: #eee; padding: 2px 4px; border-radius: 3px;">{{ $candidate->work_status ?? '-' }}</span></td>
                    <td>{{ $candidate->current_company ?? '-' }}</td>
                    <td>{{ $candidate->pay_rate ?? '-' }}</td>
                    <td>{{ $candidate->agency_name ?? '-' }}</td>
                    <td>{{ $candidate->agency_poc ?? '-' }}</td>
                    <td>{{ $candidate->agency_poc_phone ?? '-' }}</td>
                    <td>
                        @if($candidate->trackerCandidates->count() > 0)
                            @foreach($candidate->trackerCandidates as $index => $trackerCandidate)
                                <a href="{{ route('tracker.info', $trackerCandidate->tracker_info_id) }}" class="job-id-link">#{{ $trackerCandidate->tracker_info_id }}</a>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($candidate->resume_file_url)
                            <a href="{{ $candidate->resume_file_url }}" target="_blank" class="resume-link">View</a>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('candidates.edit', $candidate->id) }}" class="btn btn-secondary btn-compact" title="Edit">Edit</a>
                            <form method="POST" action="{{ route('candidates.destroy', $candidate->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this candidate?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-compact" title="Delete">Del</button>
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
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="form-group">
                    <label for="full_name" style="font-size: 11px;">Candidate Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                    @error('full_name')
                        <div style="color: #dc3545; margin-top: 2px; font-size: 11px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" style="font-size: 11px;">Candidate Email Id *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                    @error('email')
                        <div style="color: #dc3545; margin-top: 2px; font-size: 11px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" style="font-size: 11px;">Candidate Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                </div>

                <div class="form-group">
                    <label for="location_id" style="font-size: 11px;">Location</label>
                    <select id="location_id" name="location_id" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                        <option value="">Select Location</option>
                        @foreach(\App\Models\Region::orderBy('region', 'asc')->get() as $region)
                            <option value="{{ $region->id }}" {{ old('location_id') == $region->id ? 'selected' : '' }}>
                                {{ $region->city ? $region->city . ', ' : '' }}{{ $region->region }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="work_status" style="font-size: 11px;">Work Status</label>
                    <select id="work_status" name="work_status" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                        <option value="">Select Work Status</option>
                        <option value="GC">GC</option>
                        <option value="PR">PR</option>
                        <option value="Citizen">Citizen</option>
                        <option value="H1B">H1B</option>
                        <option value="OPT">OPT</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="current_company" style="font-size: 11px;">Current Company</label>
                    <input type="text" id="current_company" name="current_company" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                </div>

                <div class="form-group">
                    <label for="pay_rate" style="font-size: 11px;">Pay-Rate</label>
                    <input type="text" id="pay_rate" name="pay_rate" placeholder="e.g., $50/hr" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                </div>

                <div class="form-group">
                    <label for="agency_name" style="font-size: 11px;">Agency Name</label>
                    <input type="text" id="agency_name" name="agency_name" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                </div>

                <div class="form-group">
                    <label for="agency_poc" style="font-size: 11px;">Agency POC</label>
                    <input type="text" id="agency_poc" name="agency_poc" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                </div>

                <div class="form-group">
                    <label for="agency_poc_phone" style="font-size: 11px;">Agency POC Phone</label>
                    <input type="text" id="agency_poc_phone" name="agency_poc_phone" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label for="resume_file" style="font-size: 11px;">Resume (PDF/JPG/PNG)</label>
                    <input type="file" id="resume_file" name="resume_file" style="width: 100%; font-size: 11px;">
                </div>
            </div>

            <div style="margin-top: 10px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="submit" class="btn btn-primary btn-sm">Save Candidate</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal()">Cancel</button>
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

