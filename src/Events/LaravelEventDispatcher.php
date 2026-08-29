<?php

declare(strict_types=1);

namespace Fopost\Social\Laravel\Events;

use Illuminate\Contracts\Events\Dispatcher;
use Fopost\Social\Events\Contracts\EventDispatcherInterface;

/**
 * Bridges Fopost Core's EventDispatcherInterface to Laravel's event dispatcher.
 *
 * This allows core events (PostPublished, PostFailed) to be dispatched
 * through Laravel's event system, enabling standard Laravel listeners.
 */
class LaravelEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    public function dispatch(object $event): void
    {
        $this->dispatcher->dispatch($event);
    }
}
