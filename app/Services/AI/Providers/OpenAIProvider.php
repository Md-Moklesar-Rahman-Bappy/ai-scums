<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Contracts\AI\AIProviderInterface;
use Illuminate\Support\Facades\Http;

/**
 * OpenAIProvider.
 *
 * Implements the {@see AIProviderInterface} for the OpenAI
 * Chat Completions API (supports GPT-4o, GPT-4o-mini, etc.).
 */
class OpenAIProvider extends AbstractLLMProvider
{
    protected function configKey(): string
    {
        return 'ai.openai';
    }

    public function getName(): string
    {
        return 'OpenAI';
    }

    public function complete(string $prompt, array $options = []): string
    {
        return $this->chat([['role' => 'user', 'content' => $prompt]], $options)['content'];
    }

    public function chat(array $messages, array $options = []): array
    {
        $opts = $this->resolveOptions($options);

        $response = Http::withToken($this->apiKey())
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $opts['model'],
                'messages' => $messages,
                'temperature' => $opts['temperature'],
                'max_tokens' => $opts['max_tokens'],
            ]);

        $data = $response->json();

        return [
            'content' => $data['choices'][0]['message']['content'] ?? '',
            'tokens' => (int) ($data['usage']['total_tokens'] ?? 0),
        ];
    }
}
