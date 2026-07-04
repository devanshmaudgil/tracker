<?php

namespace App\Services\Ai;

use App\Contracts\AiClient;
use App\Exceptions\AiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OllamaClient implements AiClient
{
    public function providerName(): string
    {
        return 'Ollama';
    }

    public function modelName(): string
    {
        return (string) config('ai.providers.ollama.model', 'qwen2.5:3b');
    }

    public function fastModelName(): string
    {
        return (string) config('ai.providers.ollama.fast_model', $this->modelName());
    }

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl() . '/api/tags');

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    public function chat(array $messages, array $options = []): string
    {
        $payload = [
            'model' => $options['model'] ?? (($options['fast'] ?? false) ? $this->fastModelName() : $this->modelName()),
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => $options['temperature'] ?? 0.4,
                'num_predict' => $options['num_predict'] ?? 360,
            ],
        ];

        try {
            $response = Http::connectTimeout(10)
                ->timeout($this->timeout())
                ->post($this->baseUrl() . '/api/chat', $payload);
        } catch (ConnectionException $e) {
            throw new AiException('Ollama request timed out. Increase OLLAMA_TIMEOUT or use a smaller model.');
        }

        if ($response->failed()) {
            throw new AiException(
                'Ollama request failed: ' . ($response->json('error') ?: $response->body())
            );
        }

        $text = trim((string) $response->json('message.content', ''));

        if ($text === '') {
            throw new AiException('Ollama returned an empty response.');
        }

        return $text;
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('ai.providers.ollama.base_url', 'http://127.0.0.1:11434'), '/');
    }

    private function timeout(): int
    {
        return max(30, (int) config('ai.providers.ollama.timeout', 180));
    }
}
