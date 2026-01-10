@extends('layouts.app')

@section('title', 'Regions')

@section('content')
<div class="content-header">
    <h1>Regions</h1>
    <button type="button" class="btn btn-primary" onclick="openModal()">Add</button>
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
                <th>City</th>
                <th>State</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($regions as $region)
                <tr data-id="{{ $region->id }}"
                    data-region="{{ $region->region }}"
                    data-city="{{ $region->city ?? '' }}">
                    <td>{{ $region->city ?? '-' }}</td>
                    <td>{{ $region->region }}</td>
                    <td>
                        <div class="action-buttons">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="editRecord({{ $region->id }})" title="Edit">Edit</button>
                            <form method="POST" action="{{ route('regions.destroy', $region->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this region?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; padding: 30px; color: #666;">No regions found. <a href="#" onclick="openModal(); return false;" style="color: #f1cd86;">Add your first region</a></td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="regionModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Region</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form id="regionForm" method="POST" action="{{ route('regions.store') }}">
            @csrf
            <input type="hidden" id="formMethod" name="_method" value="POST">
            <input type="hidden" id="recordId" name="id">
            
            <div class="form-group">
                <label for="region">State/Province *</label>
                <input type="text" id="region" name="region" value="{{ old('region') }}" required placeholder="Enter state or province" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                @error('region')
                    <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="Enter city (optional)" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                @error('city')
                    <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary" id="submitBtn">Add</button>
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
        margin: 5% auto;
        padding: 0;
        border-radius: 8px;
        width: 90%;
        max-width: 600px;
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

    #regionForm {
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
    let currentRecordId = null;

    function openModal(recordId = null, rowElement = null) {
        currentRecordId = recordId;
        const modal = document.getElementById('regionModal');
        const form = document.getElementById('regionForm');
        const modalTitle = document.getElementById('modalTitle');
        const submitBtn = document.getElementById('submitBtn');
        const formMethod = document.getElementById('formMethod');
        const recordIdInput = document.getElementById('recordId');

        if (recordId && rowElement) {
            modalTitle.textContent = 'Edit Region';
            submitBtn.textContent = 'Update';
            formMethod.value = 'PUT';
            form.action = `/regions/${recordId}`;
            recordIdInput.value = recordId;
            
            document.getElementById('region').value = rowElement.dataset.region || '';
            document.getElementById('city').value = rowElement.dataset.city || '';
            
            modal.style.display = 'block';
        } else {
            modalTitle.textContent = 'Add Region';
            submitBtn.textContent = 'Add';
            formMethod.value = 'POST';
            form.action = '{{ route('regions.store') }}';
            recordIdInput.value = '';
            form.reset();
            modal.style.display = 'block';
        }
    }

    function closeModal() {
        document.getElementById('regionModal').style.display = 'none';
        document.getElementById('regionForm').reset();
        currentRecordId = null;
    }

    function editRecord(id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        openModal(id, row);
    }

    window.onclick = function(event) {
        const modal = document.getElementById('regionModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
@endsection

