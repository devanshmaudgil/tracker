@extends('layouts.app')

@section('title', 'Create Demand')

@section('content')
<div class="content-header">
    <h1>Create Demand</h1>
    <a href="{{ route('tracker.index') }}" class="btn btn-secondary">Back</a>
</div>

<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <form method="POST" action="{{ route('tracker.store') }}">
        @csrf
        
        <div class="form-group">
            <label for="month_id">Month *</label>
            <select id="month_id" name="month_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Month</option>
                @foreach($months as $month)
                    <option value="{{ $month->id }}" {{ old('month_id') == $month->id ? 'selected' : '' }}>{{ $month->month }}</option>
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
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->client }}</option>
                @endforeach
            </select>
            @error('client_id')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="region_id">Job Location</label>
            <div style="position: relative;">
                <input type="text" id="region_search" placeholder="Search job location..." autocomplete="off" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                <select id="region_id" name="region_id" style="display: none;">
                    <option value="">Select Job Location</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" data-region="{{ $region->region }}" data-city="{{ $region->city ?? '' }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                            @if($region->city)
                                {{ $region->city }}, {{ $region->region }}
                            @else
                                {{ $region->region }}
                            @endif
                        </option>
                    @endforeach
                </select>
                <div id="region_dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; border-radius: 0 0 4px 4px; max-height: 200px; overflow-y: auto; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                </div>
            </div>
            @error('region_id')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="type_of_job">Type of Job</label>
            <select id="type_of_job" name="type_of_job" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Type</option>
                <option value="onsite" {{ old('type_of_job') == 'onsite' ? 'selected' : '' }}>Onsite</option>
                <option value="remote" {{ old('type_of_job') == 'remote' ? 'selected' : '' }}>Remote</option>
                <option value="hybrid" {{ old('type_of_job') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
            </select>
            @error('type_of_job')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="bill_rate_salary_range">Bill Rate / Salary Range</label>
            <input type="text" id="bill_rate_salary_range" name="bill_rate_salary_range" value="{{ old('bill_rate_salary_range') }}" placeholder="Enter bill rate or salary range" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('bill_rate_salary_range')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Priority</option>
                <option value="Urgent" {{ old('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="High" {{ old('priority') == 'High' ? 'selected' : '' }}>High</option>
                <option value="Medium" {{ old('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                <option value="Low" {{ old('priority') == 'Low' ? 'selected' : '' }}>Low</option>
            </select>
            @error('priority')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="submission_deadline">Submission Deadline</label>
            <input type="date" id="submission_deadline" name="submission_deadline" value="{{ old('submission_deadline') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('submission_deadline')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="prd">PRD (Position Receiving Date)</label>
            <input type="date" id="prd" name="prd" value="{{ old('prd') }}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('prd')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="cf">CF (Country of Position Fulfillment)</label>
            <select id="cf" name="cf" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Country</option>
                <option value="Canada" {{ old('cf') == 'Canada' ? 'selected' : '' }}>Canada</option>
                <option value="USA" {{ old('cf') == 'USA' ? 'selected' : '' }}>USA</option>
            </select>
            @error('cf')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="position">Position Title</label>
            <input type="text" id="position" name="position" value="{{ old('position') }}" placeholder="Enter position" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            @error('position')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="lr">LR (Lead Recruiter)</label>
            <select id="lr" name="lr" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Lead Recruiter</option>
                @foreach($leadRecruiters as $recruiter)
                    <option value="{{ $recruiter->id }}" {{ old('lr') == $recruiter->id ? 'selected' : '' }}>{{ $recruiter->username ?? 'ID: ' . $recruiter->id }}</option>
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
                <option value="Internal" {{ old('csi') == 'Internal' ? 'selected' : '' }}>Internal</option>
                <option value="External" {{ old('csi') == 'External' ? 'selected' : '' }}>External</option>
                <option value="Dice" {{ old('csi') == 'Dice' ? 'selected' : '' }}>Dice</option>
                <option value="Linkedin" {{ old('csi') == 'Linkedin' ? 'selected' : '' }}>Linkedin</option>
                <option value="Others" {{ old('csi') == 'Others' ? 'selected' : '' }}>Others</option>
            </select>
            @error('csi')
                <div style="color: #dc3545; margin-top: 5px; font-size: 14px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Create</button>
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
    
    #region_dropdown div:hover {
        background-color: #f1cd86 !important;
        color: #0a2d29;
    }

    #region_search:focus {
        outline: none;
        border-color: #f1cd86;
        box-shadow: 0 0 0 3px rgba(241, 205, 134, 0.1);
    }
    
    #region_dropdown div {
        text-align: left;
    }
</style>

<script>
    // Searchable dropdown for regions
    const regionSearch = document.getElementById('region_search');
    const regionSelect = document.getElementById('region_id');
    const regionDropdown = document.getElementById('region_dropdown');
    const allRegionOptions = Array.from(regionSelect.querySelectorAll('option'));

    // Set initial value if old() has region_id
    @if(old('region_id'))
        const selectedRegion = regionSelect.querySelector('option[value="{{ old('region_id') }}"]');
        if (selectedRegion) {
            regionSearch.value = selectedRegion.textContent.trim();
        }
    @endif

    function filterRegions(searchTerm) {
        const filtered = allRegionOptions.filter(option => {
            const regionName = option.dataset.region || '';
            const cityName = option.dataset.city || '';
            const displayText = option.textContent.trim();
            const searchLower = searchTerm.toLowerCase();
            
            return regionName.toLowerCase().includes(searchLower) ||
                   cityName.toLowerCase().includes(searchLower) ||
                   displayText.toLowerCase().includes(searchLower);
        });

        regionDropdown.innerHTML = '';
        if (filtered.length > 0 && searchTerm.length > 0) {
            regionDropdown.style.display = 'block';
            filtered.forEach(option => {
                const div = document.createElement('div');
                div.textContent = option.textContent;
                div.style.padding = '8px 12px';
                div.style.cursor = 'pointer';
                div.style.borderBottom = '1px solid #eee';
                div.style.textAlign = 'left';
                div.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f5f5f5';
                });
                div.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = 'white';
                });
                div.addEventListener('click', function() {
                    regionSearch.value = option.textContent;
                    regionSelect.value = option.value;
                    regionDropdown.style.display = 'none';
                });
                regionDropdown.appendChild(div);
            });
        } else {
            regionDropdown.style.display = 'none';
        }
    }

    regionSearch.addEventListener('input', function() {
        filterRegions(this.value);
    });

    regionSearch.addEventListener('focus', function() {
        if (this.value.length > 0) {
            filterRegions(this.value);
        }
    });

    document.addEventListener('click', function(event) {
        if (!event.target.closest('#region_search') && !event.target.closest('#region_dropdown')) {
            regionDropdown.style.display = 'none';
        }
    });
</script>
@endsection

