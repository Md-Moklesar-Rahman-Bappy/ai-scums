<?php

declare(strict_types=1);

namespace App\Contracts\AI;

use App\Models\User;
use App\Services\AI\Intent;

/**
 * AIDataToolInterface.
 *
 * A read-only data retrieval tool used by the assistant's RAG pipeline. Each
 * tool corresponds to exactly one {@see Intent} and returns a
 * compact, prompt-ready context array. Tools MUST NOT mutate data - the
 * assistant is strictly read-only. This is the "Step 4: Data Retrieval" stage.
 */
interface AIDataToolInterface
{
    /**
     * The intent this tool satisfies.
     */
    public function intent(): string;

    /**
     * Human-readable tool name (used in audit logs).
     */
    public function name(): string;

    /**
     * Retrieve the context for the given user.
     *
     * @return array{summary: string, data: array<int, mixed>}
     */
    public function execute(User $user): array;
}
