@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="content-header">
    <h1>Clients</h1>
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
                <th>S.No</th>
                <th>Client</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $index => $client)
                <tr data-id="{{ $client->id }}"
                    data-client="{{ $client->client }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $client->client }}</td>
                    <td>
                        <div class="action-buttons">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="viewRecord({{ $client->id }})" title="View">View</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="editRecord({{ $client->id }})" title="Edit">Edit</button>
                            <form method="POST" action="{{ route('clients.info.destroy', $client->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; padding: 30px; color: #666;">No clients found. <a href="#" onclick="openModal(); return false;" style="color: #f1cd86;">Add your first client</a></td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="clientModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Client</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form id="clientForm" method="POST" action="{{ route('clients.info.store') }}">
            @csrf
            <input type="hidden" id="formMethod" name="_method" value="POST">
            <input type="hidden" id="recordId" name="id">
            
            <div class="form-group">
                <label for="client">Client Name *</label>
                <input type="text" id="client" name="client" value="{{ old('client') }}" required placeholder="Enter client name">
                @error('client')
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

    #clientForm {
        padding: 20px;
    }
</style>

<script>
    let currentRecordId = null;

    function openModal(recordId = null, rowElement = null) {
        currentRecordId = recordId;
        const modal = document.getElementById('clientModal');
        const form = document.getElementById('clientForm');
        const modalTitle = document.getElementById('modalTitle');
        const submitBtn = document.getElementById('submitBtn');
        const formMethod = document.getElementById('formMethod');
        const recordIdInput = document.getElementById('recordId');

        if (recordId && rowElement) {
            // Use data from table row for instant loading
            modalTitle.textContent = 'Edit Client';
            submitBtn.textContent = 'Update';
            formMethod.value = 'PUT';
            form.action = `/clients/info/${recordId}`;
            recordIdInput.value = recordId;
            
            // Populate form instantly from data attributes
            document.getElementById('client').value = rowElement.dataset.client || '';
            
            modal.style.display = 'block';
        } else if (recordId) {
            // Fallback: Fetch data if row element not provided
            modalTitle.textContent = 'Edit Client';
            submitBtn.textContent = 'Update';
            formMethod.value = 'PUT';
            form.action = `/clients/info/${recordId}`;
            recordIdInput.value = recordId;
            
            fetch(`/clients/info/${recordId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('client').value = data.client || '';
                });
            modal.style.display = 'block';
        } else {
            modalTitle.textContent = 'Add Client';
            submitBtn.textContent = 'Add';
            formMethod.value = 'POST';
            form.action = '{{ route('clients.info.store') }}';
            recordIdInput.value = '';
            form.reset();
            modal.style.display = 'block';
        }
    }

    function closeModal() {
        document.getElementById('clientModal').style.display = 'none';
        document.getElementById('clientForm').reset();
        currentRecordId = null;
    }

    function viewRecord(id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (row) {
            const clientName = row.cells[1].textContent.trim();
            alert(`Client Information:\n\nS.No: ${row.cells[0].textContent.trim()}\nClient: ${clientName}`);
        } else {
            // Fallback to API call
            fetch(`/clients/info/${id}`)
                .then(response => response.json())
                .then(data => {
                    alert(`Client Information:\n\nID: ${data.id}\nClient: ${data.client}`);
                });
        }
    }

    function editRecord(id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        openModal(id, row);
    }

    window.onclick = function(event) {
        const modal = document.getElementById('clientModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
@endsection

