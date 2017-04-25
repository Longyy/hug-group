<?php
namespace Hug\Group\Services\Authorize;

/**
 * 服务器通信密钥
 */
class ServiceConfig
{
    /**
     * 服务器通信密钥
     * @var array
     */
    protected $aServiceConfigs;

    protected $bNotEnabled;

    public function __construct()
    {
        $this->aServiceConfigs = [];
    }

    /**
     * 保存服务器通信密钥
     *
     * @author Sinute
     * @date   2015-04-19
     * @param  array     $aServiceConfigs 设置服务器通信密钥
     */
    public function store($aServiceConfigs)
    {
        if (is_array($aServiceConfigs)) {
            $this->aServiceConfigs = array_merge($this->aServiceConfigs, $aServiceConfigs);
        }
        return $this;
    }

    /**
     * 获取服务器通信配置
     *
     * @author Sinute
     * @date   2015-04-19
     * @param  string     $sKey    配置名
     * @param  string     $mDefault 默认值
     * @return string               配置值
     */
    public function get($sKey, $mDefault = null)
    {
        $mValue = array_get($this->aServiceConfigs, $sKey, null);
        if (null === $mValue) {
            $aSegments = explode('.', $sKey);
            // hack for xxx.key/xxx.time_difference
            if (count($aSegments) == 2) {
                $sFrom = strtoupper(str_replace('-', '_', $aSegments[0]));
                if ($aSegments[1] == 'key') {
                    $mValue = env("PAF_SERVICE_KEY_{$sFrom}");
                } elseif ($aSegments[1] == 'time_difference') {
                    $mValue = env("PAF_SERVICE_TD_{$sFrom}");
                }
            }
        }

        return $mValue !== null ? $mValue : $mDefault;
    }

    /**
     * 判断是否验证授权
     *
     * @author Sinute
     * @date   2015-04-22
     * @return boolean
     */
    public function authEnabled()
    {
        return isset($this->bNotEnabled) ? !$this->bNotEnabled : env('APP_AUTH', true);
    }

    /**
     * 设定是否验证权限
     *
     * @author Sinute
     * @date   2015-04-22
     * @param  boolean
     */
    public function setAuthEnabled($bEnabled)
    {
        $this->bNotEnabled = !$bEnabled;
        return $this;
    }
}
