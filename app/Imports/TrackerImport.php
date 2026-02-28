<?php

namespace App\Imports;

use App\Models\TrackerInfo;
use App\Models\Candidate;
use App\Models\TrackerCandidate;
use App\Models\CandidatePipelineStatus;
use App\Models\Client;
use App\Models\Region;
use App\Models\StaffUser;
use App\Models\Month;
use App\Models\JobStatus;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrackerImport
{
    protected $errors = [];
    protected $successCount = 0;
    protected $skipCount = 0;

    public function import($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            
            // Start from row 5 (after headers in rows 1-4)
            $highestRow = $sheet->getHighestRow();
            
            DB::beginTransaction();
            
            for ($row = 5; $row <= $highestRow; $row++) {
                try {
                    $this->processRow($sheet, $row);
                } catch (\Exception $e) {
                    $this->errors[] = "Row {$row}: " . $e->getMessage();
                    Log::error("Import error at row {$row}", ['error' => $e->getMessage()]);
                }
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'imported' => $this->successCount,
                'skipped' => $this->skipCount,
                'errors' => $this->errors
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $this->errors
            ];
        }
    }

    protected function processRow($sheet, $rowNumber)
    {
        // Read all columns from the row
        $data = [
            'prd' => $this->getCellValue($sheet, 'B', $rowNumber),
            'cf' => $this->getCellValue($sheet, 'C', $rowNumber),
            'position' => $this->getCellValue($sheet, 'D', $rowNumber),
            'lead_recruiter' => $this->getCellValue($sheet, 'E', $rowNumber),
            'csi' => $this->getCellValue($sheet, 'F', $rowNumber),
            'client' => $this->getCellValue($sheet, 'G', $rowNumber),
            'location' => $this->getCellValue($sheet, 'H', $rowNumber),
            'job_type' => $this->getCellValue($sheet, 'I', $rowNumber),
            'bill_rate' => $this->getCellValue($sheet, 'J', $rowNumber),
            'priority' => $this->getCellValue($sheet, 'K', $rowNumber),
            'deadline' => $this->getCellValue($sheet, 'L', $rowNumber),
            
            // Candidate info
            'candidate_name' => $this->getCellValue($sheet, 'M', $rowNumber),
            'candidate_email' => $this->getCellValue($sheet, 'N', $rowNumber),
            'candidate_phone' => $this->getCellValue($sheet, 'O', $rowNumber),
            'candidate_location' => $this->getCellValue($sheet, 'P', $rowNumber),
            'work_status' => $this->getCellValue($sheet, 'Q', $rowNumber),
            'current_company' => $this->getCellValue($sheet, 'R', $rowNumber),
            'pay_rate' => $this->getCellValue($sheet, 'S', $rowNumber),
            'agency_name' => $this->getCellValue($sheet, 'T', $rowNumber),
            'agency_poc' => $this->getCellValue($sheet, 'U', $rowNumber),
            'agency_poc_phone' => $this->getCellValue($sheet, 'V', $rowNumber),
            'resume_url' => $this->getCellValue($sheet, 'W', $rowNumber),
            
            // Pipeline status
            'candidate_identified' => $this->getCellValue($sheet, 'X', $rowNumber),
            'resume_reviewed' => $this->getCellValue($sheet, 'Y', $rowNumber),
            'recruiter_screening' => $this->getCellValue($sheet, 'Z', $rowNumber),
            'candidate_shortlisted' => $this->getCellValue($sheet, 'AA', $rowNumber),
            'resume_submitted' => $this->getCellValue($sheet, 'AB', $rowNumber),
            'radix_prep' => $this->getCellValue($sheet, 'AC', $rowNumber),
            'client_review' => $this->getCellValue($sheet, 'AD', $rowNumber),
            'interview_round_1' => $this->getCellValue($sheet, 'AE', $rowNumber),
            'interview_round_2' => $this->getCellValue($sheet, 'AF', $rowNumber),
            'additional_rounds' => $this->getCellValue($sheet, 'AG', $rowNumber),
            'client_decision' => $this->getCellValue($sheet, 'AH', $rowNumber),
            'client_confirmation' => $this->getCellValue($sheet, 'AI', $rowNumber),
            'offer_extended' => $this->getCellValue($sheet, 'AJ', $rowNumber),
            'background_check' => $this->getCellValue($sheet, 'AK', $rowNumber),
            'project_start' => $this->getCellValue($sheet, 'AL', $rowNumber),
            'final_status' => $this->getCellValue($sheet, 'AM', $rowNumber),
        ];

        // Skip if no position (empty row)
        if (empty($data['position'])) {
            $this->skipCount++;
            return;
        }

        // Find or create tracker info
        $tracker = $this->findOrCreateTracker($data);
        
        // If there's candidate data, process it
        if (!empty($data['candidate_name'])) {
            $candidate = $this->findOrCreateCandidate($data);
            $trackerCandidate = $this->assignCandidateToTracker($tracker, $candidate);
            $this->updatePipelineStatus($trackerCandidate, $data);
        }

        $this->successCount++;
    }

    protected function findOrCreateTracker($data)
    {
        // Try to find existing tracker by position and client
        $client = $this->findOrCreateClient($data['client']);
        $leadRecruiter = $this->findOrCreateUser($data['lead_recruiter']);
        $region = $this->findOrCreateRegion($data['location']);
        
        // Extract month from PRD or use current month
        $month = $this->extractMonth($data['prd']);
        
        $tracker = TrackerInfo::where('position', $data['position'])
            ->where('client_id', $client->id)
            ->first();

        if (!$tracker) {
            $tracker = TrackerInfo::create([
                'month_id' => $month->id,
                'prd' => $this->parseDate($data['prd']),
                'cf' => $data['cf'],
                'position' => $data['position'],
                'lead_recruiter_id' => $leadRecruiter->id,
                'csi' => $data['csi'],
                'client_id' => $client->id,
                'region_id' => $region->id,
                'type_of_job' => strtolower($data['job_type'] ?? 'contract'),
                'bill_rate_salary_range' => $data['bill_rate'],
                'priority' => $data['priority'],
                'submission_deadline' => $this->parseDate($data['deadline']),
            ]);
        }

        return $tracker;
    }

    protected function findOrCreateCandidate($data)
    {
        // Try to find by email first, then by name
        $candidate = null;
        
        if (!empty($data['candidate_email'])) {
            $candidate = Candidate::where('email', $data['candidate_email'])->first();
        }
        
        if (!$candidate && !empty($data['candidate_name'])) {
            $candidate = Candidate::where('full_name', $data['candidate_name'])->first();
        }

        if (!$candidate) {
            $location = $this->findOrCreateRegion($data['candidate_location']);
            
            $candidate = Candidate::create([
                'full_name' => $data['candidate_name'],
                'email' => $data['candidate_email'],
                'phone' => $data['candidate_phone'],
                'location_id' => $location->id,
                'work_status' => $data['work_status'],
                'current_company' => $data['current_company'],
                'pay_rate' => $data['pay_rate'],
                'agency_name' => $data['agency_name'],
                'agency_poc' => $data['agency_poc'],
                'agency_poc_phone' => $data['agency_poc_phone'],
                'resume_file_url' => $data['resume_url'],
            ]);
        }

        return $candidate;
    }

    protected function assignCandidateToTracker($tracker, $candidate)
    {
        $trackerCandidate = TrackerCandidate::where('tracker_info_id', $tracker->id)
            ->where('candidate_id', $candidate->id)
            ->first();

        if (!$trackerCandidate) {
            $trackerCandidate = TrackerCandidate::create([
                'tracker_info_id' => $tracker->id,
                'candidate_id' => $candidate->id,
                'current_status_id' => 1, // Will be updated based on pipeline
            ]);
        }

        return $trackerCandidate;
    }

    protected function updatePipelineStatus($trackerCandidate, $data)
    {
        // Determine current status based on filled fields
        $statusId = $this->determineStatusFromData($data);
        
        $pipelineData = [
            'candidate_identified' => $this->parseBoolean($data['candidate_identified']),
            'resume_reviewed_by_recruiter' => $this->extractText($data['resume_reviewed']),
            'resume_reviewed_date' => $this->extractDate($data['resume_reviewed']),
            'recruiter_screening_call' => $this->extractText($data['recruiter_screening']),
            'recruiter_screening_call_date' => $this->extractDate($data['recruiter_screening']),
            'candidate_shortlisted' => $this->parseBoolean($data['candidate_shortlisted']),
            'resume_submitted_to_client' => $this->extractText($data['resume_submitted']),
            'radix_internal_interview_prep' => $this->extractText($data['radix_prep']),
            'radix_internal_interview_prep_date' => $this->extractDate($data['radix_prep']),
            'client_resume_review' => $this->extractText($data['client_review']),
            'client_interview_round_1_date' => $this->parseDate($data['interview_round_1']),
            'client_interview_round_2_date' => $this->parseDate($data['interview_round_2']),
            'additional_rounds' => $this->parseBoolean($data['additional_rounds']),
            'client_decision' => $this->extractText($data['client_decision']),
            'client_decision_date' => $this->extractDate($data['client_decision']),
            'client_confirmation_received' => $this->parseBoolean($data['client_confirmation']),
            'client_confirmation_date' => $this->extractDate($data['client_confirmation']),
            'offer_extended_to_candidate' => $this->parseBoolean($data['offer_extended']),
            'offer_extended_date' => $this->extractDate($data['offer_extended']),
            'background_check' => $this->extractText($data['background_check']),
            'candidate_project_start_date' => $this->parseDate($data['project_start']),
            'final_status_placement_completion' => $this->extractText($data['final_status']),
            'placement_completion_date' => $this->extractDate($data['final_status']),
        ];

        CandidatePipelineStatus::updateOrCreate(
            ['tracker_candidate_id' => $trackerCandidate->id],
            $pipelineData
        );

        // Update tracker candidate status
        $trackerCandidate->update(['current_status_id' => $statusId]);
    }

    protected function determineStatusFromData($data)
    {
        // Logic to determine status based on which fields are filled
        if (!empty($data['final_status'])) return 14; // Placement Completion
        if (!empty($data['project_start'])) return 13; // Project Start
        if (!empty($data['background_check'])) return 12; // Background Check
        if (!empty($data['offer_extended'])) return 11; // Offer Extended
        if (!empty($data['client_confirmation'])) return 10; // Client Confirmation
        if (!empty($data['client_decision'])) return 9; // Client Decision
        if (!empty($data['additional_rounds'])) return 8; // Additional Rounds
        if (!empty($data['interview_round_2'])) return 7; // Interview Round 2
        if (!empty($data['interview_round_1'])) return 6; // Interview Round 1
        if (!empty($data['client_review'])) return 5; // Client Review
        if (!empty($data['radix_prep'])) return 4; // Radix Prep
        if (!empty($data['resume_submitted'])) return 3; // Resume Submitted
        if (!empty($data['recruiter_screening'])) return 2; // Recruiter Screening
        
        return 1; // Candidate Identified (default)
    }

    // Helper methods
    protected function getCellValue($sheet, $column, $row)
    {
        return $sheet->getCell($column . $row)->getValue();
    }

    protected function parseDate($value)
    {
        if (empty($value)) return null;
        
        try {
            if (is_numeric($value)) {
                // Excel date serial number
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return date('Y-m-d', strtotime($value));
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseBoolean($value)
    {
        if (empty($value)) return false;
        return in_array(strtolower($value), ['yes', '1', 'true', 'y']);
    }

    protected function extractText($value)
    {
        if (empty($value)) return null;
        
        // If value contains " - ", split and take first part
        if (strpos($value, ' - ') !== false) {
            return trim(explode(' - ', $value)[0]);
        }
        
        return trim($value);
    }

    protected function extractDate($value)
    {
        if (empty($value)) return null;
        
        // If value contains " - ", split and take second part (date)
        if (strpos($value, ' - ') !== false) {
            $parts = explode(' - ', $value);
            if (count($parts) > 1) {
                return $this->parseDate($parts[1]);
            }
        }
        
        return $this->parseDate($value);
    }

    protected function findOrCreateClient($name)
    {
        if (empty($name)) {
            $name = 'Unknown Client';
        }
        
        return Client::firstOrCreate(
            ['client' => $name],
            ['client' => $name]
        );
    }

    protected function findOrCreateUser($username)
    {
        if (empty($username)) {
            $username = 'Unknown';
        }
        
        return StaffUser::firstOrCreate(
            ['username' => $username],
            [
                'username' => $username,
                'email' => strtolower(str_replace(' ', '.', $username)) . '@radix.com',
                'password' => bcrypt('password123'),
            ]
        );
    }

    protected function findOrCreateRegion($location)
    {
        if (empty($location)) {
            $location = 'Unknown';
        }
        
        // Parse "City, Region" format
        $parts = explode(',', $location);
        $city = trim($parts[0] ?? $location);
        $region = trim($parts[1] ?? $city);
        
        return Region::firstOrCreate(
            ['city' => $city, 'region' => $region],
            ['city' => $city, 'region' => $region]
        );
    }

    protected function extractMonth($prdDate)
    {
        $date = $this->parseDate($prdDate);
        
        if ($date) {
            $monthName = date('F Y', strtotime($date));
        } else {
            $monthName = date('F Y');
        }
        
        return Month::firstOrCreate(
            ['month' => $monthName],
            ['month' => $monthName]
        );
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getSkipCount()
    {
        return $this->skipCount;
    }
}
