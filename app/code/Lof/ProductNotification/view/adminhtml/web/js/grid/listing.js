define([
    'Magento_Ui/js/grid/listing'
], function (Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            editorConfig: {
                component: 'Lof_ProductNotification/js/grid/editing/editor'
            }
        }
    });
});