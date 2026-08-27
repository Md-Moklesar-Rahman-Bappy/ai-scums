<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Contracts\AI\AIProviderInterface;
use App\Services\AI\Providers\ClaudeProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\LocalProvider;
use App\Services\AI\Providers\OpenAIProvider;
use InvalidArgumentException;

/**
 * AIProviderManager.
 *
 * Resolves the configured {@see AIProviderInterface} implementation. Provider
 * selection is driven by the AI_PROVIDER env var, enabling runtime switching
 * between OpenAI, Claude, Gemini and Local LLMs without code changes.
 */
class AIProviderManager
{
    /**
     * Map of provider name => resolvable class.
     *
     * @var array<string, class-string<AIProviderInterface>>
     */
    private array $providers = [
        'openai' => OpenAIProvider::class,
        'claude' => ClaudeProvider::class,
        'gemini' => GeminiProvider::class,
        'local' => LocalProvider::class,
    ];

    /**
     * Return the active provider instance.
     */
    public function driver(?string $name = null): AIProviderInterface
    {
        $name = $name ?: config('ai.provider', 'openai');

        if (! array_key_exists($name, $this->providers)) {
            throw new InvalidArgumentException("Unsupported AI provider: {$name}");
        }

        return app($this->providers[$name]);
    }

    /**
     * List available provider names (for UI/diagnostics).
     *
     * @return array<int, string>
     */
    public function available(): array
    {
        return array_keys($this->providers);
    }
}
