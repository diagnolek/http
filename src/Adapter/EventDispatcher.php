<?php
/**
 * @author Sebastian Pondo
 */

namespace Diagnolek\Http\Adapter;

use Psr\EventDispatcher\EventDispatcherInterface;

final class EventDispatcher implements EventDispatcherInterface
{

    private $store;

    public function __construct()
    {
        $this->store = new \ArrayObject();
    }

    public function dispatch(object $event)
    {
        foreach ($this->store as $name => $listeners) {
            if ($event instanceof Event && $event->getName() == $name) {
                $value = $event->getValue();
                foreach ($listeners as $listener) {
                    $value = $listener($value);
                }
                $event->setValue($value);
                if ($event->isPropagationStopped()) {
                    break;
                }
            }
        }
        return $event;
    }

    public function attach(string $name, callable $listener)
    {
        if ($this->store->offsetExists($name)) {
            $arr = $this->store->offsetGet($name)[] = $listener;
            $this->store->offsetSet($name, $arr);
        } else {
            $this->store->offsetSet($name, [$listener]);
        }
    }

    public function detach(string $name)
    {
        $this->store->offsetUnset($name);
    }

    public function clear()
    {
        $this->store->exchangeArray([]);
    }
}