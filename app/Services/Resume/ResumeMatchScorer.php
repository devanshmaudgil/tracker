<?php

namespace App\Services\Resume;

class ResumeMatchScorer
{
    private const STOP_WORDS = [
        'with', 'using', 'years', 'year', 'experience', 'the', 'and', 'for', 'from',
        'that', 'this', 'have', 'has', 'must', 'plus', 'more', 'least', 'ability',
        'familiarity', 'knowledge', 'good', 'understanding', 'role', 'should',
    ];

    /**
     * @return array{
     *     match_percentage: int,
     *     recommendation: string,
     *     must_haves_total: int,
     *     must_haves_met: int,
     *     skills_total: int,
     *     skills_met: int,
     *     missing_must_haves: array<int, string>,
     *     matched_skills: array<int, string>,
     *     missing_skills: array<int, string>
     * }
     */
    public function evaluate(string $jobDescription, string $resumeText): array
    {
        $resume = mb_strtolower($resumeText);
        $mustHaves = $this->extractSectionItems($jobDescription, ['must haves', 'must have', 'requirements']);
        $skills = $this->extractSectionItems($jobDescription, ['skills', 'skill', 'technical skills']);

        if ($mustHaves === [] && $skills === []) {
            $mustHaves = $this->extractKeywordLines($jobDescription);
        }

        $mustHaveMatch = $this->matchItems($mustHaves, $resume);
        $skillMatch = $this->matchItems($skills, $resume);

        $mustTotal = count($mustHaves);
        $skillTotal = count($skills);

        $mustRatio = $mustTotal > 0 ? ($mustHaveMatch['met'] / $mustTotal) : null;
        $skillRatio = $skillTotal > 0 ? ($skillMatch['met'] / $skillTotal) : null;

        $weighted = 0.0;
        if ($mustRatio !== null && $skillRatio !== null) {
            $weighted = ($mustRatio * 0.65) + ($skillRatio * 0.35);
        } elseif ($mustRatio !== null) {
            $weighted = $mustRatio;
        } elseif ($skillRatio !== null) {
            $weighted = $skillRatio * 0.85;
        } else {
            $weighted = $this->fallbackOverlapScore($jobDescription, $resumeText);
        }

        $score = (int) round($weighted * 100);
        $transferable = $this->transferableScore($jobDescription, $resume);
        $score = (int) round(($score * 0.55) + ($transferable * 0.45));

        if ($mustTotal > 0 && $mustHaveMatch['met'] === 0) {
            $score = max($score, min((int) round($transferable * 0.75), 38));
        }

        $score = $this->applyDomainPenalty($score, $jobDescription, $resume);
        $score = $this->applyMustHaveCaps($score, $mustTotal, $mustHaveMatch['met'], $mustRatio);

        return [
            'match_percentage' => $score,
            'recommendation' => $this->recommendationForScore($score, $mustTotal, $mustHaveMatch['met']),
            'must_haves_total' => $mustTotal,
            'must_haves_met' => $mustHaveMatch['met'],
            'skills_total' => $skillTotal,
            'skills_met' => $skillMatch['met'],
            'missing_must_haves' => $mustHaveMatch['missing'],
            'matched_skills' => $skillMatch['matched'],
            'missing_skills' => $skillMatch['missing'],
        ];
    }

