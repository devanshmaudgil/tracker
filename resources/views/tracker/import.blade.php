@extends('layouts.app')

@section('title', 'Import Tracker Data')

@section('content')
<div class="content-header">
    <h1>Import Tracker Data from Excel</h1>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('tracker.index') }}" class="btn btn-secondary">Back to Tracker</a>
    </div>
</div>

<style>
    .import-container {
        background: white;
        border-radius: 8px;
        padding: 30px;
        max-width: 800px;
        margin: 0 auto;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .import-instructions {
        background: #f8f9fa;
        border-left: 4px solid #f1cd86;
        padding: 15px;
        margin-bottom: 25px;
        border-radius: 4px;
    }
    
    .import-instructions h3 {
        color: #0a2d29;
        margin-bottom: 10px;
        font-size: 16px;
    }
    
    .import-instructions ul {
        margin: 10px 0;
        padding-left: 20px;
    }
    
    .import-instructions li {
        margin: 5px 0;
        color: #555;
    }
    
    .upload-area {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        background: #fafafa;
        transition: all 0.3s;
    }
    
    .upload-area:hover {
        border-color: #f1cd86;
        background: #fff;
    }
    
    .upload-area input[type="file"] {
        display: none;
    }
    
    .upload-label {
        cursor: pointer;
        color: #0a2d29;
        font-size: 16px;
        font-weight: 500;
    }
    
    .upload-icon {
        font-size: 48px;
        color: #f1cd86;
        margin-bottom: 15px;
    }
    
    .file-info {
        margin-top: 15px;
        color: #666;
        font-size: 14px;
    }
    
    .error-list {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        padding: 15px;
        margin-top: 20px;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .error-list h4 {
        color: #721c24;
        margin-bottom: 10px;
    }
    
    .error-list ul {
        list-style: none;
        padding: 0;
    }
    
    .error-list li {
        color: #721c24;
        padding: 5px 0;
        border-bottom: 1px solid #f5c6cb;
    }
    
    .error-list li:last-child {
        border-bottom: none;
    }
</style>

<div class="import-container">
    <div class="import-instructions">
        <h3>📋 Import Instructions</h3>
        <ul>
            <li><strong>File Format:</strong> Excel file (.xlsx or .xls)</li>
            <li><strong>Template:</strong> Use the same format as your export file</li>
            <li><strong>Headers:</strong> First 4 rows should contain headers (will be skipped)</li>
            <li><strong>Data Starts:</strong> Row 5 onwards</li>
            <li><strong>Auto-Detection:</strong> The system will automatically:
                <ul style="margin-top: 5px;">
                    <li>Create missing clients, regions, and users</li>
                    <li>Match existing candidates by email or name</li>
                    <li>Determine pipeline status based on filled fields</li>
                    <li>Skip empty rows</li>
                </ul>
            </li>
            <li><strong>Max File Size:</strong> 10 MB</li>
        </ul>
    </div>

    <form action="{{ route('tracker.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
        @csrf
        
        <div class="upload-area" id="uploadArea">
            <div class="upload-icon">📁</div>
            <label for="excel_file" class="upload-label">
                <strong>Click to select</strong> or drag and drop your Excel file here
            </label>
            <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls">
            <div class="file-info" id="fileInfo">No file selected</div>
        </div>

        <div style="margin-top: 25px; text-align: center;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px; font-size: 16px;">
                📤 Upload and Import
            </button>
        </div>
    </form>

    @if(session('import_errors'))
        <div class="error-list">
            <h4>⚠️ Import Errors ({{ count(session('import_errors')) }})</h4>
            <ul>
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<script>
    const fileInput = document.getElementById('excel_file');
    const fileInfo = document.getElementById('fileInfo');
    const uploadArea = document.getElementById('uploadArea');

    fileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
            fileInfo.textContent = `Selected: ${file.name} (${fileSize} MB)`;
            fileInfo.style.color = '#0a2d29';
            fileInfo.style.fontWeight = '600';
        } else {
            fileInfo.textContent = 'No file selected';
            fileInfo.style.color = '#666';
            fileInfo.style.fontWeight = 'normal';
        }
    });

    // Drag and drop functionality
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#f1cd86';
        this.style.background = '#fff';
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = '#ddd';
        this.style.background = '#fafafa';
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#ddd';
        this.style.background = '#fafafa';
        
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        }
    });

    // Form submission validation
    document.getElementById('importForm').addEventListener('submit', function(e) {
        if (!fileInput.files || !fileInput.files[0]) {
            e.preventDefault();
            alert('Please select a file to import');
            return false;
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Importing... Please wait';
    });
</script>
@endsection
