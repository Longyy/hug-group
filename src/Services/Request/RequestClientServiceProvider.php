<?php
namespace Hug\Group\Services\Request;

use Hug\Group\Providers\ServiceProvider;

class RequestClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        // 注册追踪ID服务
        $this->app->singleton('request.client', function () {
            return new \Hug\Group\Services\Request\Client;
        });

        // @deprecated
        $this->app->singleton('trackId', function () {
            return new \Hug\Group\Services\Request\TrackID;
        });
    }

}
