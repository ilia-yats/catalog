<?php

namespace app\modules\catalog;

use yii\helpers\Html;
use yii\helpers\Json;
use talma\widgets\JsTree;
use talma\widgets\JsTreeAsset;

/**
 * Corrected implementation of JsTree widget
 *
 * Extends class [[JsTree]] from Yii 2 JsTree plugin
 * (
 *      download from:  https://github.com/thiagotalma/yii2-jstree-widget
 *      composer:       php composer.phar require --prefer-dist thiagotalma/yii2-jstree "~1.0.0"
 * )
 */
class CatalogJsTree extends JsTree
{
    public $additionalOptions = [];

    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $view = $this->getView();
        JsTreeAsset::register($view);

        $config = [
            'core' => array_merge(['data' => $this->data], $this->core),
            'plugins' => $this->plugins,
        ];

        $config = array_merge($config, $this->additionalOptions);

        $defaults = Json::encode($config);

        $inputId = (!$this->hasModel()) ? $this->options['id'] : Html::getInputId($this->model, $this->attribute);

        $js = <<<SCRIPT
;(function($, window, document, undefined) {
    $('#jsTree_{$this->options['id']}').jstree({$defaults});
})(window.jQuery, window, document);
SCRIPT;
        $view->registerJs($js);
    }
}
