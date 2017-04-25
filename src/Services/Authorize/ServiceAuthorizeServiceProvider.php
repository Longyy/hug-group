<?php
namespace Hug\Group\Services\Authorize;

use Hug\Group\Providers\ServiceProvider;

class ServiceAuthorizeServiceProvider extends ServiceProvider
{
    public function register()
    {
        // 注册通信密钥服务
        $this->app->singleton('auth.service.config', function () {
            $aConfigs = $this->app->make('config')->get('services.paf', []);
            return (new \Hug\Group\Services\Authorize\ServiceConfig)->store($aConfigs);
        });

        // 注册通信令牌服务
        $this->app->singleton('auth.service.token', function () {
            return new \Hug\Group\Services\Authorize\ServiceToken;
        });

        // 注册通信IP服务
        $this->app->singleton('auth.service.ip', function () {
            return new \Hug\Group\Services\Authorize\ServiceIp;
        });
    }
}
