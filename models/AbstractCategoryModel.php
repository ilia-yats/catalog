<?php

namespace app\modules\catalog\models;

use Yii;
use yii\db\ActiveRecord;

abstract class AbstractCategoryModel extends ActiveRecord
{
    const ACTIVE_STATUS = 1;
    const NOT_ACTIVE_STATUS = 0;

    public function getActiveStatus() {
        return static::ACTIVE_STATUS;
    }

    public function getNotActiveStatus() {
        return static::NOT_ACTIVE_STATUS;
    }

    abstract public function getAll();
    abstract public function getOneByPrimaryKey($pk);
    abstract public function getAllByPrimaryKeys($pks);
}
