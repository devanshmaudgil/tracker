<?php

namespace App\Exports;

use App\Models\TrackerInfo;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TrackerExport
{
    protected $trackerIds;

    public function __construct($trackerIds)
    {
        // Accept either a single ID or an array of IDs
        $this->trackerIds = is_array($trackerIds) ? $trackerIds : [$trackerIds];
    }

    public function export()
    {
        $trackers = TrackerInfo::with(['month', 'client', 'region', 'leadRecruiter', 'trackerCandidates.candidate', 'trackerCandidates.pipelineStatus'])
            ->whereIn('id', $this->trackerIds)
            ->get();

        if ($trackers->isEmpty()) {
            return back()->with('error', 'No data found to export.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Recruitment Tracker');

        // Define Styles
        $headerGroupStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 12],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $columnHeaderStyle = [
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        // 1. Header Groups (Rows 1-3)
        $sheet->mergeCells('A1:F3');
        $sheet->setCellValue('A1', 'RADiiX INFINITEii - Recruitment & Staffing Tracker - Year 2025');
        $sheet->getStyle('A1:F3')->applyFromArray($headerGroupStyle);
        $sheet->getStyle('A1:F3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CCC0DA');

        $sheet->mergeCells('G1:L3');
        $sheet->setCellValue('G1', 'Client & Job Details');
        $sheet->getStyle('G1:L3')->applyFromArray($headerGroupStyle);
        $sheet->getStyle('G1:L3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FBE5D6');

        $sheet->mergeCells('M1:W3');
        $sheet->setCellValue('M1', 'Candidate Master Information');
        $sheet->getStyle('M1:W3')->applyFromArray($headerGroupStyle);
        $sheet->getStyle('M1:W3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2F0D9');

        $sheet->mergeCells('X1:AM3');
        $sheet->setCellValue('X1', 'Recruitment Workflow Tracking');
        $sheet->getStyle('X1:AM3')->applyFromArray($headerGroupStyle);
        $sheet->getStyle('X1:AM3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DFEBF7');

        $sheet->mergeCells('AN1:AY3');
        $sheet->setCellValue('AN1', 'Revenue & Business Tracking');
        $sheet->getStyle('AN1:AY3')->applyFromArray($headerGroupStyle);
        $sheet->getStyle('AN1:AY3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');

        $sheet->mergeCells('AZ1:AZ3');
        $sheet->setCellValue('AZ1', 'Additional Remarks');
        $sheet->getStyle('AZ1:AZ3')->applyFromArray($headerGroupStyle);
        $sheet->getStyle('AZ1:AZ3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('92D050');

        // 2. Column Headers (Row 4)
        $headers = [
            'S.No.', 'Position Receiving (Date)', 'Country', 'Position Name / Role', 'Lead Recruiter', 'Candidate Source Info',
            'Client Name', 'Job Location', 'Type of Job', 'Bill Rate / Salary Range', 'Priority', 'Submission Deadline',
            'Candidate Full Name', 'Email', 'Phone', 'Location', 'Work Status', 'Current Company', 'Pay-Rate', 'Agency Name', 'POC', 'POC Phone', 'Resume',
            'Candidate Identified', 'Resume Reviewed', 'Screening Call', 'Shortlisted', 'Resume Submitted', 'Internal Prep', 'Client Review', 'Interview 1', 'Interview 2', 'Additional Rounds', 'Client Decision', 'Confirmation', 'Offer Extended', 'Background Check', 'Project Start', 'Final Status',
            'Agreed Bill Rate', 'Candidate Pay Rate', 'Placement Type', 'Contract Duration', 'Monthly Revenue', 'Guarantee Period', 'Replacement', 'Time-to-Submit', 'Time-to-Interview', 'Time-to-Fill', 'Recruiter Notes', 'Score', 'NOTES'
        ];

        foreach (range(1, count($headers)) as $i) {
            $colName = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->setCellValue($colName . '4', $headers[$i-1]);
            
            $color = 'FFFFFF';
            if ($i <= 6) $color = 'FFF2CC'; 
            elseif ($i <= 12) $color = 'FBE5D6';
            elseif ($i <= 23) $color = 'E2F0D9';
            elseif ($i <= 39) $color = 'DFEBF7';
            elseif ($i <= 51) $color = 'F2F2F2';
            else $color = '92D050';

            $sheet->getStyle($colName . '4')->applyFromArray($columnHeaderStyle);
            $sheet->getStyle($colName . '4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
            $sheet->getColumnDimension($colName)->setWidth(15);
        }
        $sheet->getRowDimension(4)->setRowHeight(40);

        // 3. Data Rows
        $row = 5;
        $globalIndex = 1;
        foreach ($trackers as $tracker) {
            $candidateCount = $tracker->trackerCandidates->count();
            
            if ($candidateCount == 0) {
                // No candidates: just print the tracker info in one row
                $this->populateTrackerInfo($sheet, $row, $globalIndex++, $tracker);
                // Set borders for this single row
                $sheet->getStyle('A' . $row . ':AZ' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $row++;
            } else {
                // Has candidates: Group rows if count > 1
                $startRow = $row;
                foreach ($tracker->trackerCandidates as $tc) {
                    // For the first row of this group, print the tracker info
                    if ($row == $startRow) {
                        $this->populateTrackerInfo($sheet, $row, $globalIndex++, $tracker);
                    }
                    
                    // Populate candidate info for every row
                    $this->populateCandidateInfo($sheet, $row, $tc);
                    
                    // Set borders for this row (specific to candidate columns M-AZ)
                    $sheet->getStyle('M' . $row . ':AZ' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    
                    $row++;
                }
                
                // If we had multiple candidates (or even 1), merge the Tracker Info columns (A-L)
                // If count > 1, merge cells from startRow to endRow (row-1)
                $endRow = $row - 1;
                if ($endRow > $startRow) {
                    // Merge columns A through L
                    foreach (range('A', 'L') as $col) {
                        $sheet->mergeCells($col . $startRow . ':' . $col . $endRow);
                    }
                    // Improve vertical alignment for grouped cells
                    $sheet->getStyle('A' . $startRow . ':L' . $endRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }
                
                // Set borders for the Tracker Info block (A-L)
                $sheet->getStyle('A' . $startRow . ':L' . $endRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Tracker_Export_' . date('Y-m-d_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    private function populateTrackerInfo($sheet, $row, $index, $tracker)
    {
        $sheet->setCellValue('A' . $row, $index);
        $sheet->setCellValue('B' . $row, $tracker->prd ? $tracker->prd->format('d-M-Y') : '');
        $sheet->setCellValue('C' . $row, $tracker->cf ?? '');
        $sheet->setCellValue('D' . $row, $tracker->position ?? '');
        $sheet->setCellValue('E' . $row, $tracker->leadRecruiter ? $tracker->leadRecruiter->username : '');
        $sheet->setCellValue('F' . $row, $tracker->csi ?? '');
        
        $sheet->setCellValue('G' . $row, $tracker->client ? $tracker->client->client : '');
        $sheet->setCellValue('H' . $row, $tracker->region ? ($tracker->region->city . ', ' . $tracker->region->region) : '');
        $sheet->setCellValue('I' . $row, ucfirst($tracker->type_of_job ?? ''));
        $sheet->setCellValue('J' . $row, $tracker->bill_rate_salary_range ?? '');
        $sheet->setCellValue('K' . $row, $tracker->priority ?? '');
        $sheet->setCellValue('L' . $row, $tracker->submission_deadline ? $tracker->submission_deadline->format('d-M-Y') : '');
    }

    private function populateCandidateInfo($sheet, $row, $tc)
    {
        if (!$tc || !$tc->candidate) return;

        $candidate = $tc->candidate;
        $status = $tc->pipelineStatus;

        $sheet->setCellValue('M' . $row, $candidate->full_name);
        $sheet->setCellValue('N' . $row, $candidate->email);
        $sheet->setCellValue('O' . $row, $candidate->phone);
        $sheet->setCellValue('P' . $row, $candidate->location ? $candidate->location->city : '');
        $sheet->setCellValue('Q' . $row, $candidate->work_status);
        $sheet->setCellValue('R' . $row, $candidate->current_company);
        $sheet->setCellValue('S' . $row, $candidate->pay_rate);
        $sheet->setCellValue('T' . $row, $candidate->agency_name);
        $sheet->setCellValue('U' . $row, $candidate->agency_poc);
        $sheet->setCellValue('V' . $row, $candidate->agency_poc_phone);
        $sheet->setCellValue('W' . $row, $candidate->resume_file_url);

        if ($status) {
            $sheet->setCellValue('X' . $row, $status->candidate_identified ? 'Yes' : 'No');
            $sheet->setCellValue('Y' . $row, $status->resume_reviewed_by_recruiter . ($status->resume_reviewed_date ? ' - ' . $status->resume_reviewed_date->format('d-M-Y') : ''));
            $sheet->setCellValue('Z' . $row, $status->recruiter_screening_call . ($status->recruiter_screening_call_date ? ' - ' . $status->recruiter_screening_call_date->format('d-M-Y') : ''));
            $sheet->setCellValue('AA' . $row, $status->candidate_shortlisted ? 'Yes' : 'No');
            $sheet->setCellValue('AB' . $row, $status->resume_submitted_to_client);
            $sheet->setCellValue('AC' . $row, $status->radix_internal_interview_prep . ($status->radix_internal_interview_prep_date ? ' - ' . $status->radix_internal_interview_prep_date->format('d-M-Y') : ''));
            $sheet->setCellValue('AD' . $row, $status->client_resume_review);
            $sheet->setCellValue('AE' . $row, $status->client_interview_round_1_date ? $status->client_interview_round_1_date->format('d-M-Y') : '');
            $sheet->setCellValue('AF' . $row, $status->client_interview_round_2_date ? $status->client_interview_round_2_date->format('d-M-Y') : '');
            $sheet->setCellValue('AG' . $row, $status->additional_rounds ? 'Yes' : 'No');
            $sheet->setCellValue('AH' . $row, $status->client_decision . ($status->client_decision_date ? ' - ' . $status->client_decision_date->format('d-M-Y') : ''));
            $sheet->setCellValue('AI' . $row, ($status->client_confirmation_received ? 'Yes' : 'No') . ($status->client_confirmation_date ? ' - ' . $status->client_confirmation_date->format('d-M-Y') : ''));
            $sheet->setCellValue('AJ' . $row, ($status->offer_extended_to_candidate ? 'Yes' : 'No') . ($status->offer_extended_date ? ' - ' . $status->offer_extended_date->format('d-M-Y') : ''));
            $sheet->setCellValue('AK' . $row, $status->background_check);
            $sheet->setCellValue('AL' . $row, $status->candidate_project_start_date ? $status->candidate_project_start_date->format('d-M-Y') : '');
            $sheet->setCellValue('AM' . $row, $status->final_status_placement_completion . ($status->placement_completion_date ? ' - ' . $status->placement_completion_date->format('d-M-Y') : ''));
        }
    }
}
