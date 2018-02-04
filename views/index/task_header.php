<?php

use humhub\libs\Html;
use humhub\modules\task\widgets\TaskBadge;
use humhub\modules\task\widgets\TaskMenu;
use humhub\widgets\Button;
use humhub\widgets\TimeAgo;

/* @var $contentContainer \humhub\modules\content\components\ContentContainerActiveRecord */
/* @var $task \humhub\modules\task\models\Task */
/* @var $canEdit boolean */
/* @var $collapse boolean */

$editUrl = $contentContainer->createUrl('edit', ['id' => $task->id]);
//$icon = !$task->isToday() && $task->isPast() ? 'fa-calendar-check-o' : 'fa-calendar-o';
$icon = 'fa-calendar-o';
$backUrl = $this->context->contentContainer->createUrl('/task/index');


$participantStyle = 'display:inline-block;' ;

?>
<div class="panel-heading clearfix">
    <div>
        <strong><i class="fa <?= $icon ?>"></i> <?= Html::encode($task->title); ?></strong>
    </div>

    <?= TaskMenu::widget(['task' => $task,
        'canEdit' => $canEdit,
        'contentContainer' => $contentContainer]); ?>

    <div class="row clearfix">
        <div class="col-sm-12 media">
            <div class="media-body clearfix">
                <?php if ($task->scheduling) : ?>
                <h2 style="margin:5px 0 0 0;">
                    <?= $task->getFormattedStartDateTime(); ?>
                    -
                    <?= $task->getFormattedEndDateTime(); ?>
                </h2>
                <?php endif; ?>
                <span class="author">
                    <?= Html::containerLink($task->content->createdBy); ?>
                </span>
                <?php if ($task->content->updated_at !== null) : ?>
                    &middot <span class="tt updated" title="<?= Yii::$app->formatter->asDateTime($task->content->updated_at); ?>"><?= Yii::t('ContentModule.base', 'Updated'); ?></span>
                <?php endif; ?>

                <?php $badge = TaskBadge::widget(['task' => $task]) ?>
                <?= (!empty($badge)) ? '<br>' . $badge : '' ?>

                <?php if ($task->content->isPublic()) : ?>
                    <span class="label label-info"><?= Yii::t('SpaceModule.base', 'Public'); ?></span>
                <?php endif; ?>

                <?= Button::back($backUrl, Yii::t('TaskModule.base', 'Back to overview'))->sm()->loader(true); ?>

            </div>

<!--        Assigned Task User-->
            <?php if ($task->hasTaskAssigned()) : ?>
            <div>
                <div style="<?= $participantStyle ?>">
                    <em><strong><?= Yii::t('TaskModule.views_index_index', 'Assigned') ?>:</strong></em><br>
                    <?php foreach ($task->taskAssignedUsers as $user) : ?>
                        <a href="<?= $user->getUrl(); ?>">
                            <?= \humhub\modules\user\widgets\Image::widget(['user' => $user, 'width' => 24, 'showTooltip' => true]) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif ?>

<!--        Responsible Task User-->
            <?php if ($task->hasTaskResponsible()) : ?>
                <div>
                <div style="<?= $participantStyle ?>">
                    <em><strong><?= Yii::t('TaskModule.views_index_index', 'Responsible') ?>:</strong></em><br>
                    <?php foreach ($task->taskResponsibleUsers as $user) : ?>
                        <a href="<?= $user->getUrl(); ?>">
                            <?= \humhub\modules\user\widgets\Image::widget(['user' => $user, 'width' => 24, 'showTooltip' => true]) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif ?>



        </div>
    </div>
</div>