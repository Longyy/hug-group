<?php

namespace Hug\Group\Contracts\Exceptions;

/**
 * 异常接口
 *
 * @author Sinute
 * @date   2015-04-09
 */
interface ValidateException extends Exception
{
    public function getKey(); // 返回异常key
    public function getValue(); // 返回导致异常的值
    public function getRule(); // 返回导致异常的验证规则
}
