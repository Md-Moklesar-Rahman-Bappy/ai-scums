<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\AI\AssistantResponse;
use App\Http\Requests\Assistant\AssistantAskRequest;
use App\Models\User;
use App\Services\AI\AIProviderManager;
use App\Services\AI\AssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * AssistantController.
 *
 * Web interface for the AI Academic Assistant. Consumes the {@see AssistantService}
 * pipeline (intent detection -> authorization -> RAG retrieval -> LLM -> audit).
 * Responses are rendered in the chat UI via AJAX.
 */
class AssistantController extends Controller
{
    public function __construct(private readonly AssistantService $assistant) {}

    /**
     * Show the assistant chat UI.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();
        $providers = app(AIProviderManager::class)->available();

        return view('assistant.index', compact('providers', 'user'));
    }

    /**
     * Handle an assistant question (AJAX).
     */
    public function ask(AssistantAskRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var AssistantResponse $result */
        $result = $this->assistant->ask(
            $user,
            $request->input('query'),
            $request->input('provider')
        );

        return response()->json($result->toArray());
    }

    /**
     * Optional non-AJAX fallback (POST then redirect with answer).
     */
    public function askLegacy(AssistantAskRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $result = $this->assistant->ask($user, $request->input('query'), $request->input('provider'));

        return redirect()->route('assistant.index')->with('answer', $result->answer);
    }
}
