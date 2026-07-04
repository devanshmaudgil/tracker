<?php

namespace App\Services\Report;

use App\Models\TrackerInfo;
use App\Models\TrackerCandidate;
use App\Models\CandidatePipelineStatus;
use Carbon\Carbon;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Element\Header;
use PhpOffice\PhpWord\Style\Image as ImageStyle;
use PhpOffice\PhpWord\Style\Tab;

/**
 * Generates the client-facing candidate submission report (.docx).
 *
 * Layout, fonts and colours are modelled on the approved RADiiX template:
 *   - Arial throughout, dark-green (#0F2E2A) + gold (#C9A36A) accents.
 *   - Full brand banner in the FIRST-page header only.
 *   - Compact logo in the header of every subsequent page.
 *   - Faint favicon watermark behind the content on every page.
 */
class CandidateReportService
{
    // Brand palette (no leading #)
    private string $green   = '0F2E2A';
    private string $gold    = 'C9A36A';
    private string $text    = '222222';
    private string $muted   = '777777';
    private string $muted2  = '555555';
    private string $labelBg = 'F4F1EC';
    private string $border  = 'D8D2C4';
    private string $white   = 'FFFFFF';
    private string $doneBg  = 'E4EEE8';
    private string $doneTxt = '256B52';

    // Usable body width (Letter 12240 twips − 1440 − 1440)
    private int $bodyWidth = 9360;

    public function generate(
        TrackerInfo $trackerInfo,
        TrackerCandidate $trackerCandidate,
        array $options
    ): \Symfony\Component\HttpFoundation\Response {

        // PHPWord does not escape XML special chars by default; without this any
        // '&', '<' or '>' in the content corrupts the document.
        Settings::setOutputEscapingEnabled(true);

        $phpWord = new PhpWord();
        $phpWord->getSettings()->setUpdateFields(true);
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);
        $phpWord->setDefaultFontColor($this->text);

        $this->registerStyles($phpWord);

        $section = $phpWord->addSection([
            'pageSizeW'    => 12240,
            'pageSizeH'    => 15840,
            'marginTop'    => 360,   // small top margin so the page-1 banner sits near the very top
            'marginBottom' => 1080,
            'marginLeft'   => 1440,
            'marginRight'  => 1440,
            'headerHeight' => 288,
            'footerHeight' => 432,
        ]);

        $candidate   = $trackerCandidate->candidate;
        $pipeline    = $trackerCandidate->pipelineStatus;
        $companyName = $options['company_name'] ?? 'RADiiX INFINITEii';

        // ── Headers / footers / watermark (first page vs. rest) ──────────────
        $this->buildPageFurniture($section, $trackerInfo, $candidate, $options);

        // ── First-page brand banner (in the BODY so it shows full-colour, not
        //     dimmed like header content; full text-column width, flush to top).
        $banner = $this->validImage($options['banner_path'] ?? null);
        if ($banner) {
            $section->addImage($banner, [
                'width'         => 466,   // ≈ full 6.5" text-column width
                'height'        => 46,    // banner is ~10:1
                'wrappingStyle' => ImageStyle::WRAPPING_STYLE_INLINE,
                'alignment'     => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]);
            $section->addTextBreak(1);
        }

        // ── Title block ──────────────────────────────────────────────────────
        $section->addText('CANDIDATE SUBMISSION REPORT', 'fTitle', 'pTitle');
        $section->addText($trackerInfo->position ?? 'Position', 'fPos', 'pPos');

        $prep = $section->addTextRun('pPrep');
        $prep->addText('Prepared for: ', 'fPrep');
        $prep->addText($trackerInfo->client->client ?? 'Client', 'fClient');

        $section->addText(
            Carbon::now()->format('d M Y') . '  •  Confidential',
            'fMeta', 'pMeta'
        );

        // ── 01 Candidate overview (summary / strengths / JD fit) ─────────────
        $summary      = trim((string) ($options['candidate_summary']       ?? ''));
        $strongPoints = trim((string) ($options['candidate_strong_points'] ?? ''));
        $jdSkills     = trim((string) ($options['candidate_jd_skills']     ?? ''));

        if ($summary !== '' || $strongPoints !== '' || $jdSkills !== '') {
            $this->sectionTitle($section, '01', 'CANDIDATE OVERVIEW');

            if ($summary !== '') {
                $section->addText('About the Candidate', 'fOvHead', ['spaceAfter' => 60]);
                foreach ($this->splitLines($summary) as $line) {
                    $section->addText($line, 'fValue', 'pBody');
                }
            }

            if ($strongPoints !== '') {
                $section->addText('Strong Points', 'fOvHead', ['spaceBefore' => 160, 'spaceAfter' => 60]);
                foreach ($this->splitLines($strongPoints) as $line) {
                    $section->addText($line, 'fValue', 'pBody');
                }
            }

            if ($jdSkills !== '') {
                $section->addText('Must-Have & Desired Skills vs. Job Description', 'fOvHead', ['spaceBefore' => 160, 'spaceAfter' => 60]);
                foreach ($this->splitLines($jdSkills) as $line) {
                    $section->addText($line, 'fValue', 'pBody');
                }
            }
        }

        // ── 02 Job requisition ────────────────────────────────────────────────
        $this->sectionTitle($section, '02', 'JOB REQUISITION DETAILS');
        $this->detailTable($section, [
            ['Client',                 $trackerInfo->client->client ?? 'N/A'],
            ['Position Title',         $trackerInfo->position ?? 'N/A'],
            ['Location',               $this->location($trackerInfo)],
            ['Job Type',               $trackerInfo->type_of_job ? ucfirst($trackerInfo->type_of_job) : 'N/A'],
            ['Bill Rate / Salary',     $trackerInfo->bill_rate_salary_range ?? 'N/A'],
            ['Priority',               $trackerInfo->priority ?? 'N/A'],
            ['Country of Fulfillment', $trackerInfo->cf ?? 'N/A'],
            ['Date of Demand Raised',  $trackerInfo->prd ? $trackerInfo->prd->format('d M Y') : 'N/A'],
            ['Submission Deadline',    $trackerInfo->submission_deadline ? $trackerInfo->submission_deadline->format('d M Y') : 'N/A'],
            ['Lead Recruiter',         $trackerInfo->leadRecruiter->username ?? 'N/A'],
            ['Candidate Source',       $trackerInfo->csi ?? 'N/A'],
        ]);

        // ── 03 Candidate profile ──────────────────────────────────────────────
        $this->sectionTitle($section, '03', 'CANDIDATE PROFILE');

        $loc = 'N/A';
        if ($candidate->location) {
            $loc = $candidate->location->city
                ? $candidate->location->city . ', ' . $candidate->location->region
                : $candidate->location->region;
        }

        $this->detailTable($section, [
            ['Full Name',          $candidate->full_name],
            ['Email Address',      $candidate->email],
            ['Phone',              $candidate->phone ?? 'N/A'],
            ['Location',           $loc],
            ['Work Authorization', $candidate->work_status ?? 'N/A'],
            ['Current Company',    $candidate->current_company ?? 'N/A'],
            ['Pay Rate',           $candidate->pay_rate ?? 'N/A'],
            ['Agency',             $candidate->agency_name ?? 'N/A'],
            ['Agency POC',         $candidate->agency_poc ?? 'N/A'],
            ['Agency POC Phone',   $candidate->agency_poc_phone ?? 'N/A'],
        ]);

        // ── 04 Pipeline journey ───────────────────────────────────────────────
        $this->sectionTitle($section, '04', 'RECRUITMENT PIPELINE JOURNEY');
        if ($pipeline) {
            $this->pipelineTable($section, $this->buildSteps($pipeline, $trackerCandidate));
        } else {
            $section->addText('No pipeline data recorded yet.', 'fMeta', 'pBody');
        }

        // ── 05 Skills assessment ──────────────────────────────────────────────
        $skills   = $options['skills'] ?? [];
        $overall  = trim((string) ($options['overall_recommendation'] ?? ''));
        if (!empty($skills) || $overall !== '') {
            $this->sectionTitle($section, '05', 'COMMUNICATION & SKILLS ASSESSMENT');
            $section->addText(
                'Recruiter evaluation based on interview — ' . Carbon::now()->format('d M Y'),
                'fSub', 'pSub'
            );
            if (!empty($skills)) {
                $this->skillsTable($section, $skills);
            }
            if ($overall !== '') {
                $section->addText('Overall Recommendation', 'fRecHead', ['spaceBefore' => 200, 'spaceAfter' => 60]);
                foreach ($this->splitLines($overall) as $line) {
                    $section->addText($line, 'fValue', 'pBody');
                }
            }
        }

        // ── 06 Recruiter notes ────────────────────────────────────────────────
        if (!empty($options['additional_notes'])) {
            $this->sectionTitle($section, '06', 'RECRUITER NOTES');
            foreach ($this->splitLines($options['additional_notes']) as $line) {
                $section->addText($line, 'fValue', 'pBody');
            }
        }

        // ── Signature block ────────────────────────────────────────────────────
        $section->addTextBreak(1);
        $sigName = $options['recruiter_name'] ?? $companyName;
        $section->addText($sigName, 'fSigName', 'pTightZero');
        $section->addText($companyName, 'fSigMuted', 'pTightZero');
        $section->addText(Carbon::now()->format('d M Y'), 'fSigMuted', 'pTight');

        // ── Write & stream ─────────────────────────────────────────────────────
        $safeName     = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $candidate->full_name);
        $safePosition = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $trackerInfo->position ?? 'Report');
        $filename     = "Submission_Report_{$safeName}_{$safePosition}_" . Carbon::now()->format('Ymd') . '.docx';

        $tempFile = tempnam(sys_get_temp_dir(), 'rpt_') . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    // ─── Style registration ────────────────────────────────────────────────────

    private function registerStyles(PhpWord $phpWord): void
    {
        $arial = fn(array $extra) => array_merge(['name' => 'Arial'], $extra);

        $phpWord->addFontStyle('fTitle',   $arial(['size' => 20, 'bold' => true, 'color' => $this->green]));
        $phpWord->addFontStyle('fPos',     $arial(['size' => 14, 'bold' => true, 'color' => $this->gold]));
        $phpWord->addFontStyle('fPrep',    $arial(['size' => 10, 'color' => $this->text]));
        $phpWord->addFontStyle('fClient',  $arial(['size' => 10, 'bold' => true, 'color' => $this->green]));
        $phpWord->addFontStyle('fMeta',    $arial(['size' => 9,  'italic' => true, 'color' => $this->muted]));
        $phpWord->addFontStyle('fSecNum',  $arial(['size' => 12, 'bold' => true, 'color' => $this->gold]));
        $phpWord->addFontStyle('fSecTtl',  $arial(['size' => 12, 'bold' => true, 'color' => $this->green]));
        $phpWord->addFontStyle('fSub',     $arial(['size' => 9.5, 'italic' => true, 'color' => $this->muted2]));
        $phpWord->addFontStyle('fLabel',   $arial(['size' => 10, 'bold' => true, 'color' => $this->green]));
        $phpWord->addFontStyle('fValue',   $arial(['size' => 10, 'color' => $this->text]));
        $phpWord->addFontStyle('fHead',    $arial(['size' => 9,  'bold' => true, 'color' => $this->white]));
        $phpWord->addFontStyle('fStage',   $arial(['size' => 9.5, 'color' => $this->text]));
        $phpWord->addFontStyle('fDone',    $arial(['size' => 9,  'bold' => true, 'color' => $this->doneTxt]));
        $phpWord->addFontStyle('fPend',    $arial(['size' => 9,  'color' => $this->muted]));
        $phpWord->addFontStyle('fSkill',   $arial(['size' => 9.5, 'bold' => true, 'color' => $this->green]));
        $phpWord->addFontStyle('fScore',   $arial(['size' => 9.5, 'bold' => true, 'color' => $this->gold]));
        $phpWord->addFontStyle('fNotes',   $arial(['size' => 9.5, 'color' => $this->text]));
        $phpWord->addFontStyle('fOvHead',  $arial(['size' => 10, 'bold' => true, 'color' => $this->gold]));
        $phpWord->addFontStyle('fRecHead', $arial(['size' => 10, 'bold' => true, 'color' => $this->green]));
        $phpWord->addFontStyle('fSigName', $arial(['size' => 10, 'bold' => true, 'color' => $this->green]));
        $phpWord->addFontStyle('fSigMuted',$arial(['size' => 9,  'color' => $this->muted]));
        $phpWord->addFontStyle('fFooter',  $arial(['size' => 8,  'color' => $this->muted]));

        $phpWord->addParagraphStyle('pTitle',     ['spaceBefore' => 200, 'spaceAfter' => 60]);
        $phpWord->addParagraphStyle('pPos',       ['spaceAfter' => 60]);
        $phpWord->addParagraphStyle('pPrep',      ['spaceAfter' => 40]);
        $phpWord->addParagraphStyle('pMeta',      ['spaceAfter' => 200]);
        $phpWord->addParagraphStyle('pSub',       ['spaceAfter' => 140]);
        $phpWord->addParagraphStyle('pBody',      ['spaceAfter' => 100]);
        $phpWord->addParagraphStyle('pTight',     ['spaceAfter' => 120]);
        $phpWord->addParagraphStyle('pTightZero', ['spaceAfter' => 0]);
        $phpWord->addParagraphStyle('pCellTight', ['spaceAfter' => 0]);
        $phpWord->addParagraphStyle('pCellCenter',['spaceAfter' => 0, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
    }

    // ─── Page furniture (headers, footer, watermark) ────────────────────────────

    private function buildPageFurniture($section, TrackerInfo $trackerInfo, $candidate, array $options): void
    {
        $logo      = $this->validImage($options['logo_path'] ?? null);
        $watermark = $this->validImage($options['watermark_path'] ?? null);

        // Watermark: keep the X mark's aspect ratio (749x418 ≈ 1.79) and centre
        // it behind the content. We anchor it CENTER relative to the MARGIN
        // (exactly how Word's own picture-watermark does it) — anchoring to the
        // page makes Word fall back to the emitted "margin-top:0" and pin it to
        // the top instead of the middle. The PNG is pre-faded to ~6% opacity.
        $watermarkStyle = [
            'width'            => 430,
            'height'           => 240,
            'wrappingStyle'    => ImageStyle::WRAPPING_STYLE_BEHIND,
            'positioning'      => ImageStyle::POSITION_ABSOLUTE,
            'posHorizontal'    => ImageStyle::POSITION_HORIZONTAL_CENTER,
            'posHorizontalRel' => ImageStyle::POSITION_RELATIVE_TO_MARGIN,
            'posVertical'      => ImageStyle::POSITION_VERTICAL_CENTER,
            'posVerticalRel'   => ImageStyle::POSITION_RELATIVE_TO_MARGIN,
        ];

        // FIRST page header: watermark only. The visible banner lives in the body
        // (so it renders full-colour rather than dimmed). Defining a first-page
        // header still activates titlePg, keeping the logo off page 1.
        $first = $section->addHeader(Header::FIRST);
        if ($watermark) {
            $first->addWatermark($watermark, $watermarkStyle);
        }

        // DEFAULT header (page 2+): compact logo (+ watermark behind).
        $default = $section->addHeader(Header::AUTO);
        if ($watermark) {
            $default->addWatermark($watermark, $watermarkStyle);
        }
        if ($logo) {
            $default->addImage($logo, [
                'width'         => 96,
                'height'        => 54,
                'wrappingStyle' => ImageStyle::WRAPPING_STYLE_INLINE,
                'alignment'     => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]);
        }

        // Footer on every page (first + default).
        foreach ([Header::FIRST, Header::AUTO] as $type) {
            $footer = $section->addFooter($type);
            $run = $footer->addTextRun([
                'borderTopSize'  => 4,
                'borderTopColor' => $this->border,
                'spaceBefore'    => 40,
                'tabs'           => [new Tab('right', 9026)],
            ]);
            $run->addText('Tracker #' . $trackerInfo->id . '  |  Candidate #' . $candidate->id, 'fFooter');
            $run->addText("\tPage ", 'fFooter');
            $run->addField('PAGE', [], [], 'fFooter');
        }
    }

    // ─── Section heading (numbered, gold rule under) ────────────────────────────

    private function sectionTitle($section, string $num, string $title): void
    {
        $run = $section->addTextRun([
            'spaceBefore'       => 360,
            'spaceAfter'        => 180,
            'borderBottomSize'  => 6,
            'borderBottomColor' => $this->gold,
        ]);
        $run->addText($num . '   ', 'fSecNum');
        $run->addText($title, 'fSecTtl');
    }

    // ─── Two-column detail table ────────────────────────────────────────────────

    private function detailTable($section, array $rows): void
    {
        $table = $section->addTable($this->tableStyle());
        foreach ($rows as [$label, $value]) {
            $table->addRow();
            $lc = $table->addCell(3120, ['bgColor' => $this->labelBg, 'valign' => 'center']);
            $lc->addText((string) $label, 'fLabel', 'pCellTight');
            $vc = $table->addCell(6240, ['valign' => 'center']);
            $vc->addText((string) ($value ?: 'N/A'), 'fValue', 'pCellTight');
        }
    }

    // ─── Pipeline table (stage / status / date) ─────────────────────────────────

    private function pipelineTable($section, array $steps): void
    {
        $table = $section->addTable($this->tableStyle());

        $table->addRow(null, ['tblHeader' => true]);
        $h1 = $table->addCell(4560, ['bgColor' => $this->green, 'valign' => 'center']);
        $h1->addText('PIPELINE STAGE', 'fHead', 'pCellTight');
        $h2 = $table->addCell(1800, ['bgColor' => $this->green, 'valign' => 'center']);
        $h2->addText('STATUS', 'fHead', 'pCellCenter');
        $h3 = $table->addCell(3000, ['bgColor' => $this->green, 'valign' => 'center']);
        $h3->addText('DATE / DETAIL', 'fHead', 'pCellTight');

        foreach ($steps as $step) {
            $table->addRow();

            $c1 = $table->addCell(4560, ['valign' => 'center']);
            $c1->addText($step['title'], 'fStage', 'pCellTight');

            if ($step['completed']) {
                $c2 = $table->addCell(1800, ['bgColor' => $this->doneBg, 'valign' => 'center']);
                $c2->addText('Complete', 'fDone', 'pCellCenter');
            } else {
                $c2 = $table->addCell(1800, ['valign' => 'center']);
                $c2->addText('Pending', 'fPend', 'pCellCenter');
            }

            $c3 = $table->addCell(3000, ['valign' => 'center']);
            $detail = implode('  ', array_filter([$step['date'], $step['detail']]));
            $c3->addText($detail ?: 'N/A', 'fStage', 'pCellTight');
        }
    }

    // ─── Skills table (skill / score / notes) ───────────────────────────────────

    private function skillsTable($section, array $skills): void
    {
        $table = $section->addTable($this->tableStyle());
        foreach ($skills as $skill) {
            $label = $skill['label'] ?? ($skill[0] ?? '');
            $score = $skill['score'] ?? ($skill[1] ?? null);
            $notes = $skill['notes'] ?? ($skill[2] ?? '');

            $table->addRow();
            $c1 = $table->addCell(4200, ['valign' => 'center']);
            $c1->addText((string) $label, 'fSkill', 'pCellTight');

            $c2 = $table->addCell(2000, ['bgColor' => $this->labelBg, 'valign' => 'center']);
            $c2->addText($score ? "{$score} / 10" : '—', 'fScore', 'pCellCenter');

            $c3 = $table->addCell(3160, ['valign' => 'center']);
            $c3->addText($notes !== '' ? (string) $notes : '—', 'fNotes', 'pCellTight');
        }
    }

    // ─── Shared table style ─────────────────────────────────────────────────────

    private function tableStyle(): array
    {
        // Register cell paragraph styles once (idempotent: addParagraphStyle overwrites).
        return [
            'borderColor'      => $this->border,
            'borderSize'       => 1,
            'cellMarginTop'    => 90,
            'cellMarginBottom' => 90,
            'cellMarginLeft'   => 140,
            'cellMarginRight'  => 140,
            'width'            => $this->bodyWidth,
            'unit'             => 'dxa',
        ];
    }

    // ─── Pipeline step definitions ──────────────────────────────────────────────

    private function buildSteps(CandidatePipelineStatus $p, TrackerCandidate $tc): array
    {
        return [
            ['title' => 'Candidate Identified',          'completed' => true,
             'date'  => $tc->created_at?->format('d M Y'), 'detail' => null],
            ['title' => 'Resume Reviewed by Recruiter',  'completed' => $p->resume_reviewed_by_recruiter === 'Completed',
             'date'  => $p->resume_reviewed_date?->format('d M Y'), 'detail' => $p->resume_reviewed_by_recruiter],
            ['title' => 'Recruiter Screening Call',      'completed' => $p->recruiter_screening_call === 'Completed',
             'date'  => $p->recruiter_screening_call_date?->format('d M Y'), 'detail' => $p->recruiter_screening_call],
            ['title' => 'Candidate Shortlisted',         'completed' => (bool) $p->candidate_shortlisted,
             'date'  => null, 'detail' => $p->candidate_shortlisted ? 'Yes' : 'No'],
            ['title' => 'Resume Submitted to Client',    'completed' => $p->resume_submitted_to_client === 'Submitted',
             'date'  => null, 'detail' => $p->resume_submitted_to_client],
            ['title' => 'Internal Interview Prep',       'completed' => in_array($p->radix_internal_interview_prep, ['Completed','Not Required']),
             'date'  => $p->radix_internal_interview_prep_date?->format('d M Y'), 'detail' => $p->radix_internal_interview_prep],
            ['title' => 'Client Resume Review',          'completed' => $p->client_resume_review === 'Approved',
             'date'  => null, 'detail' => $p->client_resume_review],
            ['title' => 'Client Interview - Round 1',    'completed' => !empty($p->client_interview_round_1_date),
             'date'  => $p->client_interview_round_1_date?->format('d M Y'), 'detail' => null],
            ['title' => 'Client Interview - Round 2',    'completed' => !empty($p->client_interview_round_2_date),
             'date'  => $p->client_interview_round_2_date?->format('d M Y'), 'detail' => null],
            ['title' => 'Additional Interview Rounds',   'completed' => (bool) $p->additional_rounds,
             'date'  => null, 'detail' => $p->additional_rounds ? 'Yes' : 'No'],
            ['title' => 'Client Decision',               'completed' => !empty($p->client_decision),
             'date'  => $p->client_decision_date?->format('d M Y'), 'detail' => $p->client_decision],
            ['title' => 'Client Confirmation',           'completed' => (bool) $p->client_confirmation_received,
             'date'  => $p->client_confirmation_date?->format('d M Y'), 'detail' => null],
            ['title' => 'Offer Extended to Candidate',   'completed' => (bool) $p->offer_extended_to_candidate,
             'date'  => $p->offer_extended_date?->format('d M Y'), 'detail' => null],
            ['title' => 'Background Check',              'completed' => $p->background_check === 'Completed',
             'date'  => null, 'detail' => $p->background_check],
            ['title' => 'Candidate Project Start',       'completed' => !empty($p->candidate_project_start_date),
             'date'  => $p->candidate_project_start_date?->format('d M Y'), 'detail' => null],
            ['title' => 'Placement Completion',          'completed' => $p->final_status_placement_completion === 'Confirmed',
             'date'  => $p->placement_completion_date?->format('d M Y'), 'detail' => $p->final_status_placement_completion],
        ];
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function splitLines(string $text): array
    {
        $out = [];
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line !== '') {
                $out[] = $line;
            }
        }
        return $out ?: [''];
    }

    private function validImage(?string $path): ?string
    {
        return ($path && is_file($path) && @getimagesize($path) !== false) ? $path : null;
    }

    private function location(TrackerInfo $t): string
    {
        if (!$t->region) {
            return $t->country ?? 'N/A';
        }
        return $t->region->city
            ? $t->region->city . ', ' . $t->region->region
            : $t->region->region;
    }
}
