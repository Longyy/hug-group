<?php
namespace Hug\Group\Contracts\Accessors;

/**
 * 服务访问器
 *
 * @author Sinute
 * @date   2015-04-16
 */
interface ServiceAccessor extends Accessor
{
    /**
     * 执行请求
     *
     * @author Sinute
     * @date   2015-04-18
     * @param  string     $sName   请求名
     * @param  array      $aParams 参数
     * @return mixed               响应
     */
    public function request($sName, array $aParams);

    /**
     * 判断请求是否成功
     *
     * @author Sinute
     * @date   2015-04-19
     * @param  mixed     $mResponse 响应
     * @return boolean
     */
    public function isSuccess($mResponse);

    /**
     * 获取响应数据
     *
     * @author Sinute
     * @date   2015-04-19
     * @param  mixed     $mResponse 响应
     * @return mixed                数据
     */
    public function getData($mResponse);

    /**
     * 获取错误信息
     *
     * @author Sinute
     * @date   2015-04-19
     * @param  mixed     $mResponse 响应
     * @return mixed                错误信息
     */
    public function getError($mResponse);

    /**
     * 错误处理
     *
     * @author Sinute
     * @date   2015-04-19
     * @param  mixed     $mError 错误信息
     * @param  mixed     &$mData 响应数据
     * @return boolean           true继续执行/false抛出异常
     */
    public function errorHandler($mError, &$mData);
}
