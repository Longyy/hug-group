<?php
namespace Hug\Group\Database\Eloquent;

use Illuminate\Database\Events\Model as BaseModel;
use Hug\Group\Database\SoftDeletes;

class Model extends BaseModel {
    /**
     * 使用软删除
     */
    use SoftDeletes;

    /**
     * 可用于排序的字段
     * @var array
     */
    protected $orderable = [];

    /**
     * 数据库字段
     * @var array
     */
    protected $columnable = [];

    /**
     * 用于范围查询的字段
     * @var array
     */
    protected $rangeable = [];

    /**
     * 数据库字段是否为下划线风格
     * @var boolean
     */
    public static $snakeAttributes = false;

    /**
     * 设置主键字段
     * @var string
     */
    protected $primaryKey = 'iAutoID';

    /**
     * 设置创建时间字段
     */
    const CREATED_AT = 'iCreateTime';

    /**
     * 设置更新时间字段
     */
    const UPDATED_AT = 'iUpdateTime';

    /**
     * 设置状态字段
     */
    const STATUS = 'iStatus';

    const DEFAULT_PER_PAGE = 20;  //默认分页数

    /**
     * 设置删除状态的值
     */
    const INVALID_STATUS = 0;

    /**
     * 设置删除时间字段
     */
    const DELETED_AT = 'iDeleteTime';

    public function __construct(array $aAttributes = []) {
        app('db');
        parent::__construct($aAttributes);
    }

    /**
     * 转换日期时间
     *
     * @author Sinute
     * @date   2015-04-18
     * @param  $mValue 日期时间
     * @return integer
     */
    public function fromDateTime($mValue) {
        return $mValue;
    }

    /**
     * 获取当前时间
     *
     * @author Sinute
     * @date   2015-04-19
     * @return integer
     */
    public function freshTimestamp() {
        return time();
    }

    /**
     * 使用时间戳, 不自动格式化时间
     *
     * @author Sinute
     * @date   2015-05-19
     * @return array
     */
    public function getDates() {
        return [];
    }

    /**
     * 处理排序
     *
     * @author Sinute
     * @date   2015-06-18
     * @param  \Illuminate\Database\Eloquent\Builder $oQuery
     * @param  array $aOrders
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithOrder($oQuery, array $aOrders) {
        if ($this->orderable == ['*']) {
            $aOrderable = $this->columnable;
        } else {
            $aOrderable = $this->orderable;
        }
        foreach ($aOrders as $sColumn => $sOrder) {
            if (in_array($sColumn, $aOrderable)) {
                $oQuery->orderBy($sColumn, $sOrder);
            }
        }
        return $oQuery;
    }

    /**
     * 处理范围查询
     *
     * @author Sinute
     * @date   2015-07-02
     * @param  \Illuminate\Database\Eloquent\Builder $oQuery
     * @param  array $aRanges
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRange($oQuery, array $aRanges) {
        if ($this->rangeable == ['*']) {
            $aRangeable = $this->columnable;
        } else {
            $aRangeable = $this->rangeable;
        }
        foreach ($aRanges as $aRange) {
            if (in_array($aRange[0], $aRangeable)) {
                if ($aRange[1] == 'in') {
                    $oQuery->whereIn($aRange[0], $aRange[2]);
                } elseif ($aRange[1] == 'ni') {
                    $oQuery->whereNotIn($aRange[0], $aRange[2]);
                } else {
                    $oQuery->where($aRange[0], $aRange[1], $aRange[2]);
                }
            }
        }
        return $oQuery;
    }

    /**
     * 需要获取的列
     *
     * @author Sinute
     * @date   2015-07-02
     * @return array
     */
    public static function columns(array $aColumns = []) {
        $oModel = new static;
        if (!$oModel->columnable || !$aColumns) {
            $aColumns = ['*'];
        } else {
            $aColumns = array_intersect($oModel->columnable, Helpers::getColumns($aColumns));
            if (!$aColumns) {
                $aColumns = $oModel->columnable;
            }
        }
        return $aColumns;
    }

    /**
     * 获取排序
     *
     * @author Sinute
     * @date   2015-07-14
     * @return array
     */
    public static function orders(array $aOrders) {
        return Helpers::getOrders($aOrders);
    }

    /**
     * 获取范围
     *
     * @author Sinute
     * @date   2015-07-14
     * @return array
     */
    public static function ranges(array $aRanges) {
        return Helpers::getRanges($aRanges);
    }
}
