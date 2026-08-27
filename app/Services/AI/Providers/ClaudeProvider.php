<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Contracts\AI\AIProviderInterface;
use Illuminate\Support\Facades\Http;

/**
 * ClaudeProvider.
 *
 * Implements the {@see AIProviderInterface} for the
 * Anthropic Claude Messages API.
 */
class ClaudeProvider extends AbstractLLMProvider
{
    protected function configKey(): string
    {
        return 'ai.claude';
    }

    public function getName(): string
    {
        return 'Claude';
    }

    public function complete(string $prompt, array $options = []): string
    {
        return $this->chat([['role' => 'user', 'content' => $prompt]], $options)['content'];
    }

    public function chat(array $messages, array $options = []): array
    {
        $opts = $this->resolveOptions($options);

        // Claude expects a single concatenated user prompt for the Messages API.
        $userText = collect($messages)
            ->where('role', '!=', 'system')
            ->pluck('content')
            ->implode("\n");

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey(),
            'anthropic-version' => '2023-06-01',
        ])
            ->timeout(60)
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $opts['model'],
                'max_tokens' => $opts['max_tokens'],
                'messages' => [['role' => 'user', 'content' => $userText]],
            ]);

        $data = $response->json();

        return [
            'content' => $data['content'][0]['text'] ?? '',
            'tokens' => (int) ($data['usage']['input_tokens'] ?? 0)
                + (int) ($data['usage']['output_tokens'] ?? 0),
        ];
    }
}
