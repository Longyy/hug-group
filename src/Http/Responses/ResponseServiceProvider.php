<?php
namespace Hug\Group\Http\Responses;

use Hug\Group\Providers\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('response', function () {
            return new \Hug\Group\Http\Responses\ResponseFactory;
        });
    }

}
