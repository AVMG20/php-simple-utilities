# EventDispatcher Documentation

The `EventDispatcher` class provides a simple, yet powerful way to manage and dispatch events throughout your PHP application. It uses a singleton pattern to ensure that only one instance of the dispatcher exists, allowing for global access and event management.

## Example

Below is a simple example demonstrating how to register and dispatch an event:

```php
// Registering an event listener
EventDispatcher::listen('app.start', function() {
    echo 'Application has started.';
});

// Dispatching the event
EventDispatcher::dispatch('app.start');
```

## Usage

### Getting the Instance

Since `EventDispatcher` is a singleton, you obtain the instance by calling:

```php
$dispatcher = EventDispatcher::getInstance();
```

### Registering Event Listeners

To listen for events, use the `listen` method. Specify the event name and a callback to be executed when the event is dispatched.

```php
EventDispatcher::listen('user.registered', function($eventData) {
    echo 'User registered: ' . $eventData['username'];
});
```

### Dispatching Events

To dispatch an event, use the `dispatch` method. Provide the event name and, optionally, any data you wish to pass to the listeners.

```php
EventDispatcher::dispatch('user.registered', ['username' => 'john_doe']);
```

### Removing Event Listeners

To remove a specific listener, use the `removeListener` method with the event name and the exact listener callback.

```php
EventDispatcher::removeListener('user.registered', $listenerCallback);
```

## Methods

### getInstance()

Returns the singleton instance of the `EventDispatcher`.

### listen(string $eventName, callable $listener): void

Registers a listener for a specified event.

- `$eventName`: The name of the event.
- `$listener`: The callback function to execute when the event is dispatched.

### dispatch(string $eventName, $eventData = null): void

Dispatches an event, triggering all registered listeners for that event.

- `$eventName`: The name of the event.
- `$eventData`: Optional data to pass to the event listeners.

### removeListener(string $eventName, callable $listener): void

Removes a listener from a specified event.

- `$eventName`: The name of the event.
- `$listener`: The listener callback to remove.
