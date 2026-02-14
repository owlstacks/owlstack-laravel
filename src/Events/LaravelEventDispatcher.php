<?php

declare(strict_types=1);

namespace Owlstack\Laravel\Events;

use Illuminate\Contracts\Events\Dispatcher;
use Owlstack\Core\Events\Contracts\EventDispatcherInterface;

/**
 * Bridges Owlstack Core's EventDispatcherInterface to Laravel's event dispatcher.
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
