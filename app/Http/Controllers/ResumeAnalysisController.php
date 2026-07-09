<?php

namespace App\Http\Controllers;

use App\Exceptions\AiException;
use App\Services\Ai\AiManager;
use App\Services\Resume\ResumeAnalysisProgress;
use App\Services\Resume\ResumeAnalysisService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ResumeAnalysisController extends Controller
{
    public function index(AiManager $ai)
    {
        return view('resume.analysis', [
            'aiStatus' => $ai->status('gemini'),
        ]);
    }

    public function progress(string $token)
    {
        if (! ResumeAnalysisProgress::isValidToken($token)) {
            return response()->json([
                'step' => 'invalid',
                'label' => 'Invalid analysis session.',
                'percent' => 0,
            ], 404);
        }

        $data = ResumeAnalysisProgress::read($token);

        return response()->json($data ?? [
            'step' => 'waiting',
            'label' => 'Starting analysis…',
            'percent' => 5,
        ]);
    }

    public function analyze(Request $request, ResumeAnalysisService $resumeAnalysis)
    {
        set_time_limit(300);

        $validated = $request->validate([
            'job_description' => ['required', 'string', 'min:20'],
            'resume' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'analysis_token' => ['nullable', 'uuid'],
        ]);

        if ($request->header('X-Progress-Stream') === '1') {
            return $this->analyzeAsStream($request, $validated, $resumeAnalysis);
        }

        $token = $validated['analysis_token'] ?? null;

        if ($token) {
            ResumeAnalysisProgress::report($token, 'upload', 'Receiving documents…', 8);
        }

        try {
            $result = $resumeAnalysis->analyze(
                $validated['job_description'],
                $request->file('resume')->getRealPath(),
                $token
            );

            if ($request->expectsJson()) {
                $html = view('resume._fit_report', [
                    'analysisSections' => $result['sections'],
                ])->render();

                if ($token) {
                    ResumeAnalysisProgress::clear($token);
                }

                return response()->json([
                    'html' => $html,
                    'sections' => $result['sections'],
                ]);
            }

            return view('resume.analysis', [
                'aiStatus' => app(AiManager::class)->status('gemini'),
                'jobDescription' => $validated['job_description'],
                'resumeAnalysis' => $result['analysis'],
                'analysisSections' => $result['sections'] ?? null,
            ]);
        } catch (AiException $e) {
            return $this->analysisErrorResponse($request, $token, 'The analysis engine is temporarily unavailable. Please try again in a few moments.', 503);
        } catch (Throwable $e) {
            report($e);

            return $this->analysisErrorResponse($request, $token, 'Unexpected error during analysis. Please try again.', 500);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function analyzeAsStream(
        Request $request,
        array $validated,
        ResumeAnalysisService $resumeAnalysis,
    ): StreamedResponse {
        $resumePath = $request->file('resume')->getRealPath();

        return response()->stream(function () use ($validated, $resumeAnalysis, $resumePath) {
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', '0');

            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            ob_implicit_flush(true);

            // Padding helps some PHP/SAPI stacks flush the stream immediately.
            echo str_repeat(' ', 2048) . "\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            $emit = function (array $payload): void {
                echo json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n";

                if (ob_get_level() > 0) {
                    ob_flush();
                }

                flush();
            };

            try {
                $emit([
                    'type' => 'progress',
                    'step' => 'upload',
                    'label' => 'Receiving documents…',
                    'percent' => 8,
                ]);

                $result = $resumeAnalysis->analyze(
                    $validated['job_description'],
                    $resumePath,
                    null,
                    function (string $step, string $label, int $percent) use ($emit): void {
                        $emit([
                            'type' => 'progress',
                            'step' => $step,
                            'label' => $label,
                            'percent' => $percent,
                        ]);
                    }
                );

                $html = view('resume._fit_report', [
                    'analysisSections' => $result['sections'],
                ])->render();

                $emit([
                    'type' => 'complete',
                    'html' => $html,
                    'sections' => $result['sections'],
                ]);
            } catch (AiException) {
                $emit([
                    'type' => 'error',
                    'message' => 'The analysis engine is temporarily unavailable. Please try again in a few moments.',
                ]);
            } catch (Throwable $e) {
                report($e);

                $emit([
                    'type' => 'error',
                    'message' => 'Unexpected error during analysis. Please try again.',
                ]);
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function analysisErrorResponse(Request $request, ?string $token, string $message, int $status)
    {
        if ($token) {
            ResumeAnalysisProgress::clear($token);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()
            ->withInput()
            ->with('error', $message);
    }
}
