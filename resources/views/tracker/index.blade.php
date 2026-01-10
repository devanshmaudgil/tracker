@extends('layouts.app')

@section('title', 'Tracker Information')

@section('content')
<div class="content-header">
    <h1>Tracker Information</h1>
    <a href="{{ route('tracker.create') }}" class="btn btn-primary">Add</a>
</div>

<style>
    .table-container table {
        font-size: 12px;
    }
    .table-container th,
    .table-container td {
        text-align: center;
        padding: 6px 8px;
    }
    .table-container th {
        font-size: 11px;
        white-space: nowrap;
    }
</style>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Month</th>
                <th>Client Name</th>
                <th>Job Location</th>
                <th>Type of Job</th>
                <th>Bill Rate / Salary Range</th>
                <th>Priority</th>
                <th>Submission Deadline</th>
                <th>PRD</th>
                <th>CF</th>
                <th>Position</th>
                <th>LR</th>
                <th>CSI</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trackerInfos as $info)
                @php
                    $regionName = '';
                    if ($info->region) {
                        $regionName = $info->region->city 
                            ? $info->region->city . ', ' . $info->region->region 
                            : $info->region->region;
                    }
                @endphp
                <tr data-id="{{ $info->id }}"
                    data-month-id="{{ $info->month_id }}"
                    data-client-id="{{ $info->client_id ?? '' }}"
                    data-region-id="{{ $info->region_id ?? '' }}"
                    data-region-name="{{ $regionName }}"
                    data-type-of-job="{{ $info->type_of_job ?? '' }}"
                    data-bill-rate="{{ $info->bill_rate_salary_range ?? '' }}"
                    data-priority="{{ $info->priority ?? '' }}"
                    data-submission-deadline="{{ $info->submission_deadline ? $info->submission_deadline->format('Y-m-d') : '' }}"
                    data-prd="{{ $info->prd ? $info->prd->format('Y-m-d') : '' }}"
                    data-cf="{{ $info->cf ?? '' }}"
                    data-position="{{ $info->position ?? '' }}"
                    data-lr="{{ $info->lr ?? '' }}"
                    data-csi="{{ $info->csi ?? '' }}">
                    <td>{{ $info->id }}</td>
                    <td>{{ $info->month->month ?? 'N/A' }}</td>
                    <td>{{ $info->client->client ?? 'N/A' }}</td>
                    <td>
                        @if($info->region)
                            @if($info->region->city)
                                {{ $info->region->city }}, {{ $info->region->region }}
                            @else
                                {{ $info->region->region }}
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $info->type_of_job ? ucfirst($info->type_of_job) : 'N/A' }}</td>
                    <td>{{ $info->bill_rate_salary_range ?? 'N/A' }}</td>
                    <td>{{ $info->priority ?? 'N/A' }}</td>
                    <td>{{ $info->submission_deadline ? \Carbon\Carbon::parse($info->submission_deadline)->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $info->prd ? \Carbon\Carbon::parse($info->prd)->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $info->cf ?? 'N/A' }}</td>
                    <td>{{ $info->position ?? 'N/A' }}</td>
                    <td>{{ $info->leadRecruiter ? $info->leadRecruiter->username : '-' }}</td>
                    <td>{{ $info->csi ?? 'N/A' }}</td>
                    <td>
                        <div class="action-buttons">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="viewRecord({{ $info->id }})" title="View">View</button>
                            <a href="{{ route('tracker.edit', $info->id) }}" class="btn btn-secondary btn-sm" title="Edit">Edit</a>
                            <a href="{{ route('tracker.info', $info->id) }}" class="btn btn-secondary btn-sm" title="View Details">Details</a>
                            <form method="POST" action="{{ route('tracker.destroy', $info->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" style="text-align: center; padding: 30px; color: #666;">No records found. <a href="{{ route('tracker.create') }}" style="color: #f1cd86;">Add your first record</a></td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    function viewRecord(id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (row) {
            const monthCell = row.cells[1].textContent.trim();
            const clientCell = row.cells[2].textContent.trim();
            const jobLocationCell = row.cells[3].textContent.trim();
            const typeOfJobCell = row.cells[4].textContent.trim();
            const billRateCell = row.cells[5].textContent.trim();
            const priorityCell = row.cells[6].textContent.trim();
            const submissionDeadlineCell = row.cells[7].textContent.trim();
            const prdCell = row.cells[8].textContent.trim();
            const cfCell = row.cells[9].textContent.trim();
            const positionCell = row.cells[10].textContent.trim();
            const lrCell = row.cells[11].textContent.trim();
            const csiCell = row.cells[12].textContent.trim();
            
            alert(`Tracker Information:\n\nID: ${id}\nMonth: ${monthCell}\nClient Name: ${clientCell}\nJob Location: ${jobLocationCell}\nType of Job: ${typeOfJobCell}\nBill Rate / Salary Range: ${billRateCell}\nPriority: ${priorityCell}\nSubmission Deadline: ${submissionDeadlineCell}\nPRD: ${prdCell}\nCF: ${cfCell}\nPosition: ${positionCell}\nLR: ${lrCell}\nCSI: ${csiCell}`);
        } else {
            // Fallback to API call
            fetch(`/tracker/info/${id}/json`)
                .then(response => response.json())
                .then(data => {
                    let jobLocation = 'N/A';
                    if (data.region) {
                        if (data.region.city) {
                            jobLocation = data.region.city + ', ' + data.region.region;
                        } else {
                            jobLocation = data.region.region;
                        }
                    }
                    alert(`Tracker Information:\n\nID: ${data.id}\nMonth: ${data.month ? data.month.month : 'N/A'}\nClient Name: ${data.client ? data.client.client : 'N/A'}\nJob Location: ${jobLocation}\nType of Job: ${data.type_of_job || 'N/A'}\nBill Rate / Salary Range: ${data.bill_rate_salary_range || 'N/A'}\nPriority: ${data.priority || 'N/A'}\nSubmission Deadline: ${data.submission_deadline || 'N/A'}\nPRD: ${data.prd || 'N/A'}\nCF: ${data.cf || 'N/A'}\nPosition: ${data.position || 'N/A'}\nLR: ${data.lead_recruiter ? data.lead_recruiter.username : 'N/A'}\nCSI: ${data.csi || 'N/A'}`);
                });
        }
    }
</script>
@endsection
