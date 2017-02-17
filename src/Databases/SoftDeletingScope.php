<?php
namespace Hug\Group\Database;

use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;

class SoftDeletingScope implements ScopeInterface {

    /**
     * builder扩展方法
     * @var array
     */
    protected $extensions = ['ForceDelete', /*'Restore',*/ 'WithTrashed', 'OnlyTrashed'];

    /**
     * 应用scope
     *
     * @author Sinute
     * @date   2015-04-03
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where($model->getQualifiedStatusColumn(), '!=', new Expression($this->getInvalidStatus($builder)));

        $this->extend($builder);
    }

    /**
     * 移除scope
     *
     * @author Sinute
     * @date   2015-04-03
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function remove(Builder $builder, Model $model)
    {
        $column = $model->getQualifiedStatusColumn();

        $query = $builder->getQuery();

        $query->wheres = collect($query->wheres)->reject(function($where) use ($column)
        {
            return $this->isSoftDeleteConstraint($where, $column);
        })->values()->all();
    }

    /**
     * 添加扩展
     *
     * @author Sinute
     * @date   2015-04-03
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension)
        {
            $this->{"add{$extension}"}($builder);
        }

        $builder->onDelete(function(Builder $builder)
        {
            $column = $this->getStatusColumn($builder);
            // 更新删除时间
            $deletedAtColumn = $this->getDeletedAtColumn($builder);

            return $builder->update(array(
                $column => $this->getInvalidStatus($builder),
                // 更新删除时间
                $deletedAtColumn => $builder->getModel()->freshTimestampString(),
            ));
        });
    }

    /**
     * 获取删除列名
     *
     * @author Sinute
     * @date   2015-04-03
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return string
     */
    protected function getStatusColumn(Builder $builder)
    {
        if (count($builder->getQuery()->joins) > 0)
        {
            return $builder->getModel()->getQualifiedStatusColumn();
        }
        else
        {
            return $builder->getModel()->getStatusColumn();
        }
    }

    /**
     * 获取删除时间列名
     *
     * @author Sinute
     * @date   2015-04-27
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return string
     */
    protected function getDeletedAtColumn(Builder $builder)
    {
        if (count($builder->getQuery()->joins) > 0)
        {
            return $builder->getModel()->getQualifiedDeletedAtColumn();
        }
        else
        {
            return $builder->getModel()->getDeletedAtColumn();
        }
    }

    /**
     * 获取表示无效的值
     *
     * @author Sinute
     * @date   2015-04-03
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return string
     */
    protected function getInvalidStatus(Builder $builder)
    {
        return $builder->getModel()->getInvalidStatus();
    }

    /**
     * 增加强制删除方法
     *
     * @author Sinute
     * @date   2015-04-03
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addForceDelete(Builder $builder)
    {
        $builder->macro('forceDelete', function(Builder $builder)
        {
            return $builder->getQuery()->delete();
        });
    }

    /**
     * 增加恢复方法
     *
     * @author Sinute
     * @date   2015-04-03
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    // protected function addRestore(Builder $builder)
    // {
    //     $builder->macro('restore', function(Builder $builder, $defaultValue = 1)
    //     {
    //         $builder->withTrashed();

    //         return $builder->update(array($builder->getModel()->getStatusColumn() => $defaultValue));
    //     });
    // }

    /**
     * 增加获取包括已软删除数据方法
     *
     * @author Sinute
     * @date   2015-04-03
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithTrashed(Builder $builder)
    {
        $builder->macro('withTrashed', function(Builder $builder)
        {
            $this->remove($builder, $builder->getModel());

            return $builder;
        });
    }

    /**
     * 增加只获取软删除数据的方法
     *
     * @author Sinute
     * @date   2015-04-03
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addOnlyTrashed(Builder $builder)
    {
        $builder->macro('onlyTrashed', function(Builder $builder)
        {
            $model = $builder->getModel();

            $this->remove($builder, $model);

            $builder->getQuery()->where($model->getQualifiedStatusColumn(), new Expression($this->getInvalidStatus($builder)));

            return $builder;
        });
    }

    /**
     * 判断是否为软删除约束
     *
     * @author Sinute
     * @date   2015-04-03
     * @param  array      $where
     * @param  string     $column
     * @return boolean
     */
    protected function isSoftDeleteConstraint(array $where, $column)
    {
        return $where['type'] == 'Basic' && $where['column'] == $column;
    }
}
