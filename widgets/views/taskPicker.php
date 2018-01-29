<?php

/**
 * This View replaces a input with an task picker
 *
 * @property String $inputId is the ID of the input HTML Element
 * @property Int $maxTasks the maximum of tasks for this input
 * @property String $taskSearchUrl the url of the search, to find the tasks
 * @property String $currentValue is the current value of the parent field.
 *
 * @package humhub.modules_core.task
 * @since 0.5
 * @deprecated since 1.2 use TaskPickerField instead.
 */
use \humhub\modules\task\models\Task;
use \yii\helpers\Html;

$this->registerJsFile("@web-static/js/jquery.highlight.min.js");
$this->registerJsFile("@web-static/resources/task/taskpicker.js");
?>

<?php
// Resolve guids to task tags
$newValue = "";

foreach (explode(",", $currentValue) as $guid) {
    $task = Task::findOne(['id' => trim($guid)]);
    if ($task != null) {
        $name = Html::encode($task->title);
        $newValue .= '<li class="taskInput" id="' . $task->id . '">' . $name . '<i class="fa fa-times-circle"></i></li>';
    }
}
?>

<script type="text/javascript">
    $(document).ready(function () {
        $('#<?php echo $inputId; ?>').taskpicker({
            inputId: '#<?php echo $inputId; ?>',
            maxTasks: '<?php echo $maxTasks; ?>',
            searchUrl: '<?php echo $taskSearchUrl; ?>',
            currentValue: '<?php echo $newValue; ?>',
            focus: '<?php echo $focus; ?>',
            taskId: '<?php echo $taskId; ?>',
            data: <?php echo $data ?>,
            placeholderText: '<?php echo $placeholderText; ?>'
        });
    });
</script>