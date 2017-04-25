<?php
namespace Hug\Group\Http\Middleware;

/**
 * 请求响应日志中间件
 */
class RequestResponseLogger
{
    protected static $bEnable = true;

    public static function disable()
    {
        static::$bEnable = false;
    }

    public static function enable()
    {
        static::$bEnable = true;
    }

    public static function logRequest($aRequest = [])
    {
        if (!static::$bEnable) {
            return;
        }
        app('syslog')->info(
            "request",
            [
                'method'    => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '',
                'url'       => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost',
                'path'      => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
                'query'     => isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '',
                'ajax'      => 'false',
                'pjax'      => 'false',
                'secure'    => 'false',
                'ip'        => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
                'ips'       => [],
                'userAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
                'request'   => $aRequest,
            ]
        );
    }

    public static function logResponse($aResponse = [])
    {
        if (!static::$bEnable) {
            return;
        }
        app('syslog')->info(
            "response",
            [
                'status'   => 200,
                'response' => $aResponse,
            ]
        );
    }
}
