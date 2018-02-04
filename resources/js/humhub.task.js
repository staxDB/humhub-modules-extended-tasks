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

        this.$titleFilter.on('keypress', function (evt) {
            if (evt.keyCode == 13) {
                evt.preventDefault();
            }
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

        this.$.find('.field-taskfilter-status').on('change', function () {
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



    var Form = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Form, Widget);

    Form.prototype.init = function() {
        // modal.global.$.find('.tab-basic').on('shown.bs.tab', function (e) {
        //     $('#task-title').focus();
        // });

        // this.initDateInput();
        this.initTimeInput();
        this.initScheduling();
    };

    // Form.prototype.initDateInput = function(evt) {
    //     $dateFields = modal.global.$.find('.dateField');
    //     $dateInputs =  $timeFields.find('.form-control');
    //     $dateInputs.each(function() {
    //         var $this = $(this);
    //         if($this.prop('disabled')) {
    //             $this.data('oldVal', $this.val()).val('');
    //         }
    //     });
    // };

    Form.prototype.initTimeInput = function(evt) {
        $timeFields = modal.global.$.find('.timeField');
        $timeInputs =  $timeFields.find('.form-control');
        $timeInputs.each(function() {
            var $this = $(this);
            if($this.prop('disabled')) {
                $this.data('oldVal', $this.val()).val('');
            }
        });
    };

    Form.prototype.initScheduling = function(evt) {
        $schedulingTab = modal.global.$.find('.tab-scheduling');
        $checkBox = modal.global.$.find('#task-scheduling');
        if($checkBox.prop('checked')) {
            $schedulingTab.show();
        } else {
            $schedulingTab.hide();
        }
    };

    Form.prototype.toggleScheduling = function(evt) {
        $schedulingTab = modal.global.$.find('.tab-scheduling');
        if (evt.$trigger.prop('checked')) {
            $schedulingTab.show();
        } else {
            $schedulingTab.hide();
        }
    };

    Form.prototype.toggleDateTime = function(evt) {
        $timeFields = modal.global.$.find('.timeField');
        $timeInputs =  $timeFields.find('.form-control');
        if (evt.$trigger.prop('checked')) {
            $timeInputs.prop('disabled', true);
            $timeInputs.each(function() {
                $(this).data('oldVal', $(this).val()).val('');
            });
            $timeFields.css('opacity', '0.2');
        } else {
            $timeInputs.each(function() {
                $this = $(this);
                if($this.data('oldVal')) {
                    $this.val($this.data('oldVal'));
                }
            });
            $timeInputs.prop('disabled', false);
            $timeFields.css('opacity', '1.0');
        }
    };

    Form.prototype.removeTaskItem = function (evt) {
        evt.$trigger.closest('.form-group').remove();
    };

    Form.prototype.addTaskItem = function (evt) {
        var $this = evt.$trigger;
        $this.prev('input').tooltip({
            html: true,
            container: 'body'
        });

        var $newInputGroup = $this.closest('.form-group').clone(false);
        var $input = $newInputGroup.find('input');

        $input.val('');
        $newInputGroup.hide();
        $this.closest('.form-group').after($newInputGroup);
        $this.children('span').removeClass('glyphicon-plus').addClass('glyphicon-trash');
        $this.off('click.humhub-action').on('click', function () {
            $this.closest('.form-group').remove();
        });
        $this.removeAttr('data-action-click');
        $newInputGroup.fadeIn('fast');
    };


    var Item = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Item, Widget);

    Item.prototype.init = function () {
        var that = this;
    };

    Item.prototype.loader = function (show) {
        debugger;
    };

    Item.prototype.confirm = function (submitEvent) {
        this.update(client.submit(submitEvent));
    };

    Item.prototype.update = function (update) {
        this.loader();
        update.then($.proxy(this.handleUpdateSuccess, this))
            .catch(Item.handleUpdateError)
            // .catch()
            .finally($.proxy(this.loader, this, false));
    };

    Item.prototype.handleUpdateSuccess = function (response) {

        // if (this.$.find("#taskStatus").text() === "Pending") {
        //     client.reload();
        // }
        // var streamEntry = this.streamEntry();
        // return streamEntry.replace(response.output).then(function () {
        // this.loader();
        module.log.success('success.saved');
        // });
    };

    Item.handleUpdateError = function (e) {
        module.log.error(e.message, true);
        // module.log.error(e, true);
    };

    var ItemList = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(ItemList, Widget);

    ItemList.prototype.init = function () {

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



    module.export({
        ItemList: ItemList,
        Item: Item,
        deleteTask: deleteTask,
        editTask:editTask,
        sendNotification: sendNotification,
        TaskFilter: TaskFilter,
        Form: Form
    });
})
;
