<?php
/**
 * 使用方法
 *
 *
 */

namespace common\helpers;

use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecordInterface;



trait XyQuery  {
    public $hasRelation = true;
    public $add = [];
    public $delete = [];
    public $only = [];
    public $wish = [];

public function xyWhere($condition, $params = []){
    if($condition instanceof \Closure ){
        return  $condition($this);
     }else{
        return  $this -> where($condition, $params);
     }
}

public function xyLikeWhere($condition, $params = []){
    return  $this -> where(['like',$condition],$params);
}

public function xyWith($par){
    array_walk($par, function ($value, $relation) {
      if(is_array($value)){
          $parse = $this -> xyParse($value);
          return  $this -> xyMiddleWith($relation,$parse['select'], $parse['child']);
      }else{
          return   $this -> with([$relation=>$value]);
       }
    });
      return $this;
}

    public function xyJoinWith($par){
        array_walk($par, function ($value, $relation) {
            if(is_array($value)){
                $parse = $this -> xyParse($value);
                return  $this -> xyMiddleJionWith($relation,$parse['select'], $parse['child']);
            }else{
                return   $this -> joinWith([$relation=>$value]);
            }
        });
        return $this;
    }

    protected function xyMiddleWith($relation,$select, $child){
        return   $this->with([$relation => function ($query) use ($select, $child) {
            if ($child) {
              return   $query -> xyWith($child);
            }
            if($select){
                return $query -> select($select);
            }
        }]);

    }

    protected function xyMiddleJionWith($relation,$select, $child){
        return   $this->joinWith([$relation => function ($query) use ($select, $child) {
            if ($child) {
                return   $query -> xyJoinWith($child);
            }
            if($select){
                return $query -> select($select);
            }
        }]);

    }

    public function xyParse($value){
        $select = [];
        $child = [];
        foreach ($value as $k => $v) {
            if (is_int($k)) {
                $select[] = $v;
            } else {
                $child[$k] = $v;
            }
        }
        return compact('select','child');
    }

    public function xyAdd($add){
        $this->add = $add;
        return $this;
}

    public function xyDelete($delete){
        $this->delete = $delete;
        return $this;
    }

    public function xyOnly($only){
        $this->only = $only;
        return $this;
    }

    public function xyWish($wish){
        $this->wish = $wish;
        return $this;
    }
    public function xyRelation($relation=true){
        $this->hasRelation = $relation;
        return $this;
    }
    public function xyWishModel($model){
        $model -> add = $this -> add;
        $model -> delete = $this -> delete;
        $model -> only = $this -> only;
        $model -> wish = $this -> wish;
        $model -> hasRelation = $this -> hasRelation;
    }
    public function xyAll($db = null){
        if ($this->emulateExecution) {
            return [];
        }

        $rows = $this->createCommand($db)->queryAll();

        return $this->xyPopulate($rows);
    }
    public function xyOne($db = null)
    {
        if ($this->emulateExecution) {
            return false;
        }
        $row = $this->createCommand($db)->queryOne();
        if ($row !== false) {
            $models = $this->xyPopulate([$row]);
            return reset($models) ?: null;
        } else {
            return null;
        }
    }










    //////////////////////////////////////////////////////
public function xyPopulate($rows){
    if (empty($rows)) {
        return [];
    }

    $models = $this->xyCreateModels($rows);
    if (!empty($this->join) && $this->indexBy === null) {
        $models = $this->xyRemoveDuplicatedModels($models);
    }

    if (!empty($this->with)) {
        $this->xyFindWith($this->with, $models);
    }

    if ($this->inverseOf !== null) {
        $this->addInverseRelations($models);
    }

    if (!$this->asArray) {
        foreach ($models as $model) {
            $model->afterFind();
        }
    }

    return $models;
}

public function xyCreateModels($rows){

    $models = [];
    if ($this->asArray) {
        if ($this->indexBy === null) {
            return $rows;
        }
        foreach ($rows as $row) {
            if (is_string($this->indexBy)) {
                $key = $row[$this->indexBy];
            } else {
                $key = call_user_func($this->indexBy, $row);
            }
            $models[$key] = $row;
        }
    } else {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        if ($this->indexBy === null) {
            foreach ($rows as $row) {
                $model = $class::instantiate($row);
                $this -> xyWishModel($model);
                $modelClass = get_class($model);
                $modelClass::populateRecord($model, $row);
                $models[] = $model;
            }
        } else {
            foreach ($rows as $row) {
                $model = $class::instantiate($row);
                $this -> xyWishModel($model);
                $modelClass = get_class($model);
                $modelClass::populateRecord($model, $row);
                if (is_string($this->indexBy)) {
                    $key = $model->{$this->indexBy};
                } else {
                    $key = call_user_func($this->indexBy, $model);
                }
                $models[$key] = $model;
            }
        }
    }

    return $models;
}
///////////////////////////////////////////////////////////////















////////////////////////////////////////////////////////////////////////////
//非必须分割线
///////////////////////////////////////////////////////////////////////////
public function xyFindWith($with, &$models)
    {

        $primaryModel = reset($models);
        if (!$primaryModel instanceof ActiveRecordInterface) {
            $primaryModel = new $this->modelClass;
        }

        $relations = $this->xyNormalizeRelations($primaryModel, $with);
        /* @var $relation ActiveQuery */
        foreach ($relations as $name => $relation) {
            if ($relation->asArray === null) {
                // inherit asArray from primary query
                $relation->asArray($this->asArray);
            }

            $relation->xyPopulateRelation($name, $models);
        }
    }


