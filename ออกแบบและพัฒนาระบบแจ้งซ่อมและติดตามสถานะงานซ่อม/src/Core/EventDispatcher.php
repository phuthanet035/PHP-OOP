<?php

namespace App\Core;

/**
 * Event Dispatcher (Observer Pattern)
 * Matching Class Diagram 5
 */
class EventDispatcher
{
    private static ?EventDispatcher $instance = null;
    private array $listeners = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function listen(string $event, callable|array $handler): void
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }
        $this->listeners[$event][] = $handler;
    }

    public function dispatch(string $event, mixed $payload = null): void
    {
        if (empty($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $handler) {
            try {
                if (is_array($handler) && is_string($handler[0])) {
                    $class = $handler[0];
                    $method = $handler[1];
                    $instance = new $class();
                    $instance->$method($payload);
                } else {
                    call_user_func($handler, $payload);
                }
            } catch (\Throwable $e) {
                error_log("Event Dispatcher error for '{$event}': " . $e->getMessage());
            }
        }
    }
}
