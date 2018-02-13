<?php
/* @var $task \humhub\modules\task\models\Task */

use humhub\libs\Html;
use humhub\widgets\ModalButton;
use humhub\modules\task\widgets\TaskPercentageBar;

$color = $task->color ? $task->color : $this->theme->variable('info');
?>
<div class="media task">
    <div class="task-head" style="padding-left:10px; border-left: 3px solid <?= $color ?>">
        <div class="media-body clearfix">
            <a href="<?= $task->getUrl(); ?>" class="pull-left" style="margin-right: 10px">
                <i class="fa fa-tasks meeting-wall-icon colorDefault" style="font-size: 38px;"></i>
            </a>
            <h4 class="media-heading">
                <a href="<?= $task->getUrl(); ?>">
                    <b><?= Html::encode($task->title); ?></b>
                </a>
            </h4>
            <h5>
                <?= $task->getFormattedDateTime() ?>
            </h5>
            <?= TaskPercentageBar::widget(['task' => $task, 'filterResult' => false]) ?>
        </div>
    </div>

    <?php
    $assigned = $task->isTaskAssigned();
    $responsible = $task->isTaskResponsible();
    ?>

    <?php if ($responsible || $assigned) : ?>
        <div class="row">
            <div class="col-md-12 task-assignment">
                <strong><i class="fa fa-users"></i> <?= Yii::t('TaskModule.widgets_views_wallentry', 'Assignments:'); ?>
                </strong><br>
                <div style="font-style: italic; font-size: 13px;">
                    <?php if ($responsible) : ?>
                        <i class="fa fa-check"></i>
                        <?= Yii::t('TaskModule.widgets_views_wallentry', 'You are responsible!') ?>
                    <?php elseif ($assigned) : ?>
                        <i class="fa fa-check"></i>
                        <?= Yii::t('TaskModule.widgets_views_wallentry', 'You are assigned!') ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12" style="margin-top: 10px;">
            <?= ModalButton::primary(Yii::t('TaskModule.widgets_views_wallentry', 'Open Task'))->close()->link($task->getUrl())->sm() ?>
        </div>
    </div>

</div>


