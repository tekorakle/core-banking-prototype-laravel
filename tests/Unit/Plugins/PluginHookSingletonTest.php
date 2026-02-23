<?php

declare(strict_types=1);

namespace Tests\Unit\Plugins;

use App\Infrastructure\Plugins\PluginHookManager;
use Tests\TestCase;

class PluginHookSingletonTest extends TestCase
{
    public function test_hook_manager_is_singleton(): void
    {
        $instance1 = app()->make(PluginHookManager::class);
        $instance2 = app()->make(PluginHookManager::class);

        $this->assertSame($instance1, $instance2);
    }

    public function test_hooks_dispatched_through_resolved_instances_reach_listeners(): void
    {
        $hookManager = app()->make(PluginHookManager::class);

        $received = [];

        $listener = new class ($received) implements \App\Infrastructure\Plugins\Contracts\PluginHookInterface {
            /** @var array<int, array<string, mixed>> */
            private array $received;

            /**
             * @param  array<int, array<string, mixed>>  $received
             */
            public function __construct(array &$received)
            {
                $this->received = &$received;
            }

            public function getHookName(): string
            {
                return 'account.created';
            }

            public function getPriority(): int
            {
                return 0;
            }

            /**
             * @param  array<string, mixed>  $payload
             */
            public function handle(array $payload): void
            {
                $this->received[] = $payload;
            }
        };

        $hookManager->register($listener);

        // Resolve a second reference — should be same singleton
        $hookManager2 = app()->make(PluginHookManager::class);
        $hookManager2->dispatch('account.created', ['test' => true]);

        $this->assertCount(1, $received);
        $this->assertTrue($received[0]['test']);
    }
}
