<?php

/* @var $this \humhub\components\View */
/* @var $task \humhub\modules\task\models\Task */

/* @var $contentContainer \humhub\modules\content\components\ContentContainerActiveRecord */

use yii\helpers\Html;
use humhub\widgets\MarkdownView;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\modules\task\widgets\TaskItemList;
use humhub\widgets\Button;
use humhub\modules\task\models\Task;

\humhub\modules\task\assets\Assets::register($this);

$isResponsible = $task->isTaskResponsible();
$printUrl = $contentContainer->createUrl('print', ['id' => $task->id]);
$shareLink = $contentContainer->createUrl('share', ['id' => $task->id]);

// todo --> change in controller
$actionUrl = '#';

$collapse = true;

$this->registerJsConfig('task', [
    'text' => [
        'success.notification' => Yii::t('TaskModule.views_index_task', 'Task Users have been notified')
    ]
]);
$editUrl = $contentContainer->createUrl('edit', ['id' => $task->id]);


?>
<div id="task-container" class="panel panel-default task-details">

    <?= $this->render('@task/views/index/task_header', [
        'canEdit' => $isResponsible,
        'contentContainer' => $contentContainer,
        'task' => $task
    ]); ?>

    <div class="panel-body">

        <?php if (!empty($task->description)) : ?>
            <div style="display:inline-block;">
                <em><strong><?= Yii::t('TaskModule.views_index_index', 'Description') ?>:</strong></em><br>
                <div <?= ($collapse) ? 'data-ui-show-more' : '' ?>
                        data-read-more-text="<?= Yii::t('TaskModule.views_entry_view', 'Read full description...') ?>"
                        style="overflow:hidden">
                    <?= MarkdownView::widget(['markdown' => $task->description]); ?>
                </div>
            </div>
            <hr>
        <?php endif; ?>

        <?php if ($task->hasItems()) : ?>
            <em><strong><?= Yii::t('TaskModule.views_index_index', 'Checklist') ?>:</strong></em><br>
            <?php
//            Html::beginForm($contentContainer->createUrl('/task/index/confirm', ['taskID' => $task->id]));
            ?>


            <?= TaskItemList::widget(['task' => $task, 'canEdit' => $isResponsible]) ?>


            <?php
//            Html::endForm();
            ?>

            <hr>
        <?php endif; ?>

        <?php if ($task->canSeeStatusButton()) : ?>
            <?php if ($task->isPendingReview()) : ?>
                <?= Button::danger($task->getRejectReviewLabel())->link($task->getRejectReviewLink())->sm()->icon('fa-ban')->loader(true); ?>
            <?php endif; ?>
            <?= Button::success($task->getStatusLabel())->link($task->getStatusLink())->sm()->icon('fa-check-square')->loader(true); ?>
            <hr>
        <?php endif; ?>

        <?php if ($task->content->canView()) : // If the task is private and non space members are invited the task is visible, but not commentable etc. ?>
            <!--            <hr style="margin-bottom: 0;"> -->
            <?= WallEntryAddons::widget([
                'object' => $task
            ]); ?>
        <?php else: ?>
            <br>
        <?php endif; ?>
    </div>
</div>


