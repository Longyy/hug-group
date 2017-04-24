<?php
namespace Paf\Estate\Exceptions;

use Paf\Estate\Providers\ServiceProvider;

class ExceptionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('exception.pool', function () {
            return \Paf\Estate\Exceptions\ExceptionPool::getInstance();
        });
    }
}
