<?php
namespace Hug\Group\Foundation;

/**
 * 获取url
 */
class Url
{
    protected static $aDomains;
    protected static $bInit;
    protected static $sCloudName;
    protected static $sDomainBase;
    protected static $sDomainMisc;
    protected static $sDomainInternal;
    protected static $sDomainInternalService;
    protected static $aSpecialDomains;

    public static function __callStatic($sName, $aArguments)
    {
        if (!static::$bInit) {
            static::init();
            static::$bInit = true;
        }

        if (substr($sName, -3) == 'Url') {
            $sName = substr($sName, 0, -3);
        }

        $sUri    = isset($aArguments[0]) ? $aArguments[0] : '';
        $aParams = isset($aArguments[1]) ? $aArguments[1] : [];
        $mScheme = isset($aArguments[2]) ? $aArguments[2] : false;

        if ($mScheme === true) {
            $sScheme = 'https://';
        } elseif (is_string($mScheme)) {
            $sScheme = $mScheme;
        } elseif (is_null($mScheme)) {
            $sScheme = '';
        } else {
            $sScheme = env('DOMAIN_PROTOCOL', static::isSecure() ? 'https://' : 'http://');
        }

        /**
         * 特殊处理
         */
        // 内部接口强制http
        if ((substr($sName, -8) == 'Internal') || (substr($sName, -7) == 'Service')) {
            $sScheme = 'http://';
        }
        // 线上https://file.anhouse.com自动转换为https://file.pinganfang.com
        if ($sScheme == 'https://' && $sName == 'file' ) {
            $sName = 'sslFile';
        }
        // 线上https://static自动转换为https://ssl-static
        if ($sScheme == 'https://' && env('APP_ENV') == 'production' && $sName == 'static') {
            $sName = 'sslStatic';
        }

        $sDomain = static::getDomain($sName);

        $sHost = preg_replace(['~^\w+://~', '~/$~'], '', $sDomain);
        $sUri  = preg_replace(['~/+~', '~^/~', '~/$~'], ['/', '', ''], $sUri);
        if ($sUri) {
            $sUri = '/' . $sUri;
        }

        if ($aParams) {
            $sParam = '?' . http_build_query($aParams);
        } else {
            $sParam = '';
        }

        return $sScheme . $sHost . $sUri . $sParam;
    }

    protected static function isSecure()
    {
        if ($sProto = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : null) {
            return in_array(strtolower(current(explode(',', $sProto))), ['https', 'on', 'ssl', '1']);
        }

        $sHttps = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null;

        return !empty($sHttps) && 'off' !== strtolower($sHttps);
    }

