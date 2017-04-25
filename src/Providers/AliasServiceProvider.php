<?php
namespace Hug\Group\Providers;

use Hug\Group\Foundation\AliasLoader;

class AliasServiceProvider extends ServiceProvider
{
    public function register()
    {
        AliasLoader::getInstance($this->app->make('config')->get('app.aliases'))->register();
    }
}