    public function xyNormalizeRelations($model, $with)
    {
        $relations = [];
        foreach ($with as $name => $callback) {
            if (is_int($name)) {
                $name = $callback;
                $callback = null;
            }
            if (($pos = strpos($name, '.')) !== false) {
                // with sub-relations
                $childName = substr($name, $pos + 1);
                $name = substr($name, 0, $pos);
            } else {
                $childName = null;
            }

            if (!isset($relations[$name])) {
                $relation = $model->getRelation($name);
                $relation->primaryModel = null;
                $relations[$name] = $relation;
            } else {
                $relation = $relations[$name];
            }

            if (isset($childName)) {
                $relation->with[$childName] = $callback;
            } elseif ($callback !== null) {
                call_user_func($callback, $relation);
            }
        }

        return $relations;
    }


    public function xyPopulateRelation($name, &$primaryModels)
    {
        if (!is_array($this->link)) {
            throw new InvalidConfigException('Invalid link: it must be an array of key-value pairs.');
        }

        if ($this->via instanceof self) {
            // via junction table
            /* @var $viaQuery ActiveRelationTrait */
            $viaQuery = $this->via;
            $viaModels = $viaQuery->findJunctionRows($primaryModels);
            $this->filterByModels($viaModels);
        } elseif (is_array($this->via)) {
            // via relation
            /* @var $viaQuery ActiveRelationTrait|ActiveQueryTrait */
            list($viaName, $viaQuery) = $this->via;
            if ($viaQuery->asArray === null) {
                // inherit asArray from primary query
                $viaQuery->asArray($this->asArray);
            }
            $viaQuery->primaryModel = null;
            $viaModels = $viaQuery->populateRelation($viaName, $primaryModels);
            $this->filterByModels($viaModels);
        } else {
            $this->xyFilterByModels($primaryModels);
        }

        if (!$this->multiple && count($primaryModels) === 1) {
            $model = $this->xyOne();
            foreach ($primaryModels as $i => $primaryModel) {
                if ($primaryModel instanceof ActiveRecordInterface) {
                    $primaryModel->populateRelation($name, $model);
                } else {
                    $primaryModels[$i][$name] = $model;
                }
                if ($this->inverseOf !== null) {
                    $this->populateInverseRelation($primaryModels, [$model], $name, $this->inverseOf);
                }
            }

            return [$model];
        } else {
            // https://github.com/yiisoft/yii2/issues/3197
            // delay indexing related models after buckets are built
            $indexBy = $this->indexBy;
            $this->indexBy = null;
            $models = $this->XyAll();

            if (isset($viaModels, $viaQuery)) {
                $buckets = $this->xyBuildBuckets($models, $this->link, $viaModels, $viaQuery->link);
            } else {
                $buckets = $this->xyBuildBuckets($models, $this->link);
            }

            $this->indexBy = $indexBy;
            if ($this->indexBy !== null && $this->multiple) {
                $buckets = $this->indexBuckets($buckets, $this->indexBy);
            }

            $link = array_values(isset($viaQuery) ? $viaQuery->link : $this->link);
            foreach ($primaryModels as $i => $primaryModel) {
                if ($this->multiple && count($link) === 1 && is_array($keys = $primaryModel[reset($link)])) {
                    $value = [];
                    foreach ($keys as $key) {
                        $key = $this->xyNormalizeModelKey($key);
                        if (isset($buckets[$key])) {
                            if ($this->indexBy !== null) {
                                // if indexBy is set, array_merge will cause renumbering of numeric array
                                foreach ($buckets[$key] as $bucketKey => $bucketValue) {
                                    $value[$bucketKey] = $bucketValue;
                                }
                            } else {
                                $value = array_merge($value, $buckets[$key]);
                            }
                        }
                    }
                } else {
                    $key = $this->xyGetModelKey($primaryModel, $link);
                    $value = isset($buckets[$key]) ? $buckets[$key] : ($this->multiple ? [] : null);
                }
                if ($primaryModel instanceof ActiveRecordInterface) {
                    $primaryModel->populateRelation($name, $value);
                } else {
                    $primaryModels[$i][$name] = $value;
                }
            }
            if ($this->inverseOf !== null) {
                $this->populateInverseRelation($primaryModels, $models, $name, $this->inverseOf);
            }

            return $models;
        }
    }

