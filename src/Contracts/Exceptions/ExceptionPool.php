<?php

namespace Hug\Group\Contracts\Exceptions;

/**
 * 异常池接口
 *
 * @author Sinute
 * @date   2015-04-09
 */
interface ExceptionPool extends Exception
{
    /**
     * 添加异常
     *
     * @author Sinute
     * @date   2015-04-09
     * @param  string     $key
     * @param  \Hug\Group\Contracts\Exceptions\ValidateException  $oException
     */
    public function add(ValidateException $oException);

    /**
     * 抛出异常
     *
     * @author Sinute
     * @date   2015-04-09
     * @return \Hug\Group\Contracts\Exceptions\ExceptionPool
     */
    public function pour();
}
