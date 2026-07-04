<?php

namespace App\Exports;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DashboardExport
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  list<array<string, mixed>>  $positions
     * @param  array<string, string>  $filterLabels
     */
    public function __construct(
        private array $payload,
        private array $positions,
        private array $filterLabels,
    ) {
    }

    public function download()
    {
        $spreadsheet = new Spreadsheet();
        $summary = $spreadsheet->getActiveSheet();
        $summary->setTitle('Executive Summary');
        $this->buildSummarySheet($summary);

        $detail = $spreadsheet->createSheet();
        $detail->setTitle('Position Details');
        $this->buildDetailSheet($detail);

        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'RADiiX_Dashboard_Report_' . date('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function buildSummarySheet($sheet): void
    {
        $kpis = $this->payload['kpis'] ?? [];
        $attention = $this->payload['attention'] ?? [];

        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'RADiiX INFINITEii');
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 22, 'color' => ['rgb' => 'F1CD86']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0A2D29']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(36);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'Analytics Dashboard — Recruitment Intelligence Report');
        $sheet->getStyle('A2:H2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F3D38']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(24);

        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'Generated: ' . Carbon::now()->format('F d, Y — g:i A') . '  |  PASSION · PURPOSE · PRIDE');
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A5', 'Applied Filters');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('0A2D29');

        $row = 6;
        foreach ($this->filterLabels as $label => $value) {
            $sheet->setCellValue('A' . $row, $label);
            $sheet->setCellValue('B' . $row, $value);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
        }

        $row += 1;
        $sheet->setCellValue('A' . $row, 'Key Performance Indicators');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('0A2D29');
        $row++;

        $kpiRows = [
            ['Total Positions', $kpis['total'] ?? 0],
            ['Open', $kpis['open'] ?? 0],
            ['In Progress', $kpis['in_progress'] ?? 0],
            ['Placed', $kpis['placed'] ?? 0],
            ['Rejected', $kpis['rejected'] ?? 0],
            ['Placement Rate', ($kpis['placement_rate'] ?? 0) . '%'],
            ['Win Rate', ($kpis['win_rate'] ?? 0) . '%'],
            ['Total Candidates', $kpis['total_candidates'] ?? 0],
            ['Active Candidates', $kpis['active_candidates'] ?? 0],
            ['Needs Attention', $attention['total'] ?? 0],
            ['Overdue Deadlines', $attention['overdue'] ?? 0],
            ['Due This Week', $attention['due_soon'] ?? 0],
            ['Urgent Priority', $attention['urgent'] ?? 0],
        ];

        $sheet->fromArray(['Metric', 'Value'], null, 'A' . $row);
        $headerRow = $row;
        $row++;
        foreach ($kpiRows as $kpiRow) {
            $sheet->fromArray($kpiRow, null, 'A' . $row);
            $row++;
        }

        $sheet->getStyle('A' . $headerRow . ':B' . ($row - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
        ]);
        $sheet->getStyle('A' . $headerRow . ':B' . $headerRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0A2D29']],
        ]);

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setWidth($col === 'A' ? 28 : 18);
        }
    }

    private function buildDetailSheet($sheet): void
    {
        $headers = [
            'S.No.', 'ID', 'Position', 'Client', 'Lead Recruiter', 'Month', 'Status',
            'Priority', 'Job Type', 'Source', 'Country', 'Bill Rate', 'Deadline', 'Candidates', 'Concerns / Notes',
        ];
        $colCount = count($headers);

        $sheet->mergeCells('A1:' . Coordinate::stringFromColumnIndex($colCount) . '1');
        $sheet->setCellValue('A1', 'RADiiX INFINITEii — Position Detail Export');
        $sheet->getStyle('A1:' . Coordinate::stringFromColumnIndex($colCount) . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'F1CD86']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0A2D29']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        foreach ($headers as $i => $header) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '3', $header);
            $sheet->getStyle($col . '3')->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F3D38']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $sheet->getColumnDimension($col)->setWidth(match ($i) {
                0 => 6, 2 => 28, 3 => 18, 14 => 36, default => 14,
            });
        }
        $sheet->getRowDimension(3)->setRowHeight(28);

        $row = 4;
        $index = 1;
        foreach ($this->positions as $position) {
            $data = [
                $index++,
                $position['id'] ?? '',
                $position['position'] ?? '',
                $position['client'] ?? '',
                $position['recruiter'] ?? '',
                $position['month'] ?? '',
                $position['status'] ?? '',
                $position['priority'] ?? '',
                $position['job_type'] ?? '',
                $position['source'] ?? '',
                $position['country'] ?? '',
                $position['bill_rate'] ?? '',
                $position['deadline'] ?? '',
                $position['candidate_count'] ?? 0,
                isset($position['concerns']) ? implode(' | ', $position['concerns']) : ($position['detail'] ?? ''),
            ];

            foreach ($data as $i => $value) {
                $col = Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue($col . $row, $value);
            }

            $fill = $row % 2 === 0 ? 'F6FAF9' : 'FFFFFF';
            $sheet->getStyle('A' . $row . ':' . Coordinate::stringFromColumnIndex($colCount) . $row)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
            ]);

            $row++;
        }

        $sheet->freezePane('A4');
    }
}
