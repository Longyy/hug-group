<?php
namespace Hug\Group\Bootstrapper;

class RPC
{
    protected $oApp;

    public function __construct($oApp)
    {
        $this->oApp = $oApp;
    }

    protected static function checkBlackList($sVersion, $sModule, $sMethod)
    {
        $sRuleKey = strtolower("{$sVersion}:{$sModule}@{$sMethod}");
        // blacklist
        $aList = config("rpc.blacklist", false);
        if ($aList !== false) {
            foreach ($aList as $sBlackVersion => $aBlackMethods) {
                foreach ($aBlackMethods as $sBlackMethod) {
                    if ($sRuleKey === strtolower("{$sBlackVersion}:{$sBlackMethod}")) {
                        // find in blacklist
                        return false;
                    }
                }
            }
        }
        return true;
    }

    protected static function checkWhiteList($sVersion, $sModule, $sMethod)
    {
        $sRuleKey = strtolower("{$sVersion}:{$sModule}@{$sMethod}");
        // whitelist
        $aList = config("rpc.whitelist", false);
        if ($aList !== false) {
            $bInWhitelist = false;
            foreach ($aList as $sWhiteVersion => $aWhiteMethods) {
                foreach ($aWhiteMethods as $sWhiteMethod) {
                    if ($sRuleKey === strtolower("{$sWhiteVersion}:{$sWhiteMethod}")) {
                        // find in whitelist
                        $bInWhitelist = true;
                        break 2;
                    }
                }
            }
            if (!$bInWhitelist) {
                return false;
            }
        }
        return true;
    }

    public function bootstrap()
    {
        return function ($sModule, $sMethod, $aParams, $sID) {
            try {
                $sTrackID     = array_get($aParams, '0.trackId', null);
                $sFrom        = array_get($aParams, '0.from', null);
                $iRequestTime = array_get($aParams, '0.requestTime', null);
                $sToken       = array_get($aParams, '0.token', null);

                $this->oApp->make('request.client')->setTrackID($sTrackID);
                $this->oApp->make('request.client')->setFrom($sFrom);
                $this->oApp->make('request.client')->setRequestTime($iRequestTime);
                $this->oApp->make('request.client')->setToken($sToken);

                // 验证校验字段
                $aParameters = array_get($aParams, '1', []);

                \Hug\Group\Http\Middleware\RequestResponseLogger::logRequest($aParameters);

                if (!app('auth.service.ip')->check($_SERVER['REMOTE_ADDR'])) {
                    app('syslog')->info('AUTH_IP_NOT_ALLOWED', [
                        'sRemoteAddress'  => $_SERVER['REMOTE_ADDR'],
                        'aAllowedAddress' => app('auth.service.ip')->get(),
                    ]);
                    throw new \Hug\Group\Exceptions\ServiceException("UNAUTHORIZED");
                }

                if (
                    !\Hug\Group\Http\Middleware\AuthorizeValidator::check(
                        $aParameters,
                        $sTrackID,
                        $sFrom,
                        $iRequestTime,
                        $sToken
                    )
                ) {
                    throw new \Hug\Group\Exceptions\ServiceException("UNAUTHORIZED");
                }

                \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($aParameters) {
                    foreach ($aParameters as $aParameter) {
                        if (isset($aParameter['iPage'])) {
                            return $aParameter['iPage'];
                        }
                    }
                });

                $sVersion = str_replace('.', '_', array_get($aParams, '0.version', ''));

                // alias
                if ($sRoute = config("rpc.server.{$sVersion}.{$sModule}.{$sMethod}", null)) {
                    // get real module, method
                    list($sModule, $sMethod) = explode('@', $sRoute);
                }

                if (!self::checkBlackList($sVersion, $sModule, $sMethod) || !self::checkWhiteList($sVersion, $sModule, $sMethod)) {
                    throw new \Hug\Group\Exceptions\ServiceException("NOT_ALLOWED_METHOD");
                }

                $sClass    = config('rpc.namespace', 'App\\Http\\Controllers') . "\\V{$sVersion}\\{$sModule}";
                $oClass    = new $sClass;
                $aResponse = call_user_func_array([$oClass, $sMethod], $aParameters);
                \Hug\Group\Http\Middleware\RequestResponseLogger::logResponse($aResponse);

                return new \Paf\LightService\Server\Response($aResponse);
            } catch (\Hug\Group\Contracts\Exceptions\Exception $oException) {
                $aResponse = app('response')->exceptionApi($oException);
                \Hug\Group\Http\Middleware\RequestResponseLogger::logResponse($aResponse);
                // use exceptionApi to response custom exception
                return new \Paf\LightService\Server\Response($aResponse);
            } catch (\Exception $oException) {
                // log exception
                app('syslog')->error((string) $oException);
                error_log((string) $oException);
                // throw system exception to rpc
                throw $oException;
            }
        };
    }
}
