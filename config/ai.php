<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "ollama", "gemini"
    |
    */
    'default' => env('AI_PROVIDER', 'gemini'),

    'providers' => [

        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
            'model' => env('OLLAMA_MODEL', 'qwen2.5:3b'),
            'fast_model' => env('OLLAMA_FAST_MODEL', env('OLLAMA_MODEL', 'qwen2.5:3b')),
            'timeout' => (int) env('OLLAMA_TIMEOUT', 180),
        ],

        'gemini' => [
            'key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
            'timeout' => (int) env('GEMINI_TIMEOUT', 90),
        ],

    ],

];
