<?php
namespace Hug\Group\Events;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;

class Dispatcher implements DispatcherContract
{
    /**
     * The registered event listeners.
     *
     * @var array
     */
    protected $aListeners = [];

    /**
     * The wildcard listeners.
     *
     * @var array
     */
    protected $aWildcards = [];

    /**
     * The sorted event listeners.
     *
     * @var array
     */
    protected $aSorted = [];

    /**
     * The event firing stack.
     *
     * @var array
     */
    protected $aFiring = [];

    /**
     * Register an event listener with the dispatcher.
     *
     * @param  string|array  $mEvents
     * @param  mixed   $mListener
     * @param  int     $iPriority
     * @return void
     */
    public function listen($mEvents, $mListener, $iPriority = 0)
    {
        foreach ((array) $mEvents as $sEvent) {
            if (str_contains($sEvent, '*')) {
                $this->setupWildcardListen($sEvent, $mListener);
            } else {
                $this->aListeners[$sEvent][$iPriority][] = $this->makeListener($mListener);
                unset($this->aSorted[$sEvent]);
            }
        }
    }

    /**
     * Setup a wildcard listener callback.
     *
     * @param  string  $sEvent
     * @param  mixed   $mListener
     * @return void
     */
    protected function setupWildcardListen($sEvent, $mListener)
    {
        $this->aWildcards[$sEvent][] = $this->makeListener($mListener);
    }

    /**
     * Determine if a given event has listeners.
     *
     * @param  string  $sEventName
     * @return bool
     */
    public function hasListeners($sEventName)
    {
        return isset($this->aListeners[$sEventName]);
    }

    /**
     * Fire an event until the first non-null response is returned.
     *
     * @param  string  $sEvent
     * @param  array   $aPayload
     * @return mixed
     */
    public function until($sEvent, $aPayload = [])
    {
        return $this->fire($sEvent, $aPayload, true);
    }

    /**
     * Fire an event and call the listeners.
     *
     * @param  string|object  $mEvent
     * @param  mixed   $mPayload
     * @param  bool    $bHalt
     * @return array|null
     */
    public function fire($mEvent, $mPayload = [], $bHalt = false)
    {
        // When the given "event" is actually an object we will assume it is an event
        // object and use the class as the event name and this event itself as the
        // payload to the handler, which makes object based events quite simple.
        if (is_object($mEvent)) {
            list($mPayload, $mEvent) = [[$mEvent], get_class($mEvent)];
        }
        $aResponses = [];
        // If an array is not given to us as the payload, we will turn it into one so
        // we can easily use call_user_func_array on the listeners, passing in the
        // payload to each of them so that they receive each of these arguments.
        if (!is_array($mPayload)) {
            $mPayload = array($mPayload);
        }

        $this->aFiring[] = $mEvent;
        foreach ($this->getListeners($mEvent) as $mListener) {
            $mResponse = call_user_func_array($mListener, $mPayload);
            // If a response is returned from the listener and event halting is enabled
            // we will just return this response, and not call the rest of the event
            // listeners. Otherwise we will add the response on the response list.
            if (!is_null($mResponse) && $bHalt) {
                array_pop($this->aFiring);
                return $mResponse;
            }
            // If a boolean false is returned from a listener, we will stop propagating
            // the event to any further listeners down in the chain, else we keep on
            // looping through the listeners and firing every one in our sequence.
            if ($mResponse === false) {
                break;
            }

            $aResponses[] = $mResponse;
        }
        array_pop($this->aFiring);
        return $bHalt ? null : $aResponses;
    }

    /**
     * Get all of the listeners for a given event name.
     *
     * @param  string  $sEventName
     * @return array
     */
    public function getListeners($sEventName)
    {
        $aWildcards = $this->getWildcardListeners($sEventName);
        if (!isset($this->aSorted[$sEventName])) {
            $this->sortListeners($sEventName);
        }
        return array_merge($this->aSorted[$sEventName], $aWildcards);
    }

    /**
     * Get the wildcard listeners for the event.
     *
     * @param  string  $sEventName
     * @return array
     */
    protected function getWildcardListeners($sEventName)
    {
        $aWildcards = [];
        foreach ($this->aWildcards as $sKey => $aListeners) {
            if (str_is($sKey, $sEventName)) {
                $aWildcards = array_merge($aWildcards, $aListeners);
            }

        }
        return $aWildcards;
    }

    /**
     * Sort the listeners for a given event by priority.
     *
     * @param  string  $sEventName
     * @return array
     */
    protected function sortListeners($sEventName)
    {
        $this->aSorted[$sEventName] = [];
        // If listeners exist for the given event, we will sort them by the priority
        // so that we can call them in the correct order. We will cache off these
        // sorted event listeners so we do not have to re-sort on every events.
        if (isset($this->aListeners[$sEventName])) {
            krsort($this->aListeners[$sEventName]);
            $this->aSorted[$sEventName] = call_user_func_array(
                'array_merge', $this->aListeners[$sEventName]
            );
        }
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param  mixed   $mListener
     * @return mixed
     */
    public function makeListener($mListener)
    {
        return is_string($mListener) ? $this->createClassListener($mListener) : $mListener;
    }

    /**
     * Create a class based listener using the IoC container.
     *
     * @param  string    $sListener
     * @return \Closure
     */
    public function createClassListener($sListener)
    {
        return function () use ($sListener) {
            return call_user_func_array(
                $this->createClassCallable($sListener), func_get_args()
            );
        };
    }

    /**
     * Create the class based event callable.
     *
     * @param  string  $sListener
     * @return callable
     */
    protected function createClassCallable($sListener)
    {
        list($sClass, $sMethod) = $this->parseClassCallable($sListener);
        // currently not support for queue
        return array(app($sClass), $sMethod);
        // if ($this->handlerShouldBeQueued($sClass)) {
        //     return $this->createQueuedHandlerCallable($sClass, $sMethod);
        // } else {
        //     return array(app($sClass), $sMethod);
        // }
    }

    /**
     * Parse the class listener into class and method.
     *
     * @param  string  $sListener
     * @return array
     */
    protected function parseClassCallable($sListener)
    {
        $aSegments = explode('@', $sListener);
        return [$aSegments[0], count($aSegments) == 2 ? $aSegments[1] : 'handle'];
    }

    /**
     * Get the event that is currently firing.
     *
     * @return string
     */
    public function firing()
    {
        return last($this->aFiring);
    }

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param  string  $sEvent
     * @return void
     */
    public function forget($sEvent)
    {
        unset($this->aListeners[$sEvent], $this->aSorted[$sEvent]);
    }

    /**
     * Forget all of the queued listeners.
     *
     * @return void
     */
    public function forgetPushed()
    {
        foreach ($this->aListeners as $sEvent => $_) {
            if (ends_with($sEvent, '_pushed')) {
                $this->forget($sEvent);
            }

        }
    }
}
