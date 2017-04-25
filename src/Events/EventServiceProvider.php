<?php
namespace Hug\Group\Events;

use Hug\Group\Providers\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('events', function () {
            $events = new \Hug\Group\Events\Dispatcher;
            foreach (config('events', []) as $event => $listeners) {
                foreach ($listeners as $listener) {
                    $events->listen($event, $listener);
                }
            }
            return $events;
        });
    }
}
