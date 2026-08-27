<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\DTOs\AI\AssistantResponse;
use App\Models\AiAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * AssistantService.
 *
 * Orchestrates the AI Academic Assistant pipeline:
 *   Step 1  User Query            -> input
 *   Step 2  Intent Detection      -> IntentDetector
 *   Step 3  Authorization Check   -> AuthorizationGate
 *   Step 4  Data Retrieval        -> ToolRegistry (read-only tools)
 *   Step 5  Response Generation   -> AIProviderManager (LLM)
 *   Step 6  Audit Logging         -> AiAuditLog
 *
 * The assistant is strictly read-only: no tool mutates data, and Step 3
 * guarantees role/tenant isolation. This is the main research contribution of
 * the thesis.
 */
class AssistantService
{
    public function __construct(
        private readonly AIProviderManager $providers,
        private readonly IntentDetector $intentDetector,
        private readonly AuthorizationGate $authGate,
        private readonly ToolRegistry $tools,
    ) {}

    /**
     * Process a user query and return the assistant response.
     */
    public function ask(User $user, string $query, ?string $provider = null): AssistantResponse
    {
        // Step 2: Intent Detection
        $detected = $this->intentDetector->detect($query);
        $intent = $detected['intent'];

        // Step 3: Authorization Check
        $auth = $this->authGate->check($user, $intent);

        if (! $auth['allowed']) {
            return $this->respond(
                $user,
                $query,
                $intent,
                $auth['reason'],
                null,
                [],
                0,
                $provider,
                false
            );
        }

        // Step 4: Data Retrieval (read-only)
        $tool = $this->tools->forIntent($intent);
        $context = [];

        if ($tool) {
            $context = $tool->execute($user);
        }

        // Step 5: Response Generation
        $providerInstance = $this->providers->driver($provider);

        $messages = $this->buildMessages($user, $query, $intent, $context);

        try {
            $result = $providerInstance->chat($messages);
            $answer = $result['content'];
            $tokens = $result['tokens'];
        } catch (\Throwable $e) {
            Log::error('AI assistant provider failure: '.$e->getMessage());
            $answer = 'I am unable to generate a response right now. Please try again later.';
            $tokens = 0;
        }

        // Step 6: Audit Logging
        return $this->respond(
            $user,
            $query,
            $intent,
            $answer,
            $tool?->name(),
            $context['data'] ?? [],
            $tokens,
            $providerInstance->getName(),
            true
        );
    }

    /**
     * Build the LLM message list with role-aware system context and retrieved data.
     *
     * @param  array{summary: string, data: array}  $context
     * @return array<int, array{role: string, content: string}>
     */
    private function buildMessages(User $user, string $query, string $intent, array $context): array
    {
        $role = $user->getRoleNames()->first() ?? 'user';
        $system = 'You are the AI Academic Assistant of an educational management system. '
            ."You serve {$role} users. You are strictly read-only and must never suggest "
            .'changing marks, approving admissions, modifying attendance or processing payments. '
            ."Answer concisely using only the provided data context. Detected intent: {$intent}.";

        $dataContext = $context['summary'] ?? '';
        if (! empty($context['data'])) {
            $dataContext .= "\nDATA: ".json_encode($context['data']);
        }

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $dataContext."\nQUESTION: ".$query],
        ];
    }

    /**
     * Persist the audit log and wrap the result.
     */
    private function respond(
        User $user,
        string $query,
        string $intent,
        string $answer,
        ?string $tool,
        array $context,
        int $tokens,
        string $providerName,
        bool $authorized
    ): AssistantResponse {
        AiAuditLog::create([
            'user_id' => $user->id,
            'institution_id' => $user->institution_id,
            'intent' => $intent,
            'tool' => $tool,
            'query' => $query,
            'response' => $answer,
            'tokens_used' => $tokens,
        ]);

        return new AssistantResponse(
            answer: $answer,
            intent: $intent,
            authorized: $authorized,
            tool: $tool,
            context: $context,
            tokens: $tokens,
            provider: $providerName
        );
    }
}
