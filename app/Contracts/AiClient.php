<?php

namespace App\Contracts;

interface AiClient
{
    public function providerName(): string;

    public function modelName(): string;

    public function isAvailable(): bool;

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, array $options = []): string;
}
