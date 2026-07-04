<?php

namespace App\Services\Resume;

use App\Exceptions\AiException;
use Illuminate\Support\Facades\Process;
use Smalot\PdfParser\Parser;

class PdfTextExtractor
{
    public function extractFromPath(string $path): string
    {
        if (! is_readable($path)) {
            throw new AiException('Could not read the uploaded resume file.');
        }

        $text = '';

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = trim((string) $pdf->getText());
        } catch (\Throwable) {
            $text = '';
        }

        if (mb_strlen($text) < 80) {
            $text = $this->extractWithPython($path);
        }

        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;
        $text = trim($text);

        if (mb_strlen($text) < 80) {
            throw new AiException(
                'Could not extract enough text from this PDF. Dice secured PDFs may need Python pypdf (pip install pypdf), or re-save the file as an unlocked PDF.'
            );
        }

        return $this->truncate($text, 5500);
    }

    private function extractWithPython(string $path): string
    {
        $script = base_path('scripts/extract_pdf_text.py');

        if (! is_file($script)) {
            return '';
        }

        $python = $this->pythonBinary();
        if ($python === null) {
            return '';
        }

        $result = Process::timeout(30)->run([
            $python,
            $script,
            $path,
        ]);

        if (! $result->successful()) {
            return '';
        }

        return trim($result->output());
    }

    private function pythonBinary(): ?string
    {
        foreach (['python', 'python3', 'py'] as $binary) {
            $check = Process::timeout(5)->run([$binary, '--version']);
            if ($check->successful()) {
                return $binary;
            }
        }

        return null;
    }

    private function truncate(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit) . "\n\n[Resume text truncated for analysis]";
    }
}