    protected static function init()
    {
        static::$aDomains = [];

        static::$sCloudName = '';
        // production以及anhouse环境没有cloud_name
        if (!in_array(env('APP_ENV'), ['production', 'anhouse']) && ($sCloudName = env('CLOUD_NAME', ''))) {
            static::$sCloudName = sprintf('%s.', env('DOMAIN_AS', $sCloudName));
        }

        switch (env('APP_ENV')) {
            case 'production':
                static::$sDomainBase     = 'pinganfang.com';
                static::$sDomainMisc     = 'anhouse.com';
                static::$sDomainInternal = 'd.pa.com';
                break;
            case 'anhouse':
                static::$sDomainBase     = 'anhouse.cn';
                static::$sDomainMisc     = 'anhouse.com';
                static::$sDomainInternal = 'd.pa.com';
                break;
            case 'qa':
                static::$sDomainBase     = 'qa.anhouse.com.cn';
                static::$sDomainMisc     = 'anhouse.com';
                static::$sDomainInternal = 'qa.anhouse.com.cn';
                break;
            case 'dev':
            default:
                static::$sDomainBase     = 'dev.anhouse.com.cn';
                static::$sDomainMisc     = 'anhouse.com';
                static::$sDomainInternal = 'dev.anhouse.com.cn';
                break;
        }

        // env中有特殊指定时以指定值覆盖
        static::$sDomainBase            = env('DOMAIN_BASE', static::$sDomainBase);
        static::$sDomainMisc            = env('DOMAIN_MISC', static::$sDomainMisc);
        static::$sDomainInternal        = env('DOMAIN_INTERNAL', static::$sDomainInternal);
        static::$sDomainInternalService = env('DOMAIN_INTERNAL_SERVICE', sprintf('s.%s', static::$sDomainInternal));

        // 特殊域名
        static::$aSpecialDomains = [
            // pinganfang.com
            'root'          => static::$sDomainBase,
            'cookieBase'    => static::$sDomainBase,

            // mobile
            'mobile'        => sprintf('m.%s%s', static::$sCloudName, static::$sDomainBase),

            // cloud.d.pa.com
            'serviceSms'    => sprintf('service-sms.%s%s', static::$sCloudName, static::$sDomainInternal), // service-sms.d.pa.com
            'serviceMember' => sprintf('service-member.%s%s', static::$sCloudName, static::$sDomainInternal), // service-member.d.pa.com
            'memberService' => sprintf('service-member.%s%s', static::$sCloudName, static::$sDomainInternal), // serviceMember别名

            // 文件服务使用线上配置
            'upload'        => 'upd.pinganfang.com',
            'file'          => 'file.anhouse.com',
            'sslFile'       => 'ssl-file.anhouse.com',

            // 奇葩
            /**
             * production: static.anhouse.com
             * anhouse: static.anhouse.cn
             * dev: static.cloud.dev.anhouse.com.cn
             * qa: static.cloud.qa.anhouse.com.cn
             */
            'static'        => (env('APP_ENV') == 'production') ? sprintf('static.%s', static::$sDomainMisc) : sprintf('static.%s%s', static::$sCloudName, static::$sDomainBase),
            /**
             * production: ssl-static.anhouse.com
             * anhouse: static.anhouse.cn
             * dev: static.cloud.dev.anhouse.com.cn
             * qa: static.cloud.qa.anhouse.com.cn
             */
            'sslStatic'     => (env('APP_ENV') == 'production') ? sprintf('ssl-static.%s', static::$sDomainMisc) : sprintf('static.%s%s', static::$sCloudName, static::$sDomainBase),
            /**
             * production: s.anhouse.com
             * anhouse: shorturl.anhouse.cn
             * dev: shorturl.cloud.dev.anhouse.com.cn
             * qa: shorturl.cloud.qa.anhouse.com.cn
             */
            'shorturl'      => (env('APP_ENV') == 'production') ? sprintf('s.%s', static::$sDomainMisc) : sprintf('shorturl.%s%s', static::$sCloudName, static::$sDomainBase),
            /**
             * production: www.ananzu.com
             * anhouse: www.ananzu.com.cn
             * dev: ananzu.cloud.dev.anhouse.com.cn
             * qa: ananzu.cloud.qa.anhouse.com.cn
             */
            'ananzu'        => (env('APP_ENV') == 'production') ? 'www.ananzu.com' : ((env('APP_ENV') == 'anhouse') ? 'www.ananzu.com.cn' : sprintf('ananzu.%s%s', static::$sCloudName, static::$sDomainBase)),
            /**
             * production: m.ananzu.com
             * anhouse: m.ananzu.com.cn
             * dev: ananzu.m.cloud.dev.anhouse.com.cn
             * qa: ananzu.m.cloud.qa.anhouse.com.cn
             */
            'ananzuMobile'  => (env('APP_ENV') == 'production') ? 'm.ananzu.com' : ((env('APP_ENV') == 'anhouse') ? 'm.ananzu.com.cn' : sprintf('ananzu.m.%s%s', static::$sCloudName, static::$sDomainBase)),
        ];
    }

    protected static function getDomain($sName)
    {
        if (isset(static::$aDomains[$sName])) {
            return static::$aDomains[$sName];
        }
        // from config
        $sDomain = config("domain.{$sName}");
        if (!$sDomain) {
            // from env
            $sDomain = env(static::getEnvName($sName));
        }
        if (!$sDomain) {
            // from special
            $sDomain = static::getSpecialDomain($sName);
        }
        if (!$sDomain) {
            // auto generate
            $sDomain = static::generateDomain($sName);
        }

        static::$aDomains[$sName] = $sDomain;
        return $sDomain;
    }

    protected static function getEnvName($sValue)
    {
        return sprintf('DOMAIN_%s', strtoupper(preg_replace('/(.)(?=[A-Z])/', '$1' . '_', $sValue)));
    }

    protected static function getSpecialDomain($sName)
    {
        return isset(static::$aSpecialDomains[$sName]) ? static::$aSpecialDomains[$sName] : null;
    }

    protected static function generateDomain($sName)
    {
        if (substr($sName, -6) == 'Mobile') {
            return sprintf('%s.m.%s%s', substr($sName, 0, -6), static::$sCloudName, static::$sDomainBase);
        } elseif (substr($sName, -8) == 'Internal') {
            return sprintf('%s.%s%s', substr($sName, 0, -8), static::$sCloudName, static::$sDomainInternal);
        } elseif (substr($sName, -7) == 'Service') {
            return sprintf('%s.%s%s', substr($sName, 0, -7), static::$sCloudName, static::$sDomainInternalService);
        } else {
            return sprintf('%s.%s%s', $sName, static::$sCloudName, static::$sDomainBase);
        }
    }
}