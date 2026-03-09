<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CandidateSearchController extends Controller
{
    public function index()
    {
        return view('candidates.search');
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'job_description' => ['required', 'string', 'min:20'],
        ]);

        $jobDescription = $validated['job_description'];

        $geminiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        $serpApiKey = config('services.serpapi.key') ?: env('SERPAPI_API_KEY');

        if (empty($geminiKey)) {
            return back()
                ->withInput()
                ->with('error', 'Gemini API key is not configured. Please set GEMINI_API_KEY in your environment.');
        }

        if (empty($serpApiKey)) {
            return back()
                ->withInput()
                ->with('error', 'SerpAPI key is not configured. Please set SERPAPI_API_KEY in your environment to search for candidates.');
        }

        $model = 'gemini-2.5-flash';
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$geminiKey}";

        try {
            // Use Gemini to extract a job title and 3–5 search keywords from the JD
            $extractPrompt = "You are a recruiter. Given the following job description, extract:\n"
                . "1) job_title: A short job title (e.g. 'Senior PHP Developer', 'Data Engineer').\n"
                . "2) keywords: Exactly 3 to 5 key skills or technologies as separate words or short phrases, suitable for a web search.\n\n"
                . "Respond in this exact JSON format only, no other text:\n"
                . '{"job_title":"...","keywords":["keyword1","keyword2","keyword3"]}' . "\n\n"
                . "Job description:\n" . $jobDescription;

            $extractResponse = Http::timeout(30)->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $extractPrompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($extractResponse->failed()) {
                $err = data_get($extractResponse->json(), 'error.message') ?: $extractResponse->body();
                return back()
                    ->withInput()
                    ->with('error', 'Failed to parse job description: ' . $err);
            }

            $extractText = $this->extractTextFromGeminiResponse($extractResponse->json());
            $parsed = json_decode($extractText, true);

            if (!$parsed || empty($parsed['job_title']) || empty($parsed['keywords'])) {
                return back()
                    ->withInput()
                    ->with('error', 'Could not extract job title and keywords from the job description. Please try a longer or clearer JD.');
            }

            $jobTitle = $parsed['job_title'];
            $keywords = is_array($parsed['keywords']) ? $parsed['keywords'] : array_slice(explode(' ', implode(' ', $parsed['keywords'])), 0, 5);
            $keywordString = implode(' ', array_slice($keywords, 0, 4));

            // Build a boolean search string the user can copy
            $booleanParts = [];
            foreach ($keywords as $kw) {
                $kw = trim($kw);
                if ($kw !== '') {
                    $booleanParts[] = '"' . $kw . '"';
                }
            }
            $booleanQuery = 'site:linkedin.com/in "' . $jobTitle . '"';
            if (!empty($booleanParts)) {
                $booleanQuery .= ' AND (' . implode(' OR ', $booleanParts) . ')';
            }

            // LinkedIn-only search (up to 50 profiles)
            $linkedInQuery = 'site:linkedin.com/in "' . $jobTitle . '" ' . $keywordString;
            $linkedInResults = $this->fetchSerpResults($serpApiKey, $linkedInQuery, 50);

            $allResults = [];
            foreach ($linkedInResults as $r) {
                $url = $r['link'] ?? '';
                if (!$url || !str_contains(strtolower($url), 'linkedin.com')) {
                    continue;
                }
                $r['source'] = 'LinkedIn';
                $allResults[] = $r;
            }

            // Deduplicate by link
            $seen = [];
            $uniqueResults = [];
            foreach ($allResults as $r) {
                $url = $r['link'] ?? '';
                if ($url && empty($seen[$url])) {
                    $seen[$url] = true;
                    $uniqueResults[] = $r;
                }
            }

            // Limit to at most 50 profiles
            $uniqueResults = array_slice($uniqueResults, 0, 50);

            // Ask Gemini to score how well each profile matches the JD (0–100)
            $scoredResults = $this->scoreProfilesWithGemini($endpoint, $jobDescription, $uniqueResults);
            if (!empty($scoredResults)) {
                $uniqueResults = $scoredResults;
            }

            return view('candidates.search', [
                'jobDescription' => $jobDescription,
                'jobTitle' => $jobTitle,
                'keywords' => $keywords,
                'booleanQuery' => $booleanQuery,
                'results' => $uniqueResults,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with('error', 'An error occurred while searching: ' . $e->getMessage());
        }
    }

    /**
     * Call SerpAPI Google engine and return organic results (title, link, snippet).
     */
    protected function fetchSerpResults(string $apiKey, string $query, int $num = 10): array
    {
        $response = Http::timeout(25)->get('https://serpapi.com/search.json', [
            'engine' => 'google',
            'q' => $query,
            'api_key' => $apiKey,
            'num' => $num,
        ]);

        if ($response->failed()) {
            return [];
        }

        $data = $response->json();
        $organic = $data['organic_results'] ?? [];

        return array_map(function ($item) {
            return [
                'title' => $item['title'] ?? '',
                'link' => $item['link'] ?? '',
                'snippet' => $item['snippet'] ?? '',
            ];
        }, $organic);
    }

    protected function extractTextFromGeminiResponse(array $data): string
    {
        if (
            !isset($data['candidates'][0]['content']['parts'])
            || !is_array($data['candidates'][0]['content']['parts'])
        ) {
            return '';
        }

        $text = '';
        foreach ($data['candidates'][0]['content']['parts'] as $part) {
            if (isset($part['text'])) {
                $text .= $part['text'];
            }
        }
        return trim($text);
    }

    /**
     * Use Gemini to assign a match percentage (0–100) to each profile
     * based on the given job description and the profile's title/snippet/link.
     *
     * @param  string  $endpoint
     * @param  string  $jobDescription
     * @param  array<int, array<string, mixed>>  $profiles
     * @return array<int, array<string, mixed>>
     */
    protected function scoreProfilesWithGemini(string $endpoint, string $jobDescription, array $profiles): array
    {
        if (empty($profiles)) {
            return [];
        }

        // Build a compact representation of profiles for the prompt
        $itemsForModel = [];
        foreach ($profiles as $index => $profile) {
            $itemsForModel[] = [
                'index' => $index,
                'title' => $profile['title'] ?? '',
                'snippet' => $profile['snippet'] ?? '',
                'link' => $profile['link'] ?? '',
            ];
        }

        $jsonProfiles = json_encode($itemsForModel);
        if ($jsonProfiles === false) {
            return $profiles;
        }

        $prompt = "You are assisting a recruiter.\n"
            . "You will receive a job description and a list of LinkedIn search results (candidate profiles).\n"
            . "For each profile, rate how well it matches the job description on a scale of 0 to 100.\n\n"
            . "Rules:\n"
            . "- Only consider information given in the profile title, snippet, and link.\n"
            . "- Focus on skills, tech stack, seniority/experience level, and role fit.\n"
            . "- Do NOT be overly strict; good but not perfect fits can still be 80–90.\n"
            . "- Return ONLY a JSON array, no extra text.\n"
            . "- Each item must be: {\"index\": <number>, \"match_percentage\": <integer between 0 and 100>}.\n\n"
            . "Job description:\n{$jobDescription}\n\n"
            . "Profiles (JSON):\n{$jsonProfiles}\n";

        try {
            $response = Http::timeout(60)->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->failed()) {
                return $profiles;
            }

            $raw = $this->extractTextFromGeminiResponse($response->json());
            $decoded = json_decode($raw, true);

            if (!is_array($decoded)) {
                return $profiles;
            }

            $scoresByIndex = [];
            foreach ($decoded as $item) {
                if (!isset($item['index'], $item['match_percentage'])) {
                    continue;
                }
                $idx = (int) $item['index'];
                $score = (int) $item['match_percentage'];
                if ($score < 0) {
                    $score = 0;
                } elseif ($score > 100) {
                    $score = 100;
                }
                $scoresByIndex[$idx] = $score;
            }

            // Attach scores back to profiles
            foreach ($profiles as $idx => &$profile) {
                if (isset($scoresByIndex[$idx])) {
                    $profile['match_percentage'] = $scoresByIndex[$idx];
                }
            }
            unset($profile);

            // Sort by match percentage (highest first)
            usort($profiles, function (array $a, array $b): int {
                $scoreA = (int) ($a['match_percentage'] ?? 0);
                $scoreB = (int) ($b['match_percentage'] ?? 0);
                return $scoreB <=> $scoreA;
            });

            return $profiles;
        } catch (\Throwable $e) {
            report($e);
            return $profiles;
        }
    }
}
