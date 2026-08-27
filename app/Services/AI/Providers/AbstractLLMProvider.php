<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Contracts\AI\AIProviderInterface;

/**
 * AbstractLLMProvider.
 *
 * Shared HTTP/configuration helpers for concrete LLM providers. Reads API key,
 * model and tuning parameters from config and provides the default option
 * merge used by every provider.
 */
abstract class AbstractLLMProvider implements AIProviderInterface
{
    /**
     * Configuration key prefix for this provider (e.g. "ai.openai").
     */
    abstract protected function configKey(): string;

    /**
     * {@inheritDoc}
     */
    abstract public function getName(): string;

    /**
     * Merge caller options with sensible defaults from config.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function resolveOptions(array $options): array
    {
        return array_merge([
            'model' => config($this->configKey().'.model', 'default-model'),
            'temperature' => (float) config('ai.temperature', 0.2),
            'max_tokens' => (int) config('ai.max_tokens', 1000),
        ], $options);
    }

    /**
     * Read the API key for this provider from config.
     */
    protected function apiKey(): ?string
    {
        return config($this->configKey().'.key');
    }
}
