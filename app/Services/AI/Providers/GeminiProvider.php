<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Contracts\AI\AIProviderInterface;
use Illuminate\Support\Facades\Http;

/**
 * GeminiProvider.
 *
 * Implements the {@see AIProviderInterface} for Google's
 * Gemini generative language API.
 */
class GeminiProvider extends AbstractLLMProvider
{
    protected function configKey(): string
    {
        return 'ai.gemini';
    }

    public function getName(): string
    {
        return 'Gemini';
    }

    public function complete(string $prompt, array $options = []): string
    {
        return $this->chat([['role' => 'user', 'content' => $prompt]], $options)['content'];
    }

    public function chat(array $messages, array $options = []): array
    {
        $opts = $this->resolveOptions($options);

        $parts = collect($messages)
            ->where('role', '!=', 'system')
            ->pluck('content')
            ->implode("\n");

        $response = Http::timeout(60)
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.$opts['model'].':generateContent', [
                'contents' => [['parts' => [['text' => $parts]]]],
                'generationConfig' => [
                    'temperature' => $opts['temperature'],
                    'maxOutputTokens' => $opts['max_tokens'],
                ],
            ]);

        $data = $response->json();

        return [
            'content' => $data['candidates'][0]['content']['parts'][0]['text'] ?? '',
            'tokens' => (int) ($data['usageMetadata']['totalTokenCount'] ?? 0),
        ];
    }
}
