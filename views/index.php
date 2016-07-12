<?php

use yii\bootstrap\Modal;

/* @var $treeWidget string  content of catalog tree widget */

$this->title = 'Управление категориями';

Modal::begin([
    'toggleButton' => ['id' => 'catalog_modal_trigger', 'style' => 'display:none'],
    'size' => 'modal-lg',
]);
echo '<div id="catalog_modal"></div>';
Modal::end();
?>

<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading cat_tree">
            <h3>
                <i class="fa fa-list"></i>
                Управление категориями
            </h3>
        </div>
        <div class="panel-body">
            <div class="container-fluid">
                <div class="left-pane">
                    <div id="left-pane-toolbar" style="height: 40px; width: 100%; padding-right: 6px;">
                        <button class="btn btn-sm btn-primary" disabled="disabled" title="Добавить категорию" id="add_category">
                            <i class="fa fa-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-primary" disabled="disabled" title="Редактировать категорию" id="edit_category">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" disabled='disabled' title="Удалить категорию" id="delete_category">
                            <i class="fa fa-trash-o"></i>
                        </button>
                        <button class="btn btn-sm btn-success" disabled='disabled' title="Активировать выбранную категорию" id="activate_category">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-success" disabled='disabled' title="Деактивировать выбранную категорию" id="deactivate_category">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                        <button class="btn btn-sm btn-primary" title="Развернуть каталог" id="expand_category">
                            <i class="fa fa-angle-double-down"></i>
                        </button>
                        <button class="btn btn-sm btn-primary" title="Свернуть каталог" id="collapse_category">
                            <i class="fa fa-angle-double-up"></i>
                        </button>
                    </div>
                    <div style="width: 100%; margin-bottom: 6px;">
                        <input style="display:table-cell; width:100%" id="search-category">
                    </div>

                    <?php echo $treeWidget; ?>

                </div>
                <div id="products-list">
                    <div class="bootstrap-table">
                        <div class="fixed-table-toolbar">
                        </div>
                        <div class="items_list">
                            <!-- Here iframe with items list will be loaded -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>