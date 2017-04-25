<?php
namespace Hug\Group\Providers;

class LogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 数据库查询日志
        app('events')->listen('illuminate.query', function ($sQuery, $aBindings, $fTime, $sConnection) {
            app('syslog')->debug("[{$sConnection} {$fTime}] {$sQuery}", $aBindings);
        });

        app()->singleton('syslog', function () {
            return new \Monolog\Logger(config('log.name', 'app'));
        });

        $iHandlersCount = count(app('log')->getHandlers());
        $aHandlers      = [];
        while ($iHandlersCount) {
            array_push($aHandlers, app('log')->popHandler());
            $iHandlersCount--;
        }
        while ($oHandler = array_pop($aHandlers)) {
            $oHandler->pushProcessor(function ($aRecord) {
                $sTrackId           = app('request.client')->getTrackID();
                $aRecord['message'] = "{$sTrackId} {$aRecord['message']}";
                return $aRecord;
            });
            app('log')->pushHandler($oHandler);
            app('syslog')->pushHandler($oHandler);
        }
    }

    public function register()
    {
    }
}
