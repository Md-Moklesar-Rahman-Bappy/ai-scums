<?php

declare(strict_types=1);

/**
 * AI Assistant configuration.
 *
 * Controls provider selection and tuning. The active provider is chosen via
 * AI_PROVIDER and may be overridden per-request. Each provider reads its key
 * and model from its own sub-array.
 */
return [
    /*
     * Active provider: openai | claude | gemini | local
     */
    'provider' => env('AI_PROVIDER', 'openai'),

    /*
     * Global tuning applied to every provider unless overridden.
     */
    'temperature' => env('AI_TEMPERATURE', 0.2),
    'max_tokens' => env('AI_MAX_TOKENS', 1000),

    'openai' => [
        'key' => env('AI_OPENAI_KEY'),
        'model' => env('AI_OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'claude' => [
        'key' => env('AI_CLAUDE_KEY'),
        'model' => env('AI_CLAUDE_MODEL', 'claude-3-5-sonnet-20241022'),
    ],

    'gemini' => [
        'key' => env('AI_GEMINI_KEY'),
        'model' => env('AI_GEMINI_MODEL', 'gemini-1.5-flash'),
    ],

    'local' => [
        'key' => env('AI_LOCAL_KEY'),
        'endpoint' => env('AI_LOCAL_ENDPOINT', 'http://localhost:11434/v1/chat/completions'),
        'model' => env('AI_LOCAL_MODEL', 'local-model'),
    ],
];
