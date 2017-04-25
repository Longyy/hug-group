<?php
namespace Hug\Group\Exceptions;

use Hug\Group\Contracts\Exceptions\ServiceException as ServiceExceptionContract;
use Hug\Group\Exceptions\Translator\Translator;
use Exception;

class ServiceException extends Exception implements ServiceExceptionContract
{
    use Translator;

    /**
     * 构造函数
     *
     * @author Sinute
     * @date   2015-05-20
     * @param  string     $sMsg  错误信息
     * @param  integer    $iCode 错误码
     */
    public function __construct($sMsg = '', $iCode = 0)
    {
        list($sMsg, $iCode) = $this->trans($sMsg, $iCode);
        parent::__construct($sMsg, $iCode);
    }
}
