<?php
namespace Hug\Group\Services\Request;

class Client
{
    protected $sTrackID;
    protected $sFrom;
    protected $sToken;
    protected $iRequestTime;

    /**
     * 设置追踪id
     *
     * @author Sinute
     * @date   2015-09-22
     * @param  string     $sTrackID  追踪id
     */
    public function setTrackID($sTrackID)
    {
        $this->sTrackID = $sTrackID;
    }

    /**
     * 获取追踪id
     *
     * @author Sinute
     * @date   2015-09-22
     * @return string     追踪id
     */
    public function getTrackID()
    {
        if (!$this->sTrackID) {
            $this->sTrackID = $this->generateTrackID();
        }
        return $this->sTrackID;
    }

    /**
     * 设置来源
     *
     * @author Sinute
     * @date   2015-09-22
     * @param  string     $sFrom 来源字串
     */
    public function setFrom($sFrom)
    {
        $this->sFrom = $sFrom;
    }

    /**
     * 获取来源
     *
     * @author Sinute
     * @date   2015-09-22
     * @return string     来源字串
     */
    public function getFrom()
    {
        return $this->sFrom;
    }

    /**
     * 设置token
     *
     * @author Sinute
     * @date   2015-09-22
     * @param  string     $sToken token字串
     */
    public function setToken($sToken)
    {
        $this->sToken = $sToken;
    }

    /**
     * 获取token
     *
     * @author Sinute
     * @date   2015-09-22
     * @return string     token字串
     */
    public function getToken()
    {
        return $this->sToken;
    }

    /**
     * 设置请求时间
     *
     * @author Sinute
     * @date   2015-09-22
     * @param  integer     $iRequestTime 请求时间
     */
    public function setRequestTime($iRequestTime)
    {
        $this->iRequestTime = $iRequestTime;
    }

    /**
     * 获取请求时间
     *
     * @author Sinute
     * @date   2015-09-22
     * @return integer     请求时间
     */
    public function getRequestTime()
    {
        return $this->iRequestTime;
    }

    /**
     * 生成追踪id
     *
     * @author Sinute
     * @date   2015-09-22
     * @return string     追踪id
     */
    protected function generateTrackID()
    {
        return strtoupper(preg_replace(
            '~^(.{8})(.{4})(.{4})(.{4})(.{12})$~',
            '\1-\2-\3-\4-\5',
            md5(uniqid('', true))
        ));
    }
}
