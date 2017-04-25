<?php
namespace Hug\Group\Providers;

class CommonServiceProvider extends ServiceProvider
{
    protected $aProviders = [
        'Hug\Group\Providers\AliasServiceProvider',
        'Hug\Group\Exceptions\ExceptionServiceProvider',
        'Hug\Group\Services\Request\RequestClientServiceProvider',
        'Hug\Group\Http\Responses\ResponseServiceProvider',
        'Hug\Group\Providers\LogServiceProvider', // Event, Log
        'Hug\Group\Services\Authorize\ServiceAuthorizeServiceProvider',
        'Hug\Group\Events\EventServiceProvider',
    ];

    public function register()
    {
        $aProviders = [];
        foreach ($this->aProviders as $sProvider) {
            $oProvider = new $sProvider($this->app);
            $oProvider->register();
            $aProviders[$sProvider] = $oProvider;
        }
        foreach ($this->aProviders as $sProvider) {
            if (method_exists($aProviders[$sProvider], 'boot')) {
                call_user_func([$aProviders[$sProvider], 'boot']);
            }
        }
    }
}
