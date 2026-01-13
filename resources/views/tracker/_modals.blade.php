<!-- Create Candidate Modal -->
<div id="createCandidateModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header" style="padding: 10px 15px;">
            <h2 style="font-size: 18px; margin: 0;">Create New Candidate</h2>
            <span class="close" onclick="closeCreateCandidateModal()">&times;</span>
        </div>
        <form id="createCandidateForm" method="POST" action="{{ route('candidates.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tracker_id" value="{{ $trackerInfo->id }}">
            
            <div style="padding: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="form-group">
                    <label for="create_full_name" style="font-size: 12px; font-weight: 600;">Full Name *</label>
                    <input type="text" id="create_full_name" name="full_name" value="{{ old('full_name') }}" required style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                </div>

                <div class="form-group">
                    <label for="create_email" style="font-size: 12px; font-weight: 600;">Email *</label>
                    <input type="email" id="create_email" name="email" value="{{ old('email') }}" required style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                </div>

                <div class="form-group">
                    <label for="create_phone" style="font-size: 12px; font-weight: 600;">Phone</label>
                    <input type="text" id="create_phone" name="phone" value="{{ old('phone') }}" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                </div>

                <div class="form-group">
                    <label for="create_work_status" style="font-size: 12px; font-weight: 600;">Work Status</label>
                    <select id="create_work_status" name="work_status" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                        <option value="">Select Status</option>
                        <option value="GC">GC</option>
                        <option value="PR">PR</option>
                        <option value="Citizen">Citizen</option>
                        <option value="H1B">H1B</option>
                        <option value="OPT">OPT</option>
                    </select>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label for="location_search" style="font-size: 12px; font-weight: 600;">Location (City, State)</label>
                    <div style="position: relative;">
                        <input type="text" id="location_search" placeholder="Search location..." autocomplete="off" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                        <input type="hidden" id="create_location_id" name="location_id">
                        <div id="location_dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; border-radius: 0 0 4px 4px; max-height: 150px; overflow-y: auto; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            @foreach(\App\Models\Region::orderBy('region', 'asc')->get() as $region)
                                <div class="location-option" data-value="{{ $region->id }}" style="padding: 6px 10px; cursor: pointer; border-bottom: 1px solid #f0f0f0; font-size: 12px;">
                                    {{ $region->city ? $region->city . ', ' : '' }}{{ $region->region }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="create_current_company" style="font-size: 12px; font-weight: 600;">Current Company</label>
                    <input type="text" id="create_current_company" name="current_company" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                </div>

                <div class="form-group">
                    <label for="create_pay_rate" style="font-size: 12px; font-weight: 600;">Pay-Rate</label>
                    <input type="text" id="create_pay_rate" name="pay_rate" placeholder="e.g., $50/hr" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                </div>

                <div class="form-group">
                    <label for="create_agency_name" style="font-size: 12px; font-weight: 600;">Agency Name</label>
                    <input type="text" id="create_agency_name" name="agency_name" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                </div>

                <div class="form-group">
                    <label for="create_agency_poc" style="font-size: 12px; font-weight: 600;">Agency POC</label>
                    <input type="text" id="create_agency_poc" name="agency_poc" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label for="create_resume" style="font-size: 12px; font-weight: 600;">Resume (PDF/Doc)</label>
                    <input type="file" id="create_resume" name="resume" style="width: 100%; font-size: 12px;">
                </div>
            </div>
            
            <div class="modal-footer" style="padding: 10px 15px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-compact" onclick="closeCreateCandidateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-compact">Create & Assign</button>
            </div>
        </form>
    </div>
</div>
