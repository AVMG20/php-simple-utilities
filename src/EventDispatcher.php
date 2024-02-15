<?php

declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

class EventDispatcher
{
    private static ?EventDispatcher $instance = null;
    private array $listeners = [];

    /**
     * Prevent direct object creation
     */
    private function __construct() {}

    /**
     * Prevent object cloning
     */
    private function __clone() {}

    /**
     * Returns a single instance of the class.
     *
     * @return EventDispatcher The singleton instance.
     */
    public static function getInstance(): EventDispatcher
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register an event listener.
     *
     * @param string $eventName The name of the event.
     * @param callable $listener The listener callback.
     */
    public static function listen(string $eventName, callable $listener): void
    {
        self::getInstance()->listeners[$eventName][] = $listener;
    }

    /**
     * Dispatch an event, calling all associated listeners.
     *
     * @param string $eventName The name of the event.
     * @param mixed $eventData Optional data to pass to the event listeners.
     */
    public static function dispatch(string $eventName, $eventData = null): void
    {
        if (!empty(self::getInstance()->listeners[$eventName])) {
            foreach (self::getInstance()->listeners[$eventName] as $listener) {
                call_user_func($listener, $eventData);
            }
        }
    }

    /**
     * Removes a listener from an event.
     *
     * @param string $eventName The name of the event.
     * @param callable $listener The listener to remove.
     */
    public static function removeListener(string $eventName, callable $listener): void
    {
        if (!empty(self::getInstance()->listeners[$eventName])) {
            foreach (self::getInstance()->listeners[$eventName] as $index => $registeredListener) {
                if ($listener === $registeredListener) {
                    unset(self::getInstance()->listeners[$eventName][$index]);
                }
            }
        }
    }
}
