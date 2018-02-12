<?php
use humhub\modules\task\models\Task;

?>

<?php if ($task->status == Task::STATUS_PENDING) : ?>
    <div id="taskStatus" class="label label-default <?= $right ? 'pull-right' : '' ?>"><?= '<i class="fa fa-info-circle"></i> ' . Yii::t('TaskModule.views_index_index', 'Pending'); ?></div>
<?php elseif ($task->status == Task::STATUS_IN_PROGRESS) : ?>
    <div id="taskStatus" class="label label-info <?= $right ? 'pull-right' : '' ?>"><?= '<i class="fa fa-edit"></i> ' . Yii::t('TaskModule.views_index_index', 'In Progress'); ?></div>
<?php elseif ($task->status == Task::STATUS_PENDING_REVIEW) : ?>
    <div id="taskStatus" class="label label-warning <?= $right ? 'pull-right' : '' ?>"><?= '<i class="fa fa-exclamation-triangle"></i> ' . Yii::t('TaskModule.views_index_index', 'Pending Review'); ?></div>
<?php elseif ($task->status == Task::STATUS_COMPLETED) : ?>
    <div id="taskStatus" class="label label-success <?= $right ? 'pull-right' : '' ?>"><?= '<i class="fa fa-check-square"></i> ' . Yii::t('TaskModule.views_index_index', 'Completed'); ?></div>
<?php endif; ?>