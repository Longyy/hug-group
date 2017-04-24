<?php
namespace Paf\Estate\Exceptions\Translator;

trait Translator
{
    /**
     * 翻译异常信息
     *
     * @author Sinute
     * @date   2015-05-20
     * @param  string     $sMessage 错误信息
     * @param  integer    $iCode    错误码
     * @return array                翻译后的错误信息错误码数组
     */
    public function trans($sMessage, $iCode)
    {
        $sClassName = get_class($this);

        $sRawMessage   = "exceptions.{$sClassName}.{$sMessage}.0";
        $sTransMessage = trans($sRawMessage);
        if ($sTransMessage == $sRawMessage) {
            $sTransMessage = $sMessage;
        }

        if (is_int($iCode) && $iCode != 0) {
            $iTransCode = $iCode;
        } else {
            $sRawCode   = "exceptions.{$sClassName}.{$sMessage}.1";
            $iTransCode = trans($sRawCode);
            if ($iTransCode == $sRawCode) {
                $iTransCode = $iCode;
            }
        }

        return [$sTransMessage, intval($iTransCode)];
    }
}
