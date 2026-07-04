<?php

namespace App\Services\Ai;

use App\Contracts\AiClient;
use App\Exceptions\AiException;
use Illuminate\Support\Facades\Http;

class GeminiClient implements AiClient
{
    public function providerName(): string
    {
        return 'Gemini';
    }

    public function modelName(): string
    {
        return (string) config('ai.providers.gemini.model', 'gemini-2.5-flash');
    }

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey());
    }

    public function chat(array $messages, array $options = []): string
    {
        if (! $this->isAvailable()) {
            throw new AiException('Gemini API key is not configured.');
        }

        $parts = [];

        foreach ($messages as $message) {
            $role = $message['role'] ?? 'user';
            $content = $message['content'] ?? '';

            if ($role === 'system') {
                $parts[] = ['text' => "System instructions:\n" . $content];
                continue;
            }

            $prefix = $role === 'assistant' ? "Assistant:\n" : "User:\n";
            $parts[] = ['text' => $prefix . $content];
        }

        $model = $options['model'] ?? $this->modelName();
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey()}";

        $response = Http::timeout($this->timeout())->post($endpoint, [
            'contents' => [
                [
                    'parts' => $parts,
                ],
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.4,
            ],
        ]);

        if ($response->failed()) {
            throw new AiException(
                'Gemini request failed: ' . (data_get($response->json(), 'error.message') ?: $response->body())
            );
        }

        $text = $this->extractText($response->json());

        if ($text === '') {
            throw new AiException('Gemini returned an empty response.');
        }

        return $text;
    }

    private function extractText(array $data): string
    {
        if (
            ! isset($data['candidates'][0]['content']['parts'])
            || ! is_array($data['candidates'][0]['content']['parts'])
        ) {
            return '';
        }

        $text = '';

        foreach ($data['candidates'][0]['content']['parts'] as $part) {
            if (isset($part['text'])) {
                $text .= $part['text'] . "\n";
            }
        }

        return trim($text);
    }

    private function apiKey(): ?string
    {
        return config('ai.providers.gemini.key') ?: config('services.gemini.key');
    }

    private function timeout(): int
    {
        return (int) config('ai.providers.gemini.timeout', 90);
    }
}