    public function xyFilterByModels($models)
    {
        $attributes = array_keys($this->link);

        $attributes = $this->xyPrefixKeyColumns($attributes);

        $values = [];
        if (count($attributes) === 1) {
            // single key
            $attribute = reset($this->link);
            foreach ($models as $model) {
                if (($value = $model[$attribute]) !== null) {
                    if (is_array($value)) {
                        $values = array_merge($values, $value);
                    } else {
                        $values[] = $value;
                    }
                }
            }
            if (empty($values)) {
                $this->emulateExecution();
            }
        } else {
            // composite keys

            // ensure keys of $this->link are prefixed the same way as $attributes
            $prefixedLink = array_combine(
                $attributes,
                array_values($this->link)
            );
            foreach ($models as $model) {
                $v = [];
                foreach ($prefixedLink as $attribute => $link) {
                    $v[$attribute] = $model[$link];
                }
                $values[] = $v;
                if (empty($v)) {
                    $this->emulateExecution();
                }
            }
        }
        $this->andWhere(['in', $attributes, array_unique($values, SORT_REGULAR)]);
    }


    public function xyPrefixKeyColumns($attributes)
    {

        if ($this instanceof ActiveQuery && (!empty($this->join) || !empty($this->joinWith))) {
            if (empty($this->from)) {
                /* @var $modelClass ActiveRecord */
                $modelClass = $this->modelClass;
                $alias = $modelClass::tableName();
            } else {
                foreach ($this->from as $alias => $table) {
                    if (!is_string($alias)) {
                        $alias = $table;
                    }
                    break;
                }
            }
            if (isset($alias)) {
                foreach ($attributes as $i => $attribute) {
                    $attributes[$i] = "$alias.$attribute";
                }
            }
        }
        return $attributes;
    }

    public function xyBuildBuckets($models, $link, $viaModels = null, $viaLink = null, $checkMultiple = true)
    {
        if ($viaModels !== null) {
            $map = [];
            $viaLinkKeys = array_keys($viaLink);
            $linkValues = array_values($link);
            foreach ($viaModels as $viaModel) {
                $key1 = $this->xyGetModelKey($viaModel, $viaLinkKeys);
                $key2 = $this->xyGetModelKey($viaModel, $linkValues);
                $map[$key2][$key1] = true;
            }
        }

        $buckets = [];
        $linkKeys = array_keys($link);

        if (isset($map)) {
            foreach ($models as $model) {
                $key = $this->xyGetModelKey($model, $linkKeys);
                if (isset($map[$key])) {
                    foreach (array_keys($map[$key]) as $key2) {
                        $buckets[$key2][] = $model;
                    }
                }
            }
        } else {
            foreach ($models as $model) {
                $key = $this->xyGetModelKey($model, $linkKeys);
                $buckets[$key][] = $model;
            }
        }

        if ($checkMultiple && !$this->multiple) {
            foreach ($buckets as $i => $bucket) {
                $buckets[$i] = reset($bucket);
            }
        }

        return $buckets;
    }
    public function xyGetModelKey($model, $attributes)
    {
        $key = [];
        foreach ($attributes as $attribute) {
            $key[] = $this->xyNormalizeModelKey($model[$attribute]);
        }
        if (count($key) > 1) {
            return serialize($key);
        }
        $key = reset($key);
        return is_scalar($key) ? $key : serialize($key);
    }
    public function xyNormalizeModelKey($value)
    {
        if (is_object($value) && method_exists($value, '__toString')) {
            // ensure matching to special objects, which are convertable to string, for cross-DBMS relations, for example: `|MongoId`
            $value = $value->__toString();
        }
        return $value;
    }
    private function xyRemoveDuplicatedModels($models)
    {
        $hash = [];
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();

        if (count($pks) > 1) {
            // composite primary key
            foreach ($models as $i => $model) {
                $key = [];
                foreach ($pks as $pk) {
                    if (!isset($model[$pk])) {
                        // do not continue if the primary key is not part of the result set
                        break 2;
                    }
                    $key[] = $model[$pk];
                }
                $key = serialize($key);
                if (isset($hash[$key])) {
                    unset($models[$i]);
                } else {
                    $hash[$key] = true;
                }
            }
        } elseif (empty($pks)) {
            throw new InvalidConfigException("Primary key of '{$class}' can not be empty.");
        } else {
            // single column primary key
            $pk = reset($pks);
            foreach ($models as $i => $model) {
                if (!isset($model[$pk])) {
                    // do not continue if the primary key is not part of the result set
                    break;
                }
                $key = $model[$pk];
                if (isset($hash[$key])) {
                    unset($models[$i]);
                } elseif ($key !== null) {
                    $hash[$key] = true;
                }
            }
        }

        return array_values($models);
    }
}