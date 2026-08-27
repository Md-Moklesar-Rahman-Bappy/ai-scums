<?php

declare(strict_types=1);

namespace App\DTOs\AI;

use App\Services\AI\AssistantService;

/**
 * AssistantResponse.
 *
 * Immutable value object returned by the {@see AssistantService}.
 * Carries the generated answer plus the pipeline metadata used for the
 * "Step 6: Audit Logging" and UI display.
 */
final class AssistantResponse
{
    /**
     * @param  string  $answer  Generated assistant text.
     * @param  string  $intent  Detected intent constant.
     * @param  bool  $authorized  Whether the query passed authorization.
     * @param  string|null  $tool  Tool name used (if any).
     * @param  array  $context  Retrieved data context.
     * @param  int  $tokens  Tokens consumed.
     * @param  string  $provider  Provider name used.
     */
    public function __construct(
        public readonly string $answer,
        public readonly string $intent,
        public readonly bool $authorized,
        public readonly ?string $tool,
        public readonly array $context,
        public readonly int $tokens,
        public readonly string $provider,
    ) {}

    /**
     * Convert to array (for JSON APIs).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'answer' => $this->answer,
            'intent' => $this->intent,
            'authorized' => $this->authorized,
            'tool' => $this->tool,
            'context' => $this->context,
            'tokens' => $this->tokens,
            'provider' => $this->provider,
        ];
    }
}
