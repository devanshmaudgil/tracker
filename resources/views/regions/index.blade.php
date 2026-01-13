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
    
    .search-bar-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .results-count {
        color: #666;
        font-size: 14px;
    }
    
    .search-input-wrapper {
        width: 300px;
    }
    
    .search-input-wrapper input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .search-input-wrapper input:focus {
        outline: none;
        border-color: #f1cd86;
    }
    
    .no-results-row {
        display: none;
    }
    
    /* Pagination Styling */
    .pagination-container {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }
    
    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        gap: 5px;
    }
    
    .pagination li {
        display: inline-block;
    }
    
    .pagination li a,
    .pagination li span {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        color: #0a2d29;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .pagination li a:hover {
        background-color: #f1cd86;
        border-color: #f1cd86;
        color: #0a2d29;
    }
    
    .pagination li.active span {
        background-color: #0a2d29;
        border-color: #0a2d29;
        color: white;
    }
    
    .pagination li.disabled span {
        color: #999;
        cursor: not-allowed;
    }

    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 10px;
    }
</style>

<!-- Search Bar -->
<div class="search-bar-container">
    <div class="results-count" id="resultsCount">
        @if($regions->total() > 0)
            Showing {{ $regions->firstItem() }} to {{ $regions->lastItem() }} of {{ $regions->total() }} entries
        @else
            No entries found
        @endif
    </div>
    <div class="search-input-wrapper">
        <input type="text" 
               id="searchInput" 
               placeholder="Search by city or state..." 
               value="{{ request('search') }}"
               autocomplete="off">
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>City</th>
                <th>State</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="regionsTableBody">
            @include('regions._table')
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div id="paginationContainer">
    @if($regions->hasPages())
        <div class="pagination-container">
            {{ $regions->appends(request()->query())->links('vendor.pagination.custom') }}
        </div>
    @endif
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
    // AJAX search and pagination functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('regionsTableBody');
        const paginationContainer = document.getElementById('paginationContainer');
        const resultsCount = document.getElementById('resultsCount');
        let debounceTimer;
        
        function fetchData(url, search = '') {
            const fetchUrl = new URL(url);
            if (search) {
                fetchUrl.searchParams.set('search', search);
            }
            
            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = data.table;
                paginationContainer.innerHTML = data.pagination;
                resultsCount.textContent = data.count_text;
            })
            .catch(error => console.error('Error:', error));
        }

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const searchTerm = this.value;
                debounceTimer = setTimeout(() => {
                    fetchData('{{ route('regions.index') }}', searchTerm);
                }, 500);
            });
        }

        // Handle pagination clicks via AJAX
        document.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('#paginationContainer a');
            if (paginationLink) {
                e.preventDefault();
                const url = paginationLink.getAttribute('href');
                const searchTerm = searchInput ? searchInput.value : '';
                fetchData(url, searchTerm);
            }
        });
    });
</script>
@endsection

