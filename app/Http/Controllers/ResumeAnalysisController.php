<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ResumeAnalysisController extends Controller
{
    public function index()
    {
        return view('resume.analysis');
    }

    public function analyze(Request $request)
    {
        $validated = $request->validate([
            'job_description' => ['required', 'string', 'min:20'],
            'resume' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $jobDescription = $validated['job_description'];
        $resumeFile = $request->file('resume');

        $pdfContents = @file_get_contents($resumeFile->getRealPath());

        if ($pdfContents === false) {
            return back()
                ->withInput()
                ->with('error', 'Could not read the uploaded resume file.');
        }

        $encodedPdf = base64_encode($pdfContents);

        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            return back()
                ->withInput()
                ->with('error', 'Gemini API key is not configured. Please set GEMINI_API_KEY in your environment.');
        }

        $model = 'gemini-2.5-flash';
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        try {
            // Main analysis - force a clean, non-markdown layout
            $analysisPrompt = "You are an expert technical recruiter working for a staffing company.\n"
                . "You will receive a job description (JD) and a candidate resume (PDF content).\n"
                . "Compare them and then respond in EXACTLY this plain-text structure, with NO markdown characters at all (do NOT use #, *, -, _, or ---):\n\n"
                . "Analysis result\n"
                . "<overall_match_percentage>%\n"
                . "<2-3 sentence summary explaining the overall fit>\n\n"
                . "Strengths\n"
                . "<each strength on its own line, no bullets or numbering>\n\n"
                . "Gaps\n"
                . "<each gap or concern on its own line, no bullets or numbering>\n\n"
                . "Overall recommendation\n"
                . "<one of: Strong fit | Good fit | Borderline | Not recommended>\n\n"
                . "Rules:\n"
                . "- Replace <overall_match_percentage> with an integer between 0 and 100 (do include the % sign).\n"
                . "- Replace the angle-bracket placeholders with real content.\n"
                . "- Do not add any extra sections or headings.\n"
                . "- Do not use markdown syntax anywhere.\n"
                . "- Keep the language concise and recruiter-friendly.\n";

            $analysisResponse = Http::timeout(60)->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $analysisPrompt],
                            ['text' => "Job Description:\n" . $jobDescription],
                            [
                                'inlineData' => [
                                    'mimeType' => 'application/pdf',
                                    'data' => $encodedPdf,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                ],
            ]);

            if ($analysisResponse->failed()) {
                $errorMessage = data_get($analysisResponse->json(), 'error.message') ?: $analysisResponse->body();
                return back()
                    ->withInput()
                    ->with('error', 'Gemini analysis request failed: ' . $errorMessage);
            }

            $analysisText = $this->extractTextFromGeminiResponse($analysisResponse->json());

            // Screening questions + answers - also plain text, no markdown
            $qaPrompt = "Using the same job description and candidate resume, generate 10 high-quality screening questions "
                . "that a recruiter can ask on a first screening call.\n"
                . "For each question, also provide a short ideal/expected answer so the recruiter knows what to listen for.\n"
                . "Focus on validating key skills from the JD, depth of experience, communication, stability, motivation, and any risk areas.\n\n"
                . "Respond ONLY in this plain-text format (no markdown, no *, no #, no bullets):\n\n"
                . "1. Question: <the question>\n"
                . "   Ideal answer: <what a good answer would roughly look like>\n\n"
                . "2. Question: <the question>\n"
                . "   Ideal answer: <what a good answer would roughly look like>\n\n"
                . "3. Question: ... and so on up to 10.\n\n"
                . "Do not add any headings or extra commentary before or after the list.";

            $qaResponse = Http::timeout(60)->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $qaPrompt],
                            ['text' => "Job Description:\n" . $jobDescription],
                            [
                                'inlineData' => [
                                    'mimeType' => 'application/pdf',
                                    'data' => $encodedPdf,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.5,
                ],
            ]);

            if ($qaResponse->failed()) {
                $errorMessage = data_get($qaResponse->json(), 'error.message') ?: $qaResponse->body();
                return back()
                    ->withInput()
                    ->with([
                        'error' => 'Gemini screening question request failed: ' . $errorMessage,
                        'resume_analysis' => $analysisText,
                    ]);
            }

            $qaText = $this->extractTextFromGeminiResponse($qaResponse->json());

            return view('resume.analysis', [
                'jobDescription' => $jobDescription,
                'resumeAnalysis' => $analysisText,
                'screeningQa' => $qaText,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Unexpected error while contacting Gemini: ' . $e->getMessage());
        }
    }

    /**
     * Extract plain text from a Gemini generateContent response.
     */
    protected function extractTextFromGeminiResponse(array $data): string
    {
        if (
            !isset($data['candidates'][0]['content']['parts'])
            || !is_array($data['candidates'][0]['content']['parts'])
        ) {
            return 'No analysis text returned from Gemini.';
        }

        $text = '';

        foreach ($data['candidates'][0]['content']['parts'] as $part) {
            if (isset($part['text'])) {
                $text .= $part['text'] . "\n";
            }
        }

        return trim($text);
    }
}

