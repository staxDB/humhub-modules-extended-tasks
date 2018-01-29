humhub.module('task.picker', function(module, require, $) {
    var object = require('util').object;
    var Picker = require('ui.picker').Picker;

    var TaskPicker = function(node, options) {
        Picker.call(this, node, options);
    };

    object.inherits(TaskPicker, Picker);

    module.export({
        TaskPicker: TaskPicker,
    });
});
