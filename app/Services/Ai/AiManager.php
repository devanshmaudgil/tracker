<?php

namespace App\Services\Ai;

use App\Contracts\AiClient;
use App\Exceptions\AiException;
use InvalidArgumentException;

class AiManager
{
    public function driver(?string $name = null): AiClient
    {
        $name = $name ?: (string) config('ai.default', 'gemini');

        return match ($name) {
            'ollama' => app(OllamaClient::class),
            'gemini' => app(GeminiClient::class),
            default => throw new InvalidArgumentException("Unsupported AI provider [{$name}]."),
        };
    }

    public function status(?string $name = null): array
    {
        $client = $this->driver($name);

        return [
            'provider' => $client->providerName(),
            'model' => $client->modelName(),
            'available' => $client->isAvailable(),
            'driver' => $name ?: (string) config('ai.default', 'gemini'),
        ];
    }

    public function ensureAvailable(?string $name = null): AiClient
    {
        $client = $this->driver($name);

        if (! $client->isAvailable()) {
            $provider = $client->providerName();

            throw new AiException(
                "{$provider} is not available. "
                . ($provider === 'Ollama'
                    ? 'Make sure Ollama is running locally (ollama serve) and the model is installed.'
                    : 'Please configure the API key in your environment file.')
            );
        }

        return $client;
    }
}
