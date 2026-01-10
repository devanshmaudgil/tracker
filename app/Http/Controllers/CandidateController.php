<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Region;
use App\Models\TrackerCandidate;
use App\Models\CandidatePipelineStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    public function index()
    {
        $candidates = Candidate::with(['location', 'trackerCandidates.trackerInfo'])->orderBy('id', 'desc')->get();
        return view('candidates.index', compact('candidates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'location_id' => 'nullable|exists:regions,id',
            'work_status' => 'nullable|in:GC,PR,Citizen,H1B,OPT',
            'current_company' => 'nullable|string|max:255',
            'pay_rate' => 'nullable|string|max:255',
            'agency_name' => 'nullable|string|max:255',
            'agency_poc' => 'nullable|string|max:255',
            'agency_poc_phone' => 'nullable|string|max:255',
            'resume_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->all();

        if ($request->hasFile('resume_file')) {
            $path = $request->file('resume_file')->store('resumes', 'supabase');
            $data['resume_file'] = $path;
        }

        if (isset($data['resume_file']) && str_contains($data['resume_file'], 'http')) {
            unset($data['resume_file']);
        }

        $trackerId = $request->input('tracker_id');
        if ($trackerId) {
            unset($data['tracker_id']);
        }

        $candidate = Candidate::create($data);

        // If created from tracker page, auto-assign to that job
        if ($trackerId) {
            // Check if already assigned
            $existing = TrackerCandidate::where('tracker_info_id', $trackerId)
                ->where('candidate_id', $candidate->id)
                ->first();
            
            if (!$existing) {
                $trackerCandidate = TrackerCandidate::create([
                    'tracker_info_id' => $trackerId,
                    'candidate_id' => $candidate->id,
                ]);

                // Create initial pipeline status record
                CandidatePipelineStatus::create([
                    'tracker_candidate_id' => $trackerCandidate->id,
                ]);
            }

            return redirect()->route('tracker.info', $trackerId)->with('success', 'Candidate created and assigned to job successfully.');
        }

        return redirect()->route('candidates.index')->with('success', 'Candidate added successfully.');
    }

    public function edit(string $id)
    {
        $candidate = Candidate::findOrFail($id);
        $regions = Region::orderBy('region', 'asc')->get();
        return view('candidates.edit', compact('candidate', 'regions'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'location_id' => 'nullable|exists:regions,id',
            'work_status' => 'nullable|in:GC,PR,Citizen,H1B,OPT',
            'current_company' => 'nullable|string|max:255',
            'pay_rate' => 'nullable|string|max:255',
            'agency_name' => 'nullable|string|max:255',
            'agency_poc' => 'nullable|string|max:255',
            'agency_poc_phone' => 'nullable|string|max:255',
            'resume_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $candidate = Candidate::findOrFail($id);
        $data = $request->all();

        if ($request->hasFile('resume_file')) {
            if ($candidate->resume_file) {
                $oldPath = $candidate->resume_file;
                if (str_contains($oldPath, 'http')) {
                    $oldPath = preg_replace('/^.*\/object\/(?:public|sign)\/[^\/]+\//', '', $oldPath);
                }
                Storage::disk('supabase')->delete($oldPath);
            }
            $path = $request->file('resume_file')->store('resumes', 'supabase');
            $data['resume_file'] = $path;
        }

        $candidate->update($data);

        return redirect()->route('candidates.index')->with('success', 'Candidate updated successfully.');
    }

    public function destroy(string $id)
    {
        $candidate = Candidate::findOrFail($id);
        if ($candidate->resume_file) {
            $path = $candidate->resume_file;
            if (str_contains($path, 'http')) {
                $path = preg_replace('/^.*\/object\/(?:public|sign)\/[^\/]+\//', '', $path);
            }
            Storage::disk('supabase')->delete($path);
        }
        $candidate->delete();

        return redirect()->route('candidates.index')->with('success', 'Candidate deleted successfully.');
    }
}
