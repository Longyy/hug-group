<?php
namespace Hug\Group\Validation;

use Illuminate\Contracts\Validation\Validator;

trait ValidatesServiceRequests
{
    use ValidationErrors;

    /**
     * 验证并获取正确数据
     *
     * @author Sinute
     * @date   2015-04-28
     * @param  array   $aParams      参数
     * @param  array   $aRules       验证规则
     * @param  array   $aMessages    错误信息
     * @param  array   $aAttributes  自定义属性
     * @return array
     */
    public function validate(array $aParams, array $aRules, array $aMessages = [], array $aAttributes = [])
    {
        $oValidator = $this->getValidationFactory()->make($aParams, $aRules, $aMessages, $aAttributes);

        if ($oValidator->fails()) {
            $this->throwValidationException($oValidator);
        } else {
            return array_filter(array_intersect_key($aParams, $aRules), function ($mValue) {return !is_null($mValue);});
        }
    }

    /**
     * 抛出验证错误
     *
     * @author Sinute
     * @date   2015-04-28
     * @param  \Illuminate\Contracts\Validation\Validator  $oValidator  验证类
     * @return void
     */
    protected function throwValidationException(Validator $oValidator)
    {
        $this->throwValidationExceptionPool($oValidator);
    }

    /**
     * Get a validation factory instance.
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function getValidationFactory()
    {
        return app('validator');
    }
}
