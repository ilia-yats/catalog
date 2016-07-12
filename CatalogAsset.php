<?php

namespace app\modules\catalog;

use yii\web\AssetBundle;

class CatalogAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/catalog/assets';
    public $css = [
        'css/category_tree.css',
    ];
    public $js = [
        'js/category_tree.js',
    ];
    public $depends = [
        'app\assets\AppAsset',
    ];
    public $publishOptions = [
        'forceCopy'=>true,
    ];
}