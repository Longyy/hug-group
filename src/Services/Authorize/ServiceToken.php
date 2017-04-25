<?php
namespace Hug\Group\Services\Authorize;

/**
 * 服务器通信令牌
 */
class ServiceToken
{
    /**
     * 生成令牌
     *
     * @author Sinute
     * @date   2015-04-19
     * @param  array     $aRequest     请求参数
     * @param  integer   $iRequestTime 请求时间
     * @param  string    $sKey         通信密钥
     * @return string
     */
    public function generate(array $aRequest, $iRequestTime, $sKey)
    {
        ksort($aRequest);
        /**
         * 兼容高级版本, 从5.5开始json_encode在处理非utf-8不会触发错误
         * 5.4版本以及5.5+版本采用相同行为处理
         */
        $sToken = md5(sha1(@json_encode($aRequest, JSON_NUMERIC_CHECK)) . $sKey . $iRequestTime);
        if ($iErrorID = json_last_error()) {
            $sToken = false;
            $sErrorMsg = 'UNKNOWN';
            switch ($iErrorID) {
                case JSON_ERROR_NONE:
                    $sErrorMsg = 'No error has occurred';
                    break;
                case JSON_ERROR_DEPTH:
                    $sErrorMsg = 'The maximum stack depth has been exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $sErrorMsg = 'Invalid or malformed JSON';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $sErrorMsg = 'Control character error, possibly incorrectly encoded';
                    break;
                case JSON_ERROR_SYNTAX:
                    $sErrorMsg = 'Syntax error';
                    break;
                case JSON_ERROR_UTF8:
                    $sErrorMsg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    break;
            }
            \Log::error($sErrorMsg, $aRequest);
        }
        return $sToken;
    }

    /**
     * 生成令牌·改
     *
     * @author Sinute
     * @date   2015-11-10
     * @param  array     $aRequest     请求参数
     * @param  integer   $iRequestTime 请求时间
     * @param  string    $sKey         通信密钥
     * @return string
     */
    public function generateMKII(array $aRequest, $iRequestTime, $sKey)
    {
        ksort($aRequest);
        return md5(sha1(static::serialize($aRequest)) . $sKey . $iRequestTime);
    }

    /**
     * 检查令牌
     *
     * @author Sinute
     * @date   2015-04-19
     * @param  string    $sToken       token
     * @param  array     $aRequest     请求参数
     * @param  integer   $iRequestTime 请求时间
     * @param  string    $sKey         通信密钥
     * @return boolean
     */
    public function check($sToken, $aRequest, $iRequestTime, $sKey)
    {
        return $sToken &&
        (
            ($sToken === $this->generate($aRequest, $iRequestTime, $sKey)) ||
            ($sToken === $this->generateMKII($aRequest, $iRequestTime, $sKey))
        );
    }

    /**
     * 序列化
     *
     * @author Sinute
     * @date   2015-11-10
     * @param  mix     $mData 需要序列化的数据
     * @return string         序列化后的字符串
     */
    public static function serialize($mData)
    {
        if (is_array($mData)) {
            $sStr = '';
            foreach ($mData as $sKey => $mValue) {
                $sStr = sprintf('%s%s%s', $sStr, $sKey, static::serialize($mValue));
            }
            return $sStr;
        } else {
            return $mData;
        }
    }
}
