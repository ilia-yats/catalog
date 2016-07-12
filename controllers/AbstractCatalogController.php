<?php

namespace app\modules\catalog\controllers;

use app\modules\catalog\CatalogJsTree;
use app\modules\shop\controllers\GoodController;
use Yii;
use yii\web\JsExpression;
use yii\web\View;
use yii\web\Response;
use yii\base\Exception;
use yii\web\Controller;
use app\modules\catalog\CatalogAsset;
use app\modules\catalog\models\AbstractCategoryModel;
use app\modules\catalog\models\AbstractItemModel;

/**
 * Class AbstractCatalogController
 *
 * Basic class, which provides catalog functional.
 * To implement catalog in your module:
 *  extend this class and implement [[catalogInit]] method, in which set up all public properties
 *  (some of them are set with default values, but may be overwritten),
 *
 *  set up the instances of 'category' and 'item' models, which must extend [[AbstractCategoryModel]] and [[AbstractItemModel]].
 *
 *  example:
 *  ```php
 *
 *      class ShopCatalogController extends AbstractCatalogController
 *      {
 *          public function initCatalog()
 *          {
 *              $this->categoryModel = new Category();
 *              $this->catalogItemModel = new Good();
 *              $this->categoriesControllerUrl = 'shop/category';
 *              $this->itemsControllerUrl = 'shop/good';
 *              ...
 *          }
 *      }
 *  ```
 *
 * @package app\modules\catalog\controllers
 */
abstract class AbstractCatalogController extends Controller
{
    // Models
    /**
     * @var AbstractCategoryModel
     */
    public $categoryModel;

    /**
     * @var AbstractItemModel
     */
    public $catalogItemModel;


    // Routes/Urls
    /**
     * route of action, which handle all changes in tree of categories
     * @var string
     */
    public $treeActionsHandlerUrl;

    /**
     * route of action, which provides JSON data for building tree of categories
     * @var string
     */
    public $treeSourceUrl;

    /**
     * route to items controller
     * @var string
     */
    public $itemsControllerUrl;

    /**
     * route to categories controller
     * @var string
     */
    public $categoriesControllerUrl;


    // Tree widget
    /**
     * content of tree widget
     * @var string
     */
    public $treeWidget;

    /**
     * additional options for tree widget
     * @var array
     */
    public $jsTreeAdditionalOptions = [];

    /**
     * widget name
     * @var string
     */
    public $widgetName = 'js_tree';

    /**
     * widget themes
     * @var array
     */
    public $widgetThemes = ["stripes" => TRUE];

    /**
     * widget plugins
     * @var array
     */
    public $widgetPlugins = [
//        "contextmenu", // TODO: handle context menu actions
        "dnd",
        "massload",
        "search",
        "sort",
        "state",
        "types",
        "unique",
        "wholerow",
        "changed",
        "conditionalselect",
    ];

    // Database key fields names
    /**
     * primary key of item's table
     * @var string
     */
    public $itemPkField = 'id';

    /**
     * foreign key from item's table to category's table
     *  (if item's table has not straight foreign key on category's table,
     *  solve this problem in [[itemController]] in method [[actionGetInCategory]]:
     *  receive the id of category by the name specified in this parameter and run needed request to needed table/model)
     *
     * @var string
     */
    public $itemToCategoryFkField = 'category_id';

    /**
     * primary key of category's table
     * @var string
     */
    public $categoryPkField = 'id';

    /**
     * status flag field in category's table
     * @var string
     */
    public $categoryActivityField = 'status';

    /**
     * foreign key from category's table to category's table (to parent category)
     * @var string
     */
    public $categoryToParentFkField = 'parent_id';


    // Main js handler function name
    /**
     * name of main js function to pass in widget
     * @var string
     */
    public $treeStructureChangeable = 'true';


    // Paths
    /**
     * @var string
     */
    public $viewPath = '@app/modules/catalog/views/index';

    /**
     * @var string
     */
    public $cssPath = 'assets/css/category_tree.css';

    /**
     * @var string
     */
    public $jsPath = 'assets/js/category_tree.js';


    /**
     * In this method you should initialize instances of [[$categoryModel]] and [[$itemModel]]
     *  as Models extended from AbstractCategoryModel and AbstractItemModel;
     *
     * Also, you should set all needed options in [[$options]] array
     */
    abstract public function initCatalog();

    /**
     * @throws Exception
     */
    public function init()
    {
        // Set up properties in user defined method
        $this->initCatalog();

        // If treeWidget wasn't specified by user in [[initCatalog]] method, create default widget
        if( ! $this->treeWidget) {
            $this->treeWidget = CatalogJsTree::widget([
                'name'              => $this->widgetName, // 'js_tree',
                'additionalOptions' => $this->jsTreeAdditionalOptions,
                'core'              => [
                    'check_callback' => new JsExpression($this->treeStructureChangeable),
                    'data'           => ['url' => $this->treeSourceUrl],
                    "themes"         => $this->widgetThemes,
                ],
                'plugins'           => $this->widgetPlugins,
            ]);
        }

        // Check if needed properties has been set up
        if( ! $this->categoryModel instanceof AbstractCategoryModel) {
            throw new Exception('Not valid category model in catalog module');
        }
        if( ! $this->catalogItemModel instanceof AbstractItemModel) {
            throw new Exception('Not valid item model in catalog module');
        }
    }

