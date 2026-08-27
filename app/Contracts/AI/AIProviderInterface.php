<?php

declare(strict_types=1);

namespace App\Contracts\AI;

/**
 * AIProviderInterface.
 *
 * Abstraction over an LLM backend. All concrete providers (OpenAI, Claude,
 * Gemini, Local) implement this interface so the assistant can be swapped
 * without touching business logic. This satisfies the "provider abstraction"
 * requirement of the project.
 */
interface AIProviderInterface
{
    /**
     * Human-readable provider name.
     */
    public function getName(): string;

    /**
     * Send a single prompt and return the generated text.
     *
     * @param  array<string, mixed>  $options  Provider-specific options (model, temperature, max_tokens).
     */
    public function complete(string $prompt, array $options = []): string;

    /**
     * Run a multi-turn chat and return the assistant message plus raw usage.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     * @return array{content: string, tokens: int}
     */
    public function chat(array $messages, array $options = []): array;
}
