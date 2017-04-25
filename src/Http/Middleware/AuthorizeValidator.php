<?php
namespace Hug\Group\Http\Middleware;

use Cache;
use Closure;
use Hug\Group\Exceptions\ServiceException;

/**
 * 授权验证
 */
class AuthorizeValidator
{
    public static function check($aParameters, $sTrackID, $sFrom, $iRequestTime, $sToken)
    {
        // 授权验证打开
        if (app('auth.service.config')->authEnabled()) {
            return static::checkParams($sFrom, $iRequestTime, $sTrackID, $sToken) &&
                static::checkRequestTime($sFrom, $iRequestTime) &&
                static::checkIp($sFrom) &&
                static::checkLimit($sFrom) &&
                static::checkToken($sToken, $aParameters, $sFrom, $iRequestTime);
        }
        return true;
    }

    protected static function checkParams($sFrom, $iRequestTime, $sTrackID, $sToken)
    {
        if (
            !$sFrom || // 来源
            !$iRequestTime || // 请求时间
            !$sTrackID || // 追踪ID
            !$sToken // Token
        ) {
            app('syslog')->info('AUTH_PARAMS_INCOMPLETE', compact('sTrackID', 'sFrom', 'iRequestTime', 'sToken'));
            return false;
        }
        return true;
    }

    protected static function checkRequestTime($sFrom, $iRequestTime)
    {
        // 验证请求时间
        if (abs($iRequestTime - $_SERVER['REQUEST_TIME']) > app('auth.service.config')->get("{$sFrom}.time_difference", 60)) {
            app('syslog')->info('AUTH_TIMEOUT', [
                'iRequestTime'       => $iRequestTime,
                'iReceivedTime'      => $_SERVER['REQUEST_TIME'],
                'iMaxTimeDifference' => app('auth.service.config')->get("{$sFrom}.time_difference", 60),
            ]);
            return false;
        }
        return true;
    }

    protected static function checkIp($sFrom)
    {
        // ip
        if ($mAllowedIp = app('auth.service.config')->get("{$sFrom}.ip")) {
            $sIp = $_SERVER['REMOTE_ADDR'];
            if (is_string($mAllowedIp) && str_is($mAllowedIp, $sIp)) {
                return true;
            } elseif (is_array($mAllowedIp)) {
                foreach ($mAllowedIp as $sAllowedIp) {
                    if (str_is($sAllowedIp, $sIp)) {
                        return true;
                    }
                }
            }
            app('syslog')->info('AUTH_IP_NOT_ALLOWED', [
                'sRemoteAddress'  => $sIp,
                'aAllowedAddress' => is_array($mAllowedIp) ? $mAllowedIp : [$mAllowedIp],
            ]);
            return false;
        }
        return true;
    }

    protected static function checkLimit($sFrom)
    {
        // 频率
        if ($sLimit = app('auth.service.config')->get("{$sFrom}.limit")) {
            $aLimit = explode('/', $sLimit);
            $aUnit  = [
                'min'    => 1,
                'minute' => 1,
                'hour'   => 60,
                'day'    => 60 * 24,
                'week'   => 60 * 24 * 7,
                'month'  => 60 * 24 * 30,
            ];
            $iLimit = array_get($aLimit, 0, 0);
            $iCycle = array_get($aUnit, array_get($aLimit, 1), false);
            $iTimes = null;
            if ($iLimit > 0 && $iCycle > 0) {
                $sCacheKey = "AUTHORIZE:LIMIT:{$sFrom}";
                if (!Cache::has($sCacheKey)) {
                    Cache::put($sCacheKey, 1, $iCycle);
                    return true;
                } elseif (($iTimes = Cache::increment($sCacheKey)) <= $iLimit) {
                    return true;
                }
            }
            app('syslog')->info('OUT_OF_RATE_LIMIT', compact('iTimes', 'iLimit', 'iCycle'));
            return false;
        }
        return true;
    }

    protected static function checkToken($sToken, $aParameters, $sFrom, $iRequestTime)
    {
        if (!($sKey = app('auth.service.config')->get("{$sFrom}.key"))) {
            app('syslog')->info('AUTH_KEY_NOT_FOUND', compact('sFrom'));
            return false;
        }
        // 验证token
        if (!app('auth.service.token')->check($sToken, $aParameters, $iRequestTime, $sKey)) {
            $sCorrectToken = app('auth.service.token')->generate($aParameters, $iRequestTime, $sKey);
            app('syslog')->info('AUTH_TOKEN_ERROR', compact('aParameters', 'sFrom', 'sKey', 'iRequestTime', 'sToken', 'sCorrectToken'));
            return false;
        }
        return true;
    }
}