    /**
     * Overrides parent method in order to register needed scripts in view
     *
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render($view, $params = [])
    {
        $this->view->registerJs("
            var protoCatalog = {
                itemsControllerUrl: '{$this->itemsControllerUrl}',
                itemPkField: '{$this->itemPkField}',
                itemToCategoryFkField: '{$this->itemToCategoryFkField}',
                categoriesControllerUrl: '{$this->categoriesControllerUrl}',
                categoryPkField: '{$this->categoryPkField}',
                categoryToParentFkField: '{$this->categoryToParentFkField}',
                treeActionsHandlerUrl: '{$this->treeActionsHandlerUrl}',
                treeSourceUrl: '{$this->treeSourceUrl}',
            }",
            View::POS_HEAD
        );
        CatalogAsset::register($this->view);

        return parent::render($view, $params);
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render($this->viewPath, ['treeWidget' => $this->treeWidget]);
    }

    /**
     * @return string JSON
     */
    public function actionTreeSource()
    {
        $result = [];

        $categories = $this->categoryModel->getAll();

        foreach($categories as $i => $category) {
            $table = [
                'id'      => $category[$this->categoryPkField],
                'parent'  => $category[$this->categoryToParentFkField] ? $category[$this->categoryToParentFkField] : '#',
                // TODO: solve how to get multi language name
                'text'    => $category['name'],
                'icon'    => '',
                'state'   => [
                    'opened'   => FALSE,
                    'disabled' => FALSE,
                    'selected' => FALSE,
                ],
                'li_attr' => ($category[$this->categoryActivityField] == 0) ? ['class' => 'deactivated_category'] : [],
                'a_attr'  => [],
            ];

            $result[] = $table;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        return $result;
    }

    /**
     * @return string JSON
     */
    public function actionTreeActionsAjaxHandler()
    {
        $request = Yii::$app->request;
        $response = ['status' => 0];

        if($request->isAjax && ($operation = $request->post('operation', FALSE))) {
            if($operation == 'rename_node') {
                if($category = $this->categoryModel->getOneByPrimaryKey($request->post('id', 0))) {
                    $category->name = $request->post('new_name', '');
                    $response['status'] = (int) $category->save();
                }
            } elseif($operation == 'create_node') {
                $category = new $this->categoryModel->className();
                // Parent category id may be absent, which means that new category should not have parent,
                $category->{$this->categoryToParentFkField} = $request->post('parent_id', NULL);
                $category->name = Yii::t('app', 'New node');
                $response['status'] = (int) $category->save();
            } elseif($operation == 'move_node') {
                if($category = $this->categoryModel->getOneByPrimaryKey($request->post('id', 0))) {
                    $parent_id = $request->post('parent_id', NULL);
                    if($parent_id == '#') {
                        $parent_id = NULL;
                    }
                    $category->{$this->categoryToParentFkField} = $parent_id;
                    $response['status'] = (int) $category->save();
                }
            } elseif($operation == 'delete_node') {
                if($categories = $this->categoryModel->getAllByPrimaryKeys($request->post('ids', 0))) {
                    $response['status'] = 1;
                    foreach($categories as $category) {
                        // TODO: Implement safe deletion with ability of restoring
                        if( ! $category->delete()) {
                            $response['status'] = 0;
                        }
                    }
                }
            } elseif($operation == 'copy_node') {
                $category = new $this->categoryModel->className();
                $category->{$this->categoryToParentFkField} = $request->post('parent_id', NULL);
                $category->name = $request->post('name', '');
                $response['status'] = (int) $category->save();

                // Next two actions can take array of ids and be applied to many items
            } elseif($operation == 'activate_category') {
                if($categories = $this->categoryModel->getAllByPrimaryKeys($request->post('ids', 0))) {
                    $response['status'] = 1;
                    foreach($categories as $category) {
                        $category->{$this->categoryActivityField} = $this->categoryModel->getActiveStatus();
                        if( ! $category->save()) {
                            $response['status'] = 0;
                        }
                    }
                }
            } elseif($operation == 'deactivate_category') {
                if($categories = $this->categoryModel->getAllByPrimaryKeys($request->post('ids', 0))) {
                    $response['status'] = 1;
                    foreach($categories as $category) {
                        $category->{$this->categoryActivityField} = $this->categoryModel->getNotActiveStatus();
                        if( ! $category->save()) {
                            $response['status'] = 0;
                        }
                    }
                }
            }
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        return $response;
    }
}