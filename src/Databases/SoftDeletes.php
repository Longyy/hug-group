<?php
namespace Hug\Group\Database;

trait SoftDeletes {

    /**
     * 默认不从数据库删除真实数据
     * @var boolean
     */
    protected $forceDeleting = false;

    /**
     * 增加全局Scope
     *
     * @author Sinute
     * @date   2015-04-02
     * @return void
     */
    public static function bootSoftDeletes()
    {
        static::addGlobalScope(new SoftDeletingScope);
    }

    /**
     * 从数据库中删除真实数据
     *
     * @author Sinute
     * @date   2015-04-02
     * @return void
     */
    public function forceDelete()
    {
        $this->forceDeleting = true;

        $this->delete();

        $this->forceDeleting = false;
    }

    /**
     * 执行删除
     * 覆盖\Illuminate\Database\Eloquent\Model@performDeleteOnModel
     *
     * @author Sinute
     * @date   2015-04-02
     * @return void
     */
    protected function performDeleteOnModel()
    {
        if ($this->forceDeleting)
        {
            return $this->withTrashed()->where($this->getKeyName(), $this->getKey())->forceDelete();
        }

        return $this->runSoftDelete();
    }

    /**
     * 执行软删除
     *
     * @author Sinute
     * @date   2015-04-02
     * @return void
     */
    protected function runSoftDelete()
    {
        $query = $this->newQuery()->where($this->getKeyName(), $this->getKey());

        $this->{$this->getStatusColumn()} = $this->getInvalidStatus();
        // 更新删除时间
        $this->{$this->getDeletedAtColumn()} = $time = $this->freshTimestamp();

        $query->update([
            $this->getStatusColumn() => $this->getInvalidStatus(),
            // 更新删除时间
            $this->getDeletedAtColumn() => $this->fromDateTime($time),
        ]);
    }

    /**
     * 恢复软删除的数据
     *
     * @author Sinute
     * @date   2015-04-02
     * @param  integer    $defaultValue 恢复时使用的值, 默认为1
     * @return bool|null
     */
    // public function restore($defaultValue = 1)
    // {
    //     // If the restoring event does not return false, we will proceed with this
    //     // restore operation. Otherwise, we bail out so the developer will stop
    //     // the restore totally. We will clear the deleted timestamp and save.
    //     if ($this->fireModelEvent('restoring') === false)
    //     {
    //         return false;
    //     }

    //     $this->{$this->getStatusColumn()} = $defaultValue;

    //     // Once we have saved the model, we will fire the "restored" event so this
    //     // developer will do anything they need to after a restore operation is
    //     // totally finished. Then we will return the result of the save call.
    //     $this->exists = true;

    //     $result = $this->save();

    //     $this->fireModelEvent('restored', false);

    //     return $result;
    // }

    /**
     * 确定是否是软删除的数据
     *
     * @author Sinute
     * @date   2015-04-02
     * @return bool
     */
    public function trashed()
    {
        return ! $this->{$this->getStatusColumn()} === $this->getInvalidStatus();
    }

    /**
     * 获取包括已软删除的数据
     *
     * @author Sinute
     * @date   2015-04-02
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function withTrashed()
    {
        return (new static)->newQueryWithoutScope(new SoftDeletingScope);
    }

    /**
     * 只获取已软删除的数据
     *
     * @author Sinute
     * @date   2015-04-02
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function onlyTrashed()
    {
        $instance = new static;

        $column = $instance->getQualifiedStatusColumn();

        $invalidStatus = $instance->getInvalidStatus();

        return $instance->newQueryWithoutScope(new SoftDeletingScope)->where($column, $invalidStatus);
    }

    /**
     * 注册恢复事件调度器
     *
     * @author Sinute
     * @date   2015-04-02
     * @param  \Closure|string  $callback
     * @return void
     */
    // public static function restoring($callback)
    // {
    //     static::registerModelEvent('restoring', $callback);
    // }

    /**
     * 注册已恢复事件调度器
     *
     * @author Sinute
     * @date   2015-04-02
     * @param  \Closure|string  $callback
     * @return void
     */
    // public static function restored($callback)
    // {
    //     static::registerModelEvent('restored', $callback);
    // }

    /**
     * 获取删除列名
     *
     * @author Sinute
     * @date   2015-04-02
     * @return string
     */
    public function getStatusColumn()
    {
        return defined('static::STATUS') ? static::STATUS : 'status';
    }

    /**
     * 获取删除列的完整名称
     *
     * @author Sinute
     * @date   2015-04-02
     * @return string
     */
    public function getQualifiedStatusColumn()
    {
        return $this->getTable().'.'.$this->getStatusColumn();
    }

    /**
     * 获取表示无效的值
     *
     * @author Sinute
     * @date   2015-04-02
     * @return int
     */
    public function getInvalidStatus()
    {
        return defined('static::INVALID_STATUS') ? static::INVALID_STATUS : 0;
    }

    /**
     * 获取删除时间列名
     *
     * @author Sinute
     * @date   2015-04-27
     * @return string
     */
    public function getDeletedAtColumn()
    {
        return defined('static::DELETED_AT') ? static::DELETED_AT : 'deleted_at';
    }

    /**
     * 获取删除时间列的完整名称
     *
     * @author Sinute
     * @date   2015-04-27
     * @return string
     */
    public function getQualifiedDeletedAtColumn()
    {
        return $this->getTable().'.'.$this->getDeletedAtColumn();
    }

}
