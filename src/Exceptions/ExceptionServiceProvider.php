<?php
namespace Hug\Group\Exceptions;

use Hug\Group\Providers\ServiceProvider;

class ExceptionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('exception.pool', function () {
            return \Hug\Group\Exceptions\ExceptionPool::getInstance();
        });
    }
}
