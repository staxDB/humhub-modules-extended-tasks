<?php

/* @var $this \humhub\components\View */
/* @var $task \humhub\modules\task\models\Task */
/* @var $contentContainer \humhub\modules\content\components\ContentContainerActiveRecord */

use humhub\modules\task\widgets\TaskItemList;
use humhub\modules\task\widgets\TaskItemWidget;
use humhub\widgets\ModalButton;
use yii\helpers\Html;
use humhub\modules\comment\widgets\CommentLink;
use humhub\modules\like\widgets\LikeLink;
use \humhub\modules\comment\widgets\Comments;
use humhub\widgets\MarkdownView;
use humhub\modules\content\widgets\WallEntryAddons;

\humhub\modules\task\assets\Assets::register($this);

$canEdit = $task->content->canEdit();
$createItemUrl = $contentContainer->createUrl('/task/item/edit', ['taskId' => $task->id]);
$printUrl = $contentContainer->createUrl('print', ['id' => $task->id]);
$shareLink = $contentContainer->createUrl('share', ['id' => $task->id]);


$collapse = true;
$renderAddons = true;

$this->registerJsConfig('task', [
    'text' => [
        'success.notification' => Yii::t('TaskModule.views_index_task', 'Task Users have been notified')
    ]
]);


?>
<div id="task-container" class="panel panel-default task-details">
    <?= $this->render('@task/views/index/task_header', [
        'canEdit' => $canEdit,
        'contentContainer' => $contentContainer,
        'task' => $task
    ]); ?>
    <div class="panel-body">

        <?php if (!empty($task->description)) : ?>
            <div style="display:inline-block;">
                <em><strong><?= Yii::t('TaskModule.views_index_index', 'Description') ?>:</strong></em><br>
                <div <?= ($collapse) ? 'data-ui-show-more' : '' ?> data-read-more-text="<?= Yii::t('TaskModule.views_entry_view', 'Read full description...') ?>" style="overflow:hidden">
                    <?= MarkdownView::widget(['markdown' => $task->description]); ?>
                </div>
            </div>
            <hr>
        <?php endif; ?>

        <?= TaskItemList::widget(['task' => $task, 'canEdit' => $canEdit]) ?>

        <?php if ($canEdit): ?>

            <div class="row">
                <div class="col-md-12 text-center">
                    <?php if (count($task->items) == 0) : ?>
                        <?= Yii::t('TaskModule.views_index_index', 'Add Checkpoints by clicking the following button.'); ?>
                        <br>
                    <?php endif; ?>
                    <br>
                    <?= ModalButton::info(Yii::t('TaskModule.views_index_index', 'Add checkpoint'))->id('task-agenda-create')->load($createItemUrl)->lg()->icon('fa-plus')?>
                    <br><br><br>
                </div>
            </div>

        <?php else: ?>
            <br><br>
        <?php endif; ?>

        <!-- wall-entry-addons class required since 1.2 -->
        <?php if($renderAddons) : ?>
            <div class="stream-entry-addons clearfix">
                <?= WallEntryAddons::widget(
                    ['object' => $task]
                ); ?>
            </div>
        <?php endif; ?>

        <?php if ($task->content->canView()) : // If the task is private and non space members are invited the task is visible, but not commentable etc. ?>
            <hr style="margin-bottom: 0;">

            <div class="row">
                <div class="col-md-12">
                    <div class="wall-entry">
                        <div class="wall-entry-controls">
                            <?= CommentLink::widget(['object' => $task]); ?>
                            · <?= LikeLink::widget(['object' => $task]); ?>
                        </div>
                    </div>
                    <?= Comments::widget(['object' => $task]); ?>
                </div>
            </div>
        <?php else: ?>
            <br>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">

    if (window.matchMedia('(min-width: 991px)').matches) {
        $('.item-protocol').mouseover(function () {
            $(this).find('.edit-link').show();
        }).mouseout(function () {
            $(this).find('.edit-link').hide();
        });

        $('.task-item-content').mouseover(function () {
            $(this).find('.edit-link').show();
        }).mouseout(function () {
            $(this).find('.edit-link').hide();
        });

        $('.task-information').mouseover(function () {
            $(this).find('.edit-link').show();
        }).mouseout(function () {
            $(this).find('.edit-link').hide();
        });
    }

</script>