    /**
     * @param  array<int, string>  $labels
     * @return array<int, string>
     */
    private function extractSectionItems(string $jobDescription, array $labels): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $jobDescription) ?: [];
        $items = [];
        $inSection = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                if ($inSection && $items !== []) {
                    break;
                }
                continue;
            }

            $lower = mb_strtolower($trimmed);
            $isHeader = false;
            foreach ($labels as $label) {
                if ($lower === $label || str_starts_with($lower, $label . ':')) {
                    $inSection = true;
                    $isHeader = true;
                    $afterColon = trim((string) preg_replace('/^' . preg_quote($label, '/') . ':?\s*/i', '', $trimmed));
                    if ($afterColon !== '') {
                        $items[] = $afterColon;
                    }
                    break;
                }
            }

            if ($isHeader) {
                continue;
            }

            if ($inSection) {
                $items[] = preg_replace('/^[\-\*\x{2022}]\s*/u', '', $trimmed) ?? $trimmed;
            }
        }

        return array_values(array_filter(array_map('trim', $items)));
    }

    /**
     * @return array<int, string>
     */
    private function extractKeywordLines(string $jobDescription): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $jobDescription) ?: [];
        $items = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }
            if (preg_match('/\b(must|required|mandatory|5\+|years)\b/i', $trimmed)) {
                $items[] = $trimmed;
            }
        }

        return $items;
    }

    /**
     * @param  array<int, string>  $items
     * @return array{met: int, matched: array<int, string>, missing: array<int, string>}
     */
    private function matchItems(array $items, string $resumeLower): array
    {
        $matched = [];
        $missing = [];

        foreach ($items as $item) {
            if ($this->itemMatchesResume($item, $resumeLower)) {
                $matched[] = $item;
            } else {
                $missing[] = $item;
            }
        }

        return [
            'met' => count($matched),
            'matched' => $matched,
            'missing' => $missing,
        ];
    }

    private function itemMatchesResume(string $item, string $resumeLower): bool
    {
        $itemLower = mb_strtolower($item);
        $keywords = $this->significantTokens($itemLower);

        if ($keywords === []) {
            return false;
        }

        $hits = 0;
        foreach ($keywords as $keyword) {
            if ($this->resumeContainsKeyword($resumeLower, $keyword)) {
                $hits++;
            }
        }

        if (str_contains($itemLower, 'servicenow')) {
            return str_contains($resumeLower, 'servicenow')
                && $hits >= 2;
        }

        $requiredHits = $this->requiredKeywordHits($itemLower, count($keywords));

        return $hits >= $requiredHits;
    }

    private function requiredKeywordHits(string $itemLower, int $keywordCount): int
    {
        if ($this->isSoftSkillLine($itemLower)) {
            return min($keywordCount, 2);
        }

        if ($keywordCount <= 3) {
            return max(1, $keywordCount - 1);
        }

        return max(2, (int) ceil($keywordCount * 0.4));
    }

    private function isSoftSkillLine(string $itemLower): bool
    {
        return (bool) preg_match('/^(ability|familiarity|knowledge|understanding|experience with)\b/', $itemLower);
    }

    /**
     * @return array<int, string>
     */
    private function significantTokens(string $text): array
    {
        $text = preg_replace('/[^a-z0-9\+\#\.\s]/', ' ', $text) ?? $text;
        $parts = preg_split('/\s+/', $text) ?: [];
        $tokens = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || strlen($part) < 3) {
                continue;
            }
            if (in_array($part, self::STOP_WORDS, true)) {
                continue;
            }
            if (is_numeric($part)) {
                continue;
            }
            $tokens[] = $part;
        }

        return array_values(array_unique($tokens));
    }

    private function resumeContainsKeyword(string $resumeLower, string $keyword): bool
    {
        if (str_contains($resumeLower, $keyword)) {
            return true;
        }

        $stem = rtrim($keyword, 's');
        if ($stem !== $keyword && strlen($stem) >= 4 && str_contains($resumeLower, $stem)) {
            return true;
        }

        if ($keyword === 'analysts' && str_contains($resumeLower, 'analyst')) {
            return true;
        }

        if ($keyword === 'engineers' && str_contains($resumeLower, 'engineering')) {
            return true;
        }

        if ($keyword === 'cmdb' && str_contains($resumeLower, 'configuration management database')) {
            return true;
        }

        if ($keyword === 'itsm' && str_contains($resumeLower, 'it service management')) {
            return true;
        }

        if ($keyword === 'itom' && str_contains($resumeLower, 'it operations')) {
            return true;
        }

        return false;
    }

    private function transferableScore(string $jobDescription, string $resumeLower): int
    {
        $jd = mb_strtolower($jobDescription);
        $score = 0;

        $pairs = [
            ['business analyst', 14],
            ['business analysis', 14],
            ['requirements', 10],
            ['stakeholder', 6],
            ['process mapping', 6],
            ['workflow', 5],
            ['agile', 4],
            ['scrum', 4],
            ['itil', 4],
            ['data analysis', 4],
        ];

        foreach ($pairs as [$phrase, $points]) {
            if (str_contains($jd, $phrase) && str_contains($resumeLower, $phrase)) {
                $score += $points;
            }
        }

        return min(32, $score);
    }

    private function applyDomainPenalty(int $score, string $jobDescription, string $resumeLower): int
    {
        $jd = mb_strtolower($jobDescription);
        $platforms = ['servicenow', 'salesforce', 'sap', 'workday', 'dynamics 365'];

        foreach ($platforms as $platform) {
            if (! str_contains($jd, $platform)) {
                continue;
            }

            if (! str_contains($resumeLower, $platform)) {
                return min($score, 40);
            }
        }

        return $score;
    }

    private function fallbackOverlapScore(string $jobDescription, string $resumeText): float
    {
        $jdTokens = $this->significantTokens(mb_strtolower($jobDescription));
        $resume = mb_strtolower($resumeText);

        if ($jdTokens === []) {
            return 0.2;
        }

        $hits = 0;
        foreach ($jdTokens as $token) {
            if ($this->resumeContainsKeyword($resume, $token)) {
                $hits++;
            }
        }

        return min(0.7, $hits / max(8, count($jdTokens)));
    }

    private function applyMustHaveCaps(int $score, int $mustTotal, int $mustMet, ?float $mustRatio): int
    {
        if ($mustTotal === 0) {
            return max(0, min(100, $score));
        }

        if ($mustMet === 0) {
            return min($score, 42);
        }

        if ($mustRatio !== null && $mustRatio < 0.5) {
            $score = min($score, 55);
        }

        if ($mustMet < $mustTotal) {
            $score = min($score, 65);
        }

        return max(0, min(100, $score));
    }

    private function recommendationForScore(int $score, int $mustTotal, int $mustMet): string
    {
        if ($mustTotal > 0 && $mustMet === 0 && $score < 45) {
            return 'Not recommended';
        }

        if ($mustTotal > 0 && $mustMet === 0) {
            return 'Borderline';
        }

        return match (true) {
            $score >= 80 => 'Strong match',
            $score >= 65 => 'Good match',
            $score >= 45 => 'Borderline',
            default => 'Not recommended',
        };
    }
}
