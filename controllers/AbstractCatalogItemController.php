<?php

namespace app\modules\catalog\controllers;

use yii\web\Controller;

abstract class AbstractCatalogItemController extends Controller
{
    // Layout of pages loaded in iframes
    public $layout = '@app/modules/catalog/views/layouts/catalogItemsView';

    abstract public function actionGetInCategory($categoryId);
}