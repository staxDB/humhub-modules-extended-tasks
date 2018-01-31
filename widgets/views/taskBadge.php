<?php
use humhub\modules\task\models\Task;

?>

<?php if (/*$task->isToday()*/ false) : ?>
    <div class="label label-danger <?= $right ? 'pull-right' : '' ?>"><?= Yii::t('TaskModule.views_index_index', 'Today'); ?></div>
<?php elseif (/*$task->isTomorrow()*/ false) : ?>
    <div class="label label-warning <?= $right ? 'pull-right' : '' ?>"><?= Yii::t('TaskModule.views_index_index', 'Tomorrow'); ?></div>
<?php endif; ?>

<?php if ($task->status == Task::STATUS_OPEN) : ?>
    <div class="label label-info <?= $right ? 'pull-right' : '' ?>"><?= Yii::t('TaskModule.views_index_index', 'Open'); ?></div>
<?php elseif ($task->status == Task::STATUS_PENDING) : ?>
    <div class="label label-default <?= $right ? 'pull-right' : '' ?>"><?= Yii::t('TaskModule.views_index_index', 'Pending'); ?></div>
<?php elseif ($task->status == Task::STATUS_IN_PROGRESS) : ?>
    <div class="label label-info <?= $right ? 'pull-right' : '' ?>"><?= Yii::t('TaskModule.views_index_index', 'In Progress'); ?></div>
<?php elseif ($task->status == Task::STATUS_PENDING_REVIEW) : ?>
    <div class="label label-warning <?= $right ? 'pull-right' : '' ?>"><?= Yii::t('TaskModule.views_index_index', 'Pending Review'); ?></div>
<?php elseif ($task->status == Task::STATUS_COMPLETED) : ?>
    <div class="label label-success <?= $right ? 'pull-right' : '' ?>"><?= Yii::t('TaskModule.views_index_index', 'Completed'); ?></div>
<?php endif; ?>
