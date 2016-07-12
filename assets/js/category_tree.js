/**
 * Main script of Catalog module.
 * Create catalog
 *
 * @var protoCatalog
 */

/**
 * Catalog constructor.
 * This catalog prototype definition exists in controller
 *
 *    var protoCatalog = {
 *       itemsControllerUrl: '{$this->itemsControllerUrl}',
 *       itemPkField: '{$this->itemPkField}',
 *       itemToCategoryFkField: '{$this->itemToCategoryFkField}',
 *       categoriesControllerUrl: '{$this->categoriesControllerUrl}',
 *       categoryPkField: '{$this->categoryPkField}',
 *       categoryToParentFkField: '{$this->categoryToParentFkField}',
 *       treeActionsHandlerUrl: '{$this->treeActionsHandlerUrl}',
 *       treeSourceUrl: '{$this->treeSourceUrl}'
 *   };
 */
function Catalog() {

    // DOM block with catalog tree
    var treeBlock = $('[id^="jsTree_w"]');

    // Call this method in document-ready block
    this.init = function () {

        // Check all needed options
        if (!this.itemsControllerUrl
            || !this.itemPkField
            || !this.itemToCategoryFkField
            || !this.categoriesControllerUrl
            || !this.categoryPkField
            || !this.categoryToParentFkField
            || !this.treeActionsHandlerUrl
            || !this.treeSourceUrl
        ) {
            console.log('catalog settings were not configured correctly');
            return false;
        }

        // Preserve the context of created object
        var that = this;

        // Enable 'add category' button
        $('#add_category').prop('disabled', false);

        // Bind click handler (loads iframe with items belonged to clicked category)
        treeBlock.bind("changed.jstree", function (e, data, x) {
            // If at least one category is selected, enable action buttons and reload iframe
            if (data.selected) {

                // enable tree action buttons, which can be applied to any count of selected items (deletion)
                $('#delete_category').prop('disabled', false);

                // if only one certain category is selected (not multi-select), load category items in iframe
                if (data.selected.length == 1) {

                    var id = data.selected[0];

                    // enable tree actions buttons, which can be applied only to one selected item
                    //  (adding new sub-item and editing)
                    $('#edit_category').prop('disabled', false);
                    $('#add_category').prop('disabled', false);

                    // Enable activation or deactivation button depending on the current state of selected node
                    if ($('#' + id).hasClass('deactivated_category')) {
                        $('#deactivate_category').prop('disabled', true);
                        $('#activate_category').prop('disabled', false);
                    } else {
                        $('#deactivate_category').prop('disabled', false);
                        $('#activate_category').prop('disabled', true);
                    }

                    var itemsIframeSource = that.itemsControllerUrl + '/get-in-category?'
                        + that.itemToCategoryFkField + '=' + id;
                    $('.items_list').html('<iframe frameborder="0" src="' + itemsIframeSource + '" scrolling="auto"></iframe>');

                } else if (data.selected.length > 1) {  // else if more than one item was selected:
                    // disable adding and editing buttons
                    $('#edit_category').prop('disabled', true);
                    $('#add_category').prop('disabled', true);
                    // enable activation and deactivation buttons
                    $('#activate_category').prop('disabled', false);
                    $('#deactivate_category').prop('disabled', false);

                } else { // else, if no categories were selected
                    $('#edit_category').prop('disabled', true);
                    $('#delete_category').prop('disabled', true);
                }
            }
        });

        // Drag-n-drop handler
        treeBlock.bind("move_node.jstree", function (e, data) {
            var displacedNodeId = data.node.id;
            var newParentNodeId = data.parent;
            // Send action to the server
            that.actionsHandler(
                'move_node',
                {
                    id: displacedNodeId,
                    parent_id: newParentNodeId
                }
            );
        });

        // Tree buttons handlers
        $(document).on("click", '#activate_category', function () {
            var selectedNodesIds = treeBlock.jstree('get_selected');
            // Send action to the server
            that.actionsHandler(
                'activate_category',
                {
                    ids: selectedNodesIds
                },
                function () {
                    that.reload();
                }
            );
        });
        $(document).on("click", '#deactivate_category', function () {
            var selectedNodesIds = treeBlock.jstree('get_selected');
            // Send action to the server
            that.actionsHandler(
                'deactivate_category',
                {
                    ids: selectedNodesIds
                },
                function () {
                    that.reload();
                }
            );
        });
        $(document).on("click", '#delete_category', function () {
            var selectedNodesIds = treeBlock.jstree('get_selected');
            var confirmation = confirm("Вы действительно хотите удалить категорию?");
            if (confirmation === true) {
                console.log('deletion');
                treeBlock.jstree(true).delete_node(selectedNodesIds);
                // Send action to the server
                that.actionsHandler(
                    'delete_category',
                    {
                        ids: selectedNodesIds
                    },
                    function () {
                        that.reload();
                        // Clear items area
                        $('.items_list').html('');
                    }
                );
            }
        });
        $('#expand_category').click(function () {
            treeBlock.jstree('open_all');
        });
        $('#collapse_category').click(function () {
            treeBlock.jstree('close_all');
        });

        // Open modals when add or edit category
        $(document).on("click", '#edit_category', function () {
            var selectedNodesIds = treeBlock.jstree('get_selected');
            // if only one certain category was selected, process editing
            if (selectedNodesIds.length == 1) {
                // Show modal
                var categoryUpdateIframeSrc = that.categoriesControllerUrl + '/update?' + that.categoryPkField + '=' + selectedNodesIds[0];
                that.showModal('<iframe frameborder="0" src="' + categoryUpdateIframeSrc + '"></iframe>');
            }
        });
        $(document).on("click", '#add_category', function () {
            var selectedNodesIds = treeBlock.jstree('get_selected');
            var parentId = 0; // parent_id of new category (by default is 0, i.e. new category doesn't have parent)
            var categoryCreateIframeSrc = that.categoriesControllerUrl + '/create'; // Default src URL for iframe with 'category add' window
            // if only one certain category was selected, pass its id as parent_id of new category
            if (selectedNodesIds.length == 1) {
                parentId = selectedNodesIds[0];
            }
            // If parent id exists, add it to iframe source
            if (parentId > 0) {
                categoryCreateIframeSrc += '?' + that.categoryToParentFkField + '=' + parentId;
            }
            // Show modal
            that.showModal('<iframe frameborder="0" src="' + categoryCreateIframeSrc + '"></iframe>');
        });

        // Searching in tree
        var to = false;
        $('#search-category').keyup(function () {
            if (to) {
                clearTimeout(to);
            }
            to = setTimeout(function () {
                var v = $('#search-category').val();
                treeBlock.jstree(true).search(v);
            }, 250);
        });
    };

    this.showModal = function (content) {
        $('#catalog_modal_trigger').click();
        $('#catalog_modal').html(content);
    };

    this.closeModal = function () {
        $('#catalog_modal').html('');
        $('#catalog_modal_trigger').click();
    };

    // Controls if tree structure changes are enabled
    this.treeStructureChangeable = function (operation, node, node_parent, node_position, more) {
        return true;
    };

    this.showChangesPerformedStatus = function (data) {
        console.log(data);
        // TODO: Implement feedback
    };

    // Sends to server information about confirmed action in a tree (category deletion, deactivation etc.) and invoke callback
    this.actionsHandler = function (actionName, actionData, success_callback, error_callback) {

        // Preserve the context of created object
        var that = this;

        // Set operation name in data object
        actionData.operation = actionName;

        $.ajax({
            type: 'POST',
            url: that.treeActionsHandlerUrl,
            data: actionData,
            success: success_callback,
            error: error_callback
        });
    };

    this.reload = function () {
        treeBlock.jstree(true).refresh();
    };
}

// Set main settings from protoCatalog,
Catalog.prototype = protoCatalog;

// create catalog manager
var catalog = new Catalog();

// On document ready init catalog manager
$(function () {
    catalog.init();
});