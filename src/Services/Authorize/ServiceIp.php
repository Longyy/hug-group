<?php
namespace Hug\Group\Services\Authorize;

use Hug\Group\Http\IpUtils;

/**
 * 服务器Ip
 */
class ServiceIp
{
    /**
     * 服务器IP白名单
     * @var array
     */
    protected $aServiceIps;

    public function __construct()
    {
        $this->aServiceIps = [
            '192.168.0.0/16',
            '10.0.0.0/8',
            '172.16.0.0/16',
            '172.17.0.0/16',
            '172.31.0.0/16',
            '127.0.0.1',
            '101.95.96.102',
            '114.80.125.114',
            '116.247.112.178',
            '116.247.112.179',
            '116.247.112.180',
            '222.73.117.197',
        ];
    }

    /**
     * 保存服务器IP白名单
     *
     * @author Sinute
     * @date   2015-04-19
     * @param  array     $aServiceIps 服务器IP白名单
     */
    public function store(array $aServiceIps)
    {
        $this->aServiceIps = $aServiceIps;
    }

    /**
     * 获取白名单
     *
     * @author Sinute
     * @date   2015-10-29
     * @return array
     */
    public function get()
    {
        return $this->aServiceIps;
    }

    /**
     * 检查服务器IP白名单
     *
     * @author Sinute
     * @date   2015-04-19
     * @param  string     $sIp ip
     * @return boolean
     */
    public function check($sIp)
    {
        return IpUtils::checkIp($sIp, $this->aServiceIps);
    }
}
