<?php
namespace Paf\Estate\Exceptions;

use Paf\Estate\Contracts\Exceptions\ValidateException as ValidateExceptionContract;
use Paf\Estate\Exceptions\Translator\Translator;
use Exception;

/**
 * 自定义异常类, 新增了sKey, mValue, sRule几个属性
 */
class ValidateException extends Exception implements ValidateExceptionContract
{
    use Translator;

    private $sKey;
    private $mValue;
    private $sRule;

    /**
     * 构造函数
     *
     * @author Sinute
     * @date   2015-04-09
     * @param  string     $sKey
     * @param  mixed      $mValue
     * @param  string     $sMsg
     * @param  integer    $iCode
     * @param  string     $sRule
     */
    public function __construct($sKey, $mValue, $sMsg = '', $iCode = 0, $sRule = '')
    {
        list($sMsg, $iCode) = $this->trans($sMsg, $iCode);
        parent::__construct($sMsg, $iCode);
        $this->sKey   = $sKey;
        $this->mValue = $mValue;
        $this->sRule  = $sRule;
    }

    /**
     * 返回异常key
     *
     * @author Sinute
     * @date   2015-04-09
     * @return string
     */
    public function getKey()
    {
        return $this->sKey;
    }

    /**
     * 返回导致异常的值
     *
     * @author Sinute
     * @date   2015-04-09
     * @return mixed
     */
    public function getValue()
    {
        return $this->mValue;
    }

    /**
     * 返回导致异常的验证规则
     *
     * @author Sinute
     * @date   2015-04-09
     * @return string
     */
    public function getRule()
    {
        return $this->sRule;
    }
}
