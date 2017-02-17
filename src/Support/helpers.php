<?php

if (!function_exists('config_path')) {
    function config_path($sPath = '')
    {
        return App::configPath() . ($sPath ? '/' . $sPath : $sPath);
    }
}

if (!function_exists('storage_path')) {
    function storage_path($sPath = '')
    {
        return App::storagePath() . ($sPath ? '/' . $sPath : $sPath);
    }
}

if (!function_exists('public_path')) {
    function public_path($sPath = '')
    {
        return App::publicPath() . ($sPath ? '/' . $sPath : $sPath);
    }
}

if (!function_exists('app_path')) {
    function app_path($sPath = '')
    {
        return App::appPath() . ($sPath ? '/' . $sPath : $sPath);
    }
}

if (!function_exists('base_path')) {
    function base_path($sPath = '')
    {
        return App::basePath() . ($sPath ? '/' . $sPath : $sPath);
    }
}

if (!function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param  string  $iID
     * @param  array   $aParameters
     * @param  string  $sDomain
     * @param  string  $sLocale
     * @return string
     */
    function trans($iID = null, $aParameters = array(), $sDomain = 'messages', $sLocale = null)
    {
        if (is_null($iID)) {
            return app('translator');
        }

        return app('translator')->trans($iID, $aParameters, $sDomain, $sLocale);
    }
}

if (!function_exists('config')) {
    function config($sKey = null, $mDefault = null)
    {
        if (is_null($sKey)) {
            return app('config');
        }

        if (is_array($sKey)) {
            return Config::merge($sKey);
        }

        return Config::get($sKey, $mDefault);
    }
}

if (!function_exists('app')) {
    function app($sMake = null)
    {
        if (!is_null($sMake)) {
            return App::make($sMake);
        }

        return App::make('app');
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string  $sKey
     * @param  mixed   $mDefault
     * @return mixed
     */
    function env($sKey, $mDefault = null)
    {
        $sValue = getenv($sKey);

        if ($sValue === false) {
            return value($mDefault);
        }

        switch (strtolower($sValue)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'null':
            case '(null)':
                return null;

            case 'empty':
            case '(empty)':
                return '';
        }

        return $sValue;
    }
}

if (!function_exists('str_quick_random')) {
    /**
     * 生成随机字符串
     *
     * @author Sinute
     * @date   2015-06-17
     * @param  integer    $iLength 字符串长度
     * @param  string     $sPool   字符池
     * @return string              随机字符串
     */
    function str_quick_random($iLength, $sPool = null)
    {
        $iLength = (int) $iLength;
        if (empty($sPool)) {
            $sPool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        return substr(str_shuffle(str_repeat($sPool, $iLength)), 0, $iLength);
    }
}

if (!function_exists('hf_url')) {
    /**
     * 生成好房url
     *
     * @author Sinute
     * @date   2015-06-17
     * @param  string     $sDomain 域名
     * @param  string     $sUri   路径
     * @param  array      $aParams url参数
     * @param  boolean    $mScheme 调用方案
     * @return string              url
     */
    function hf_url($sDomain, $sUri = '', $aParams = [], $mScheme = false)
    {
        if (substr($sDomain, -3) == 'Url') {
            $sDomain = substr($sDomain, 0, -3);
        }
        return Paf\Estate\Foundation\Url::{$sDomain . 'Url'}($sUri, $aParams, $mScheme);
    }
}

if (!function_exists('str_length_trunc')) {
    /**
     * 字符串按长度截断
     *
     * @author Sinute
     * @date   2015-06-17
     * @param  string     $sStr    字符串
     * @param  integer    $iLength 长度
     * @param  string     $sSuffix 后缀
     * @return string              截断后的字符串
     */
    function str_length_trunc($sStr, $iLength, $sSuffix = '...')
    {
        $sRunes = preg_split('/(?<!^)(?!$)/u', $sStr);
        if (count($sRunes) > $iLength) {
            return join(array_slice($sRunes, 0, $iLength)) . $sSuffix;
        } else {
            return $sStr;
        }
    }
}

if (!function_exists('str_width_trunc')) {
    /**
     * 字符串按宽度截断
     *
     * @author Sinute
     * @date   2015-06-17
     * @param  string     $sStr    字符串
     * @param  integer    $iWidth  宽度
     * @param  string     $sSuffix 后缀
     * @return string              截断后的字符串
     */
    function str_width_trunc($sStr, $iWidth, $sSuffix = '...')
    {
        $sRunes  = preg_split('/(?<!^)(?!$)/u', $sStr);
        $iWidthT = 0;
        $iCount  = 0;
        foreach ($sRunes as $sRune) {
            if (($iWidthT += (strlen($sRune) === 1 ? 1 : 2)) <= $iWidth) {
                $iCount++;
            } else {
                break;
            }

        }
        if (count($sRunes) > $iCount) {
            return join(array_slice($sRunes, 0, $iCount)) . $sSuffix;
        } else {
            return $sStr;
        }
    }
}

if (!function_exists('array_assoc')) {
    /**
     * 将索引数组变为关联数组
     *
     * @author Sinute
     * @date   2015-07-21
     * @param  array      $aArray    数组
     * @param  mixed     $oCallback  字符串时为生成索引的key, Closure时结果为生成的key
     * @return array
     */
    function array_assoc(array $aArray, $oCallback)
    {
        if ($oCallback instanceof Closure) {
            $aAssocArray = [];
            foreach ($aArray as $mValue) {
                $aAssocArray[$oCallback($mValue)] = $mValue;
            }
            return $aAssocArray;
        } else {
            return array_combine(array_fetch($aArray, $oCallback), $aArray);
        }
    }
}

if (!function_exists('jo')) {
    /**
     * json object
     *
     * @author Sinute
     * @date   2015-08-27
     * @param  array      $aArray    数组
     * @return array|stdClass
     */
    function jo(array $aArray)
    {
        if (count($aArray) == 0) {
            return new stdClass;
        } else {
            return $aArray;
        }
    }
}

if (!function_exists('hf_assets')) {
    /**
     * 生成静态资源地址
     *
     * @author Sinute
     * @date   2015-10-14
     * @param  string     $sDomain 域名
     * @param  string     $sPath   路径
     * @param  string     $sVersion   版本号
     * @param  array      $aParams url参数
     * @param  boolean    $mScheme 调用方案
     * @return string              url
     */
    function hf_assets($sDomain, $sPath, $sVersion = '', $aParams = [], $mScheme = false)
    {
        $sVersion = config("view.versions.{$sVersion}", $sVersion);
        if (!$sVersion) {
            // 如果没有版本号则退化为hf_url
            return hf_url($sDomain, $sPath, $aParams, $mScheme);
        }
        return hf_url($sDomain, preg_replace('~(\w+/)(.*)~', "\$1/{$sVersion}/\$2", $sPath), $aParams, $mScheme);
    }
}
