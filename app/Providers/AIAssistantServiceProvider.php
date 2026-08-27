<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\AI\AIProviderManager;
use App\Services\AI\AssistantService;
use App\Services\AI\AuthorizationGate;
use App\Services\AI\IntentDetector;
use App\Services\AI\ToolRegistry;
use Illuminate\Support\ServiceProvider;

/**
 * AIAssistantServiceProvider.
 *
 * Registers the AI Assistant pipeline services as singletons so they are
 * resolved once per request and shared across controllers/commands.
 */
class AIAssistantServiceProvider extends ServiceProvider
{
    /**
     * Register the AI services.
     */
    public function register(): void
    {
        $this->app->singleton(AIProviderManager::class, fn () => new AIProviderManager);
        $this->app->singleton(IntentDetector::class, fn () => new IntentDetector);
        $this->app->singleton(AuthorizationGate::class, fn () => new AuthorizationGate);
        $this->app->singleton(ToolRegistry::class, fn () => new ToolRegistry);

        $this->app->singleton(AssistantService::class, function ($app): AssistantService {
            return new AssistantService(
                $app->make(AIProviderManager::class),
                $app->make(IntentDetector::class),
                $app->make(AuthorizationGate::class),
                $app->make(ToolRegistry::class),
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/ai.php', 'ai');
    }
}
