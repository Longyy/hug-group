<?php
namespace Hug\Group\Http\Responses;

use Exception;
use Hug\Group\Contracts\Exceptions\Exception as ExceptionContract;
use Hug\Group\Contracts\Exceptions\ExceptionPool as ExceptionPoolContract;
use Hug\Group\Contracts\Exceptions\ValidateException as ValidateExceptionContract;

/**
 * 响应
 */
class ApiResponse
{
    /**
     * API异常返回
     *
     * @author Sinute
     * @date   2015-04-18
     * @param  \Exception  $oException 异常
     * @return array
     */
    public static function exceptionApi(Exception $oException)
    {
        $aErrors    = [];
        $sErrorType = '';
        if ($oException instanceof ExceptionContract) {
            if ($oException instanceof ExceptionPoolContract) {
                // 异常池处理
                foreach ($oException as $oValidateException) {
                    $aErrors[$oValidateException->getKey()] = [
                        'iCode'  => $oValidateException->getCode(),
                        'sMsg'   => $oValidateException->getMessage(),
                        'sRule'  => $oValidateException->getRule(),
                        'mValue' => $oValidateException->getValue(),
                    ];
                }
                $sErrorType = 'VALIDATION';
            } elseif ($oException instanceof ValidateExceptionContract) {
                $aErrors[$oException->getKey()] = [
                    'iCode'  => $oException->getCode(),
                    'sMsg'   => $oException->getMessage(),
                    'sRule'  => $oException->getRule(),
                    'mValue' => $oException->getValue(),
                ];
                $sErrorType = 'VALIDATION';
            } else {
                $aErrors[] = [
                    'iCode'  => $oException->getCode(),
                    'sMsg'   => $oException->getMessage(),
                    'sRule'  => '',
                    'mValue' => '',
                ];
                $sErrorType = 'LOGIC';
            }
        } else {
            // 系统异常
            $aNamespace                 = explode('\\', get_class($oException));
            $sClassName                 = end($aNamespace);
            $aErrors['SystemException'] = [
                'iCode'  => $oException->getCode(),
                'sMsg'   => $sClassName/*$oException->getMessage()*/, // 不传递信息以免外层没有处理直接返回给前端
                'sRule'  => '',
                'mValue' => '',
            ];
            $sErrorType = 'SYSTEM';
        }

        $aResponse = [
            'bSuccess'   => false,
            'sErrorType' => $sErrorType,
            'aErrors'    => $aErrors,
        ];

        return $aResponse;
    }

    /**
     * API详细返回
     *
     * @author Sinute
     * @date   2015-04-18
     * @param  mixed     $mContent 返回内容
     * @return array
     */
    public static function detailApi($mContent)
    {
        if (is_object($mContent) && method_exists($mContent, 'toArray')) {
            $mContent = $mContent->toArray();
        }

        return [
            'bSuccess' => true,
            'aData'    => $mContent,
        ];
    }

    /**
     * API列表返回
     *
     * @author Sinute
     * @date   2015-04-18
     * @param  mixed     $mContent 返回内容
     * @return array
     */
    public static function listApi($mContent)
    {
        if (is_object($mContent) && method_exists($mContent, 'toArray')) {
            $mContent = $mContent->toArray();
        }

        if (isset($mContent['data'])) {
            $aList = $mContent['data'];
        } elseif (isset($mContent['aData'])) {
            $aList = $mContent['aData'];
        } elseif (isset($mContent['list'])) {
            $aList = $mContent['list'];
        } elseif (isset($mContent['aList'])) {
            $aList = $mContent['aList'];
        } else {
            $aList = $mContent;
        }

        if (isset($mContent['total']) || isset($mContent['iTotal'])) {
            $aData = [
                'aList'        => $aList,
                'iTotal'       => isset($mContent['total']) ? intval($mContent['total']) : intval($mContent['iTotal']),
                'iPerPage'     => isset($mContent['per_page']) ? intval($mContent['per_page']) : intval($mContent['iPerPage']),
                'iCurrentPage' => isset($mContent['current_page']) ? intval($mContent['current_page']) : intval($mContent['iCurrentPage']),
                'iLastPage'    => isset($mContent['last_page']) ? intval($mContent['last_page']) : intval($mContent['iLastPage']),
                'iFrom'        => isset($mContent['from']) ? intval($mContent['from']) : intval($mContent['iFrom']),
                'iTo'          => isset($mContent['to']) ? intval($mContent['to']) : intval($mContent['iTo']),
            ];
        } else {
            $aData = [
                'aList' => $aList,
            ];
        }

        return [
            'bSuccess' => true,
            'aData'    => $aData,
        ];
    }

}
