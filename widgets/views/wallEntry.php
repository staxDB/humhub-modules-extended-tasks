<?php
 /* @var $task \humhub\modules\task\models\Task */

use humhub\libs\Html;
use humhub\widgets\ModalButton;


?>
<div class="media task">
    <div class="media-left media-middle">
        <div class="col-md-1">
            <i class="fa fa-tasks meeting-wall-icon colorDefault" style="font-size: 38px;"></i>
            <!--                <br><br>-->
        </div>
    </div>
    <div class="media-body">
        <div class="row">
            <div class="col-md-11">
                <h4 class="media-heading">
                    <a href="<?= $task->getUrl(); ?>"><?= Html::encode($task->title); ?></a>
                </h4>
                <h5>
                    <?= $task->getFormattedDateTime() ?>
                </h5>

                <div>
                    <?= \humhub\modules\task\widgets\TaskPercentageBar::widget(['task' => $task, 'filterResult' => false])?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">

        <strong><i class="fa fa-users"></i> <?= Yii::t('TaskModule.widgets_views_wallentry', 'Assignments:'); ?></strong><br>
        <div style="font-style: italic; font-size: 13px;">
            <?php if ($task->isTaskResponsible()) : ?>
                <i class="fa fa-check"></i>
                <?= Yii::t('TaskModule.widgets_views_wallentry', 'You are responsible!') ?>
            <?php elseif ($task->isTaskAssigned()) : ?>
                <i class="fa fa-check"></i>
                <?= Yii::t('TaskModule.widgets_views_wallentry', 'You are assigned!') ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-12" style="margin-top: 10px;">
        <?= ModalButton::primary(Yii::t('TaskModule.widgets_views_wallentry', 'Open Task'))->close()->link($task->getUrl())->sm() ?>
    </div>
</div>


