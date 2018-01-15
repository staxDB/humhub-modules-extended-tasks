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

\humhub\modules\task\assets\Assets::register($this);

$canEdit = $task->content->canEdit();
$createItemUrl = $contentContainer->createUrl('/task/item/edit', ['taskId' => $task->id]);
$printUrl = $contentContainer->createUrl('print', ['id' => $task->id]);
$shareLink = $contentContainer->createUrl('share', ['id' => $task->id]);

$this->registerJsConfig('task', [
    'text' => [
        'success.notification' => Yii::t('TaskModule.views_index_task', 'Task Users have been notified')
    ]
]);

$participantStyle = empty($task->location) ? 'display:inline-block;' :  'display:inline-block;padding-right:10px;border-right:2px solid '. $this->theme->variable('default');
$locationStyle = ($task->hasTaskUsers()) ? 'display:inline-block;padding-left:10px;vertical-align:top;' : 'display:inline-block;';

?>
<div id="task-container" class="panel panel-default task-details">
    <?= $this->render('@task/views/index/task_header', [
        'canEdit' => $canEdit,
        'contentContainer' => $contentContainer,
        'task' => $task
    ]); ?>
    <div class="panel-body">

        <?php if($task->hasTaskUsers()): ?>
        <div>
            <?php if ($task->hasTaskUsers()) : ?>
                <div style="<?= $participantStyle ?>">
                    <em><strong><?= Yii::t('TaskModule.views_index_index', 'Participants') ?>:</strong></em><br>
                    <?php foreach ($task->taskUserUsers as $user) : ?>
                        <a href="<?= $user->getUrl(); ?>">
                            <?= \humhub\modules\user\widgets\Image::widget(['user' => $user, 'width' => 24, 'showTooltip' => true]) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif ?>

            <hr>
        </div>
        <?php endif; ?>
        <?= TaskItemList::widget(['task' => $task, 'canEdit' => $canEdit]) ?>

        <?php if ($canEdit): ?>

            <div class="row">
                <div class="col-md-12 text-center">
                    <?php if (count($task->items) == 0) : ?>
                        <?= Yii::t('TaskModule.views_index_index', 'Create your first agenda entry by clicking the following button.'); ?>
                        <br>
                    <?php endif; ?>
                    <br>
                    <?= ModalButton::info(Yii::t('TaskModule.views_index_index', 'New subtask'))->id('task-agenda-create')->load($createItemUrl)->lg()->icon('fa-plus')?>
                    <br><br><br>
                </div>
            </div>

        <?php else: ?>
            <br><br>
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
