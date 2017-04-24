<?php
namespace Paf\Estate\Validation;

use Illuminate\Contracts\Validation\Validator;
use Paf\Estate\Exceptions\ExceptionPool;
use Paf\Estate\Exceptions\ValidateException;

trait ValidationErrors
{
    /**
     * 服务层格式化验证错误信息
     *
     * @author Sinute
     * @date   2015-04-28
     * @param  \Illuminate\Validation\Validator $oValidator 验证类
     * @return array
     */
    protected function formatValidationExceptions(Validator $oValidator)
    {
        $aErrors   = [];
        $aData     = $oValidator->getData();
        $aFailed   = $oValidator->failed();
        $aRules    = $oValidator->getRules();
        $aMessages = $oValidator->getCustomMessages();
        foreach ($aFailed as $sAttribute => $aFail) {
            $aParams    = reset($aFail);
            $sRule      = strtoupper(key($aFail));
            $sLowerRule = strtolower(key($aFail));
            array_unshift($aParams, $sAttribute);
            preg_match_all('~:\w+~', app('translator')->getCatalogue()->get("exceptions.Paf\Estate\Exceptions\ValidateException.{$sRule}.0"), $aMatch);

            $aParameters = [];
            if (count($aParams) > count($aMatch[0])) {
                $aParams = array_slice($aParams, 0, count($aMatch[0]));
            } elseif (count($aParams) < count($aMatch[0])) {
                $aParams = array_pad($aParams, count($aMatch[0]), '');
            }
            $aParameters = array_combine($aMatch[0], $aParams);

            $aErrors[] = new ValidateException(
                $sAttribute,
                array_get($aData, $sAttribute),
                isset($aMessages["{$sAttribute}.{$sLowerRule}"]) ? $aMessages["{$sAttribute}.{$sLowerRule}"] : trans("exceptions.Paf\Estate\Exceptions\ValidateException.{$sRule}.0", $aParameters),
                trans("exceptions.Paf\Estate\Exceptions\ValidateException.{$sRule}.1"),
                join("|", $aRules[$sAttribute])
            );
        }
        return $aErrors;
    }

    /**
     * 抛出验证异常池
     * @param  Validator $oValidator 验证类
     * @return void
     */
    protected function throwValidationExceptionPool(Validator $oValidator)
    {
        $aErrors = $this->formatValidationExceptions($oValidator);
        foreach ($aErrors as $oError) {
            ExceptionPool::getInstance()->add($oError);
        }
        ExceptionPool::getInstance()->pour();
    }
}