<?php
namespace Hug\Group\Exceptions;

use Hug\Group\Contracts\Exceptions\ExceptionPool as ExceptionPoolContract;
use Hug\Group\Contracts\Exceptions\ValidateException as ValidateExceptionContract;
use Exception;
use Iterator;

/**
 * 异常池
 */
class ExceptionPool extends Exception implements ExceptionPoolContract, Iterator
{
    /**
     * 异常池实例
     * @var \Hug\Group\Exceptions\ExceptionPool
     */
    private static $oExceptionPool;

    /**
     * 异常数组
     * @var array
     */
    private $aExceptions;

    /**
     * 当前异常指针
     * @var integer
     */
    private $iPosition;

    /**
     * 构造函数
     *
     * @author Sinute
     * @date   2015-04-18
     */
    public function __construct()
    {
        $iPosition   = 0;
        $aExceptions = [];
    }

    /**
     * 添加异常
     *
     * @author Sinute
     * @date   2015-04-16
     * @param  \Hug\Group\Contracts\Exceptions\ValidateException $oException
     */
    public function add(ValidateExceptionContract $oException)
    {
        if (count($this->aExceptions) === 0) {
            parent::__construct($oException->getMessage(), $oException->getCode());
        }
        $this->aExceptions[] = $oException;
    }

    /**
     * 抛出异常
     *
     * @author Sinute
     * @date   2015-04-16
     * @return void
     */
    public function pour()
    {
        if (count($this->aExceptions) > 0) {
            throw $this;
        }
    }

    /**
     * 获取实例
     *
     * @author Sinute
     * @date   2015-04-16
     * @return \Hug\Group\Exceptions\ExceptionPool
     */
    public static function getInstance()
    {
        if (!self::$oExceptionPool) {
            self::$oExceptionPool = new ExceptionPool;
        }

        return self::$oExceptionPool;
    }

    /********************* 实现Iterator接口 ***********************/
    /**
     * 返回当前项
     *
     * @author Sinute
     * @date   2015-04-16
     * @return \Hug\Group\Contracts\Exceptions\Exception
     */
    public function current()
    {
        return $this->aExceptions[$this->iPosition];
    }

    /**
     * 返回当前key
     *
     * @author Sinute
     * @date   2015-04-16
     * @return int
     */
    public function key()
    {
        return $this->iPosition;
    }

    /**
     * 返回下一个位置
     *
     * @author Sinute
     * @date   2015-04-16
     * @return int
     */
    public function next()
    {
        ++$this->iPosition;
    }

    /**
     * 重置索引位置
     *
     * @author Sinute
     * @date   2015-04-16
     * @return void
     */
    public function rewind()
    {
        $this->iPosition = 0;
    }

    /**
     * 判断当前位置是否合法
     *
     * @author Sinute
     * @date   2015-04-16
     * @return bool
     */
    public function valid()
    {
        return isset($this->aExceptions[$this->iPosition]);
    }
}
