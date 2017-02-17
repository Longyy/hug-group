<?php
namespace Hug\Group\Database\Eloquent;

class Helpers
{
    /**
     * 获取排序
     *
     * @author Sinute
     * @date   2015-06-18
     * @return array
     */
    public static function getOrders($aOrders)
    {
        $sOrderQuery = isset($aOrders[self::getOrderKey()]) ? $aOrders[self::getOrderKey()] : '';
        $aOrders     = [];
        if ($sOrderQuery) {
            $aColumns = str_getcsv($sOrderQuery);
            foreach ($aColumns as $sColumn) {
                if (starts_with($sColumn, '-')) {
                    $aOrders[substr($sColumn, 1)] = 'DESC';
                } elseif (starts_with($sColumn, '+')) {
                    $aOrders[substr($sColumn, 1)] = 'ASC';
                } else {
                    $aOrders[$sColumn] = 'ASC';
                }
            }
        }

        return $aOrders;
    }

    /**
     * 获取排序key
     *
     * @author Sinute
     * @date   2015-07-02
     * @return string
     */
    public static function getOrderKey()
    {
        return '_sOrder';
    }

    /**
     * 获取选择列
     *
     * @author Sinute
     * @date   2015-07-02
     * @return array
     */
    public static function getColumns($aColumns)
    {
        $sColumnQuery = isset($aColumns[self::getColumnKey()]) ? $aColumns[self::getColumnKey()] : '';
        $aColumns     = [];
        if ($sColumnQuery) {
            $aColumns = str_getcsv($sColumnQuery);
        }

        return $aColumns;
    }

    /**
     * 获取选择列key
     *
     * @author Sinute
     * @date   2015-07-02
     * @return string
     */
    public static function getColumnKey()
    {
        return '_sColumn';
    }

    /**
     * 获取范围字段
     *
     * @author Sinute
     * @date   2015-07-02
     * @return array
     */
    public static function getRanges($aRanges)
    {
        $aQuery  = $aRanges;
        $aRanges = [];
        foreach ($aQuery as $sKey => $sValue) {
            if (ends_with($sKey, '-gt')) {
                $aRanges[] = [substr($sKey, 0, -3), '>', $sValue];
            } elseif (ends_with($sKey, '-lt')) {
                $aRanges[] = [substr($sKey, 0, -3), '<', $sValue];
            } elseif (ends_with($sKey, '-ge')) {
                $aRanges[] = [substr($sKey, 0, -3), '>=', $sValue];
            } elseif (ends_with($sKey, '-le')) {
                $aRanges[] = [substr($sKey, 0, -3), '<=', $sValue];
            } elseif (ends_with($sKey, '-ne')) {
                $aRanges[] = [substr($sKey, 0, -3), '<>', $sValue];
            } elseif (ends_with($sKey, '-eq')) {
                $aRanges[] = [substr($sKey, 0, -3), '=', $sValue];
            } elseif (ends_with($sKey, '-like')) {
                $aRanges[] = [substr($sKey, 0, -5), 'like', $sValue];
            } elseif (ends_with($sKey, '-in')) {
                $aRanges[] = [substr($sKey, 0, -3), 'in', str_getcsv($sValue)];
            } elseif (ends_with($sKey, '-ni')) {
                $aRanges[] = [substr($sKey, 0, -3), 'ni', str_getcsv($sValue)];
            }
        }
        return $aRanges;
    }

}
