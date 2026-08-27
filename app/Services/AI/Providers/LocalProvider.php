<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Contracts\AI\AIProviderInterface;
use Illuminate\Support\Facades\Http;

/**
 * LocalProvider.
 *
 * Implements the {@see AIProviderInterface} for a self-hosted
 * OpenAI-compatible endpoint (Ollama, LM Studio, llama.cpp, etc.). Enables the
 * "Local LLM" option of the project with zero code changes to the assistant.
 */
class LocalProvider extends AbstractLLMProvider
{
    protected function configKey(): string
    {
        return 'ai.local';
    }

    public function getName(): string
    {
        return 'Local LLM';
    }

    public function complete(string $prompt, array $options = []): string
    {
        return $this->chat([['role' => 'user', 'content' => $prompt]], $options)['content'];
    }

    public function chat(array $messages, array $options = []): array
    {
        $opts = $this->resolveOptions($options);
        $endpoint = config('ai.local.endpoint', 'http://localhost:11434/v1/chat/completions');

        $response = Http::timeout(120)
            ->when($this->apiKey(), fn ($h) => $h->withToken($this->apiKey()))
            ->post($endpoint, [
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
