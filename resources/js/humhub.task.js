/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
humhub.module('task', function (module, require, $) {

    var Widget = require('ui.widget').Widget;
    var object = require('util').object;
    var client = require('client');
    var loader = require('ui.loader');
    var modal = require('ui.modal');

    var Item = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Item, Widget);

    Item.prototype.init = function () {
        this.$timeRange = this.$.find('.task-item-time-range');

        var that = this;

        if (this.parent().options.canEdit) {
            this.$.on('mouseover', function () {
                that.$.find('.task-item-dropdown-menu').show();
                if(that.$.siblings('li').length > 0 && that.$.find('.legacyFlag').length == 0) {
                    that.$.find('.task-drag-icon').show();
                }
            }).on('mouseout', function () {
                that.$.find('.task-item-dropdown-menu').hide();
                that.$.find('.task-drag-icon').hide();
            });
        }
    };

    Item.prototype.isFirst = function () {
        return (this.$.prev('li').length > 0);
    };

    Item.prototype.index = function () {
        return this.$.index();
    };

    Item.prototype.setData = function (itemData) {
        this.$timeRange.text(itemData.time);
        this.options.sortOrder = itemData.sortOrder;
        this.$.attr('data-sort-order', itemData.sortOrder);
    };

    Item.prototype.loader = function (show) {
        if (show === false) {
            loader.reset(this.$timeRange);
        } else {
            loader.set(this.$timeRange, {
                'position': 'left',
                'size': '8px',
                'css': {padding: '0px', 'margin-left': '-18px'}
            });
        }
    };

    var ItemList = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(ItemList, Widget);

    ItemList.prototype.init = function () {
        var that = this;
        debugger;
        if (this.options.canEdit && this.$.find('li[data-item-id]').length > 1 && this.$.find('.legacyFlag').length == 0) {
            this.$.imagesLoaded(function() {
                that.initSortableList();
            });
        }

        this.updateViewByItemOrder();
    };

    ItemList.prototype.initSortableList = function (evt) {
        var that = this;
        this.$.sortable({
            create: function () {
                jQuery(this).height(jQuery(this).height());
            },
            revert: 50,
            update: function (evt, ui) {
                var item = Item.instance(ui.item);

                var data = {
                    'ItemDrop[taskId]': that.options.taskId,
                    'ItemDrop[itemId]': item.options.itemId,
                    'ItemDrop[index]': item.index()
                };

                item.loader();
                client.post(that.options.dropUrl, {data: data}).then(function (response) {
                    if (response.success) {
                        that.updateItems(response.items);
                    } else {
                        module.log.error(err, true);
                        that.cancelDrop();
                    }
                }).catch(function (err) {
                    module.log.error(err, true);
                    that.cancelDrop();
                }).finally(function () {
                    item.loader(false);
                });
            },
            stop: function () {
                that.updateViewByItemOrder();
            }
        });
        this.$.disableSelection();
    };

    Item.prototype.moveDown = function (evt) {
        var test = this.$.next();
        this.$.before(this.$.next());
        // call sortable of parent ItemList
        var itemList = this.parent();
        itemList.$.sortable('option', 'update')(null, {item: this.$});
        itemList.updateViewByItemOrder();
    };

    Item.prototype.moveUp = function (evt) {
        var test = this.$.next();
        this.$.after(this.$.prev());

        var itemList = this.parent();
        itemList.$.sortable('option', 'update')(null, {item: this.$});
        itemList.updateViewByItemOrder();
    };

    ItemList.prototype.updateViewByItemOrder = function () {
        var buttomLine = this.$.find('.buttom-line').show();
        buttomLine.last().hide();

        var $moveDownButtons = this.$.find('.task-item-move-down').show();
        $moveDownButtons.last().hide();

        var $moveDownButtons = this.$.find('.task-item-move-up').show();
        $moveDownButtons.first().hide();
    };

    ItemList.prototype.getItems = function () {
        var result = [];

        this.$.find("[data-item-id]").each(function () {
            result.push(Item.instance(this));
        });

        return result;
    };

    ItemList.prototype.updateItems = function (items) {
        $.each(items, function (itemId, item) {
            var itemInst = Item.instance($('[data-item-id="' + itemId + '"]'));
            itemInst.setData(item);
        });
    };

    ItemList.prototype.cancelDrop = function () {
        this.$.sortable('cancel');
        this.updateViewByItemOrder();
    };

    ItemList.prototype.disableDrop = function () {
        this.$.sortable('disable');
    };

    ItemList.prototype.enableDrop = function () {
        this.$.sortable('enable');
    };

    var sendNotification = function (evt) {
        client.post(evt).then(function (response) {
            if (response.success) {
                module.log.success('success.notification', true);
            }
        });
    };

    var TaskFilter = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(TaskFilter, Widget);

    TaskFilter.prototype.getDefaultOptions = function () {
        return {
            'delay': 200
        };
    };

    TaskFilter.prototype.init = function () {
        this.$titleFilter = this.$.find('#taskfilter-title');
        this.$entryContainer = $('#filter-tasks-list');
        var that = this;

        this.$titleFilter.on('keyup', function (evt) {
            if (that.title() !== that.lastTitleSearch) {
                if (that.request) {
                    clearTimeout(that.request);
                }

                that.request = setTimeout($.proxy(that.filterCall, that), that.options.delay);
            }
        });

        this.$.find('.checkbox').on('change', function () {
            that.filterCall();
        });

        this.$entryContainer.on('click', '.pagination-container a', function (evt) {
            evt.preventDefault();
            that.filterCall($(this).attr('href'));
        });
    };

    TaskFilter.prototype.filterCall = function (url) {
        var that = this;
        this.lastTitleSearch = this.title();
        this.loader();

        url = url || this.$.attr('action');

        // Note: the additional empty objects are given due an bug in v1.2.1 fixed in v1.2.2
        client.submit(this.$, {url: url}).then(function (response) {
            if (response.success) {
                that.$entryContainer.html(response.output);
            }
        }).catch(function (err) {
            module.log.error(err, true);
        }).finally(function () {
            that.loader(false);
        });

    };

    TaskFilter.prototype.loader = function (show) {
        var $node = $('#task-filter-loader');

        if (show === false) {
            loader.reset($node);
        } else {
            loader.set($node, {
                'position': 'left',
                'size': '8px',
                'css': {padding: '0px'}
            });
        }
    };

    TaskFilter.prototype.title = function () {
        return this.$titleFilter.val();
    };

    var deleteTask = function(evt) {
        var streamEntry = Widget.closest(evt.$trigger);
        streamEntry.loader();
        modal.confirm().then(function() {
            modal.post(evt).then(function() {
                modal.global.close();
            }).catch(function(e) {
                module.log.error(e, true);
            });
        });

    };

    var editTask = function (evt) {
        var that = this;
        var streamEntry = Widget.closest(evt.$trigger);
        streamEntry.loader();
        modal.load(evt).catch(function (e) {
            module.log.error(e, true);
        });
    };

    module.export({
        ItemList: ItemList,
        Item: Item,
        deleteTask: deleteTask,
        editTask:editTask,
        sendNotification: sendNotification,
        TaskFilter: TaskFilter
    });
})
;
