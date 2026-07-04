<?php

namespace App\Services\Resume;

use App\Services\Ai\AiManager;

class ResumeAnalysisService
{
    public function __construct(
        private AiManager $ai,
        private PdfTextExtractor $pdfTextExtractor,
        private ResumeMatchScorer $matchScorer,
    ) {
    }

    /**
     * @param  callable(string, string, int): void|null  $onProgress
     */
    public function analyze(
        string $jobDescription,
        string $resumePath,
        ?string $progressToken = null,
        ?callable $onProgress = null,
    ): array {
        $client = $this->ai->ensureAvailable();

        $this->reportProgress($progressToken, $onProgress, 'extract', 'Reading resume document…', 18);
        $resumeText = $this->pdfTextExtractor->extractFromPath($resumePath);

        $this->reportProgress($progressToken, $onProgress, 'score', 'Evaluating requirements match…', 38);
        $scorecard = $this->matchScorer->evaluate($jobDescription, $resumeText);

        $this->reportProgress($progressToken, $onProgress, 'narrative', 'Generating match assessment…', 52);
        $narrative = $client->chat([
            [
                'role' => 'system',
                'content' => $this->narrativeSystemPrompt(),
            ],
            [
                'role' => 'user',
                'content' => $this->buildNarrativePrompt($jobDescription, $resumeText, $scorecard),
            ],
        ], [
            'temperature' => 0.15,
            'num_predict' => 300,
            'fast' => true,
        ]);

        $this->reportProgress($progressToken, $onProgress, 'finalize', 'Preparing your report…', 92);
        $sections = $this->buildAnalysisSections($scorecard, $narrative);

        $this->reportProgress($progressToken, $onProgress, 'done', 'Report ready', 100);

        return [
            'provider' => $client->providerName(),
            'model' => $client->modelName(),
            'analysis' => $this->sectionsToPlainText($sections),
            'sections' => $sections,
            'scorecard' => $scorecard,
        ];
    }

    private function reportProgress(
        ?string $progressToken,
        ?callable $onProgress,
        string $step,
        string $label,
        int $percent,
    ): void {
        if ($onProgress) {
            $onProgress($step, $label, $percent);
        }

        if ($progressToken) {
            ResumeAnalysisProgress::report($progressToken, $step, $label, $percent);
        }
    }

    /**
     * @param  array<string, mixed>  $scorecard
     */
    private function buildNarrativePrompt(string $jobDescription, string $resumeText, array $scorecard): string
    {
        $missingMust = $scorecard['missing_must_haves'] !== []
            ? implode('; ', $scorecard['missing_must_haves'])
            : 'None';
        $missingSkills = $scorecard['missing_skills'] !== []
            ? implode('; ', array_slice($scorecard['missing_skills'], 0, 6))
            : 'None';
        $matchedSkills = $scorecard['matched_skills'] !== []
            ? implode('; ', array_slice($scorecard['matched_skills'], 0, 6))
            : 'None';

        return "Job Description:\n{$jobDescription}\n\n"
            . "Candidate Resume:\n{$resumeText}\n\n"
            . "Scoring facts (do not change these):\n"
            . "- Match percentage: {$scorecard['match_percentage']}%\n"
            . "- Recommendation: {$scorecard['recommendation']}\n"
            . "- Must-haves met: {$scorecard['must_haves_met']} of {$scorecard['must_haves_total']}\n"
            . "- Skills met: {$scorecard['skills_met']} of {$scorecard['skills_total']}\n"
            . "- Missing must-haves: {$missingMust}\n"
            . "- Missing skills: {$missingSkills}\n"
            . "- Matched skills: {$matchedSkills}\n\n"
            . "Write only the narrative sections requested in the system prompt.";
    }

    private function narrativeSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a strict technical recruiter. Write concise recruiter notes only.

Return EXACTLY this plain-text format (no markdown):

Summary
<1-2 sentences on one line after the heading, no colon after the word Summary>

Strengths
<max 3 short points, one per line directly under this heading, no colon after the word Strengths>

Gaps
<max 3 short points, one per line directly under this heading, no colon after the word Gaps>

Rules:
- Plain text only. Never use markdown symbols like **, *, #, or ---.
- Never put a colon on its own line after a section heading.
- Do NOT include match percentage or recommendation (added separately).
- Do NOT invent skills not present in the resume.
- Be conservative and evidence-based.
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $scorecard
     * @return array{
     *     match_percentage: int,
     *     must_haves_line: string,
     *     summary: string,
     *     strengths: array<int, string>,
     *     gaps: array<int, string>,
     *     recommendation: string
     * }
     */
    private function buildAnalysisSections(array $scorecard, string $narrative): array
    {
        $parsed = $this->parseNarrativeSections($narrative);

        $gaps = $this->mergeGaps(
            $parsed['gaps'],
            $scorecard['missing_must_haves'] ?? [],
            $scorecard['missing_skills'] ?? []
        );

        $mustLine = $scorecard['must_haves_total'] > 0
            ? "Must-haves met: {$scorecard['must_haves_met']} of {$scorecard['must_haves_total']}"
            : 'Must-haves met: not specified in JD';

        return [
            'match_percentage' => (int) $scorecard['match_percentage'],
            'must_haves_line' => $mustLine,
            'summary' => $parsed['summary'] !== ''
                ? $parsed['summary']
                : 'Limited overlap between the resume and this job description.',
            'strengths' => array_slice($parsed['strengths'], 0, 4),
            'gaps' => array_slice($gaps, 0, 6),
            'recommendation' => (string) $scorecard['recommendation'],
        ];
    }

    /**
     * @return array{summary: string, strengths: array<int, string>, gaps: array<int, string>}
     */
    private function parseNarrativeSections(string $narrative): array
    {
        $narrative = $this->stripMarkdown($narrative);
        $narrative = preg_replace('/^Analysis result.*$/mi', '', $narrative) ?? $narrative;
        $narrative = preg_replace('/^\d{1,3}%.*$/mi', '', $narrative) ?? $narrative;
        $narrative = preg_replace('/^Overall recommendation.*$/mi', '', $narrative) ?? $narrative;
        $narrative = trim($narrative);

        $summary = '';
        $strengths = [];
        $gaps = [];

        if (preg_match('/Summary\s*:?\s*(.*?)(?=Strengths\s*:?|$)/is', $narrative, $match)) {
            $summary = $this->normalizeContentLine($this->collapseParagraph(trim($match[1])));
        }

        if (preg_match('/Strengths\s*:?\s*(.*?)(?=Gaps\s*:?|$)/is', $narrative, $match)) {
            $strengths = $this->extractListItems($match[1]);
        }

        if (preg_match('/Gaps\s*:?\s*(.*?)$/is', $narrative, $match)) {
            $gaps = $this->extractListItems($match[1]);
        }

        if ($summary === '' && $strengths === [] && $gaps === [] && $narrative !== '') {
            $summary = $this->collapseParagraph($narrative);
        }

        return compact('summary', 'strengths', 'gaps');
    }

    private function stripMarkdown(string $text): string
    {
        $text = preg_replace('/\*\*(.+?)\*\*/s', '$1', $text) ?? $text;
        $text = preg_replace('/\*(.+?)\*/s', '$1', $text) ?? $text;
        $text = preg_replace('/^#{1,6}\s*/m', '', $text) ?? $text;
        $text = str_replace(['**', '__', '---'], '', $text);

        return trim($text);
    }

    private function collapseParagraph(string $text): string
    {
        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }

    private function normalizeContentLine(string $line): string
    {
        $line = trim($line);
        $line = preg_replace('/^:\s*/', '', $line) ?? $line;

        return trim($line);
    }

    /**
     * @return array<int, string>
     */
    private function extractListItems(string $section): array
    {
        $items = [];

        foreach (preg_split('/\r\n|\r|\n/', $section) ?: [] as $line) {
            $line = $this->normalizeContentLine($line);
            $line = preg_replace('/^[\-\*\x{2022}]+\s*/u', '', $line) ?? $line;
            $line = preg_replace('/^\d+[\.\)]\s*/', '', $line) ?? $line;
            $line = trim($line, " \t*:");
            $line = $this->normalizeContentLine($line);

            if ($line === '' || preg_match('/^(summary|strengths|gaps)\s*:?\s*$/i', $line)) {
                continue;
            }

            if (preg_match('/^[:\-\.\s]+$/', $line)) {
                continue;
            }

            if (mb_strlen($line) < 4) {
                continue;
            }

            $items[] = $line;
        }

        return array_values(array_unique($items));
    }

    /**
     * @param  array<int, string>  $aiGaps
     * @param  array<int, string>  $missingMustHaves
     * @param  array<int, string>  $missingSkills
     * @return array<int, string>
     */
    private function mergeGaps(array $aiGaps, array $missingMustHaves, array $missingSkills): array
    {
        $gaps = [];

        foreach ($missingMustHaves as $item) {
            $gaps[] = 'Missing must-have: ' . $item;
        }

        foreach (array_slice($missingSkills, 0, 4) as $item) {
            $gaps[] = 'Missing skill: ' . $item;
        }

        foreach ($aiGaps as $item) {
            if (! $this->gapAlreadyListed($item, $gaps)) {
                $gaps[] = $item;
            }
        }

        return array_values(array_unique($gaps));
    }

    /**
     * @param  array<int, string>  $existing
     */
    private function gapAlreadyListed(string $item, array $existing): bool
    {
        $needle = mb_strtolower($item);

        foreach ($existing as $gap) {
            similar_text(mb_strtolower($gap), $needle, $percent);
            if ($percent >= 70) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{
     *     match_percentage: int,
     *     must_haves_line: string,
     *     summary: string,
     *     strengths: array<int, string>,
     *     gaps: array<int, string>,
     *     recommendation: string
     * }  $sections
     */
    private function sectionsToPlainText(array $sections): string
    {
        $lines = [
            'Analysis result',
            $sections['match_percentage'] . '%',
            $sections['must_haves_line'],
            '',
            'Summary',
            $sections['summary'],
            '',
            'Strengths',
        ];

        foreach ($sections['strengths'] as $index => $strength) {
            $lines[] = ($index + 1) . '. ' . $strength;
        }

        $lines[] = '';
        $lines[] = 'Gaps';

        foreach ($sections['gaps'] as $index => $gap) {
            $lines[] = ($index + 1) . '. ' . $gap;
        }

        $lines[] = '';
        $lines[] = 'Overall recommendation';
        $lines[] = $sections['recommendation'];

        return implode("\n", $lines);
    }
}
