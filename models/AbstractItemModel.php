<?php

namespace app\modules\catalog\models;

use yii\db\ActiveRecord;
use Yii;

abstract class AbstractItemModel extends ActiveRecord
{
    const ACTIVE_STATUS = 1;
    const NOT_ACTIVE_STATUS = 0;

    public function getActiveStatus() {
        return static::ACTIVE_STATUS;
    }

    public function getNotActiveStatus() {
        return static::NOT_ACTIVE_STATUS;
    }

}
