<?php

namespace Avmg\PhpSimpleUtilities\Unit;

use Avmg\PhpSimpleUtilities\EventDispatcher;
use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{
    // Test that the EventDispatcher is a singleton
    public function testListenAndDispatch()
    {
        $eventDataReceived = null;

        EventDispatcher::listen('test.event', function ($eventData) use (&$eventDataReceived) {
            $eventDataReceived = $eventData;
        });

        EventDispatcher::dispatch('test.event', 'data');

        $this->assertEquals('data', $eventDataReceived);
    }

    // Test removing a listener
    public function testRemoveListener()
    {
        $listenerCalled = false;
        $listener = function () use (&$listenerCalled) {
            $listenerCalled = true;
        };

        EventDispatcher::listen('test.event', $listener);
        EventDispatcher::removeListener('test.event', $listener);
        EventDispatcher::dispatch('test.event');

        $this->assertFalse($listenerCalled, 'Listener should not be called after being removed.');
    }

    // Tests for dispatching events to multiple listeners
    public function testDispatchToMultipleListeners()
    {
        $listener1Called = false;
        $listener2Called = false;
        $listener1 = function () use (&$listener1Called) {
            $listener1Called = true;
        };
        $listener2 = function () use (&$listener2Called) {
            $listener2Called = true;
        };

        EventDispatcher::listen('multi.event', $listener1);
        EventDispatcher::listen('multi.event', $listener2);

        EventDispatcher::dispatch('multi.event');

        $this->assertTrue($listener1Called, 'Listener 1 should be called.');
        $this->assertTrue($listener2Called, 'Listener 2 should be called.');
    }

    // Test dispatching with no listeners should not fail
    public function testDispatchWithNoListeners()
    {
        $this->expectNotToPerformAssertions();

        // Attempting to dispatch an event with no listeners should simply not fail
        EventDispatcher::dispatch('no.listeners.event');
    }

    // Test that listeners for different events do not interfere
    public function testIsolationBetweenDifferentEvents()
    {
        $event1Called = false;
        $event2Called = false;

        EventDispatcher::listen('event1', function () use (&$event1Called) {
            $event1Called = true;
        });

        EventDispatcher::listen('event2', function () use (&$event2Called) {
            $event2Called = true;
        });

        // Dispatch event1, event2 should not be called
        EventDispatcher::dispatch('event1');

        $this->assertTrue($event1Called, 'Event1 listener should be called.');
        $this->assertFalse($event2Called, 'Event2 listener should not be called.');

        // Resetting flags for next test
        $event1Called = false;
        $event2Called = false;

        // Dispatch event2, event1 should not be called
        EventDispatcher::dispatch('event2');

        $this->assertFalse($event1Called, 'Event1 listener should not be called after event2 dispatch.');
        $this->assertTrue($event2Called, 'Event2 listener should be called.');
    }

    // Test removing a listener that does not exist does not cause errors
    public function testRemoveNonExistentListener()
    {
        $this->expectNotToPerformAssertions();

        // This should not cause an error
        EventDispatcher::removeListener('non.existent.event', function () {});
    }

    // Test that the same listener can be added to multiple events
    public function testSameListenerMultipleEvents()
    {
        $sharedListenerCalledCount = 0;
        $sharedListener = function () use (&$sharedListenerCalledCount) {
            $sharedListenerCalledCount++;
        };

        EventDispatcher::listen('event1', $sharedListener);
        EventDispatcher::listen('event2', $sharedListener);

        EventDispatcher::dispatch('event1');
        EventDispatcher::dispatch('event2');

        $this->assertEquals(2, $sharedListenerCalledCount, 'Shared listener should be called twice, once for each event.');
    }

}
