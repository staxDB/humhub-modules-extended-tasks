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
                    <?php if ($task->scheduling) : ?>
                        <?= Yii::t('TaskModule.views_index_index', 'Deadline at') ?>
                        <?= $task->getFormattedEndDateTime() ?>
                    <?php else : ?>
                        <?= Yii::t('TaskModule.views_index_index', 'No Scheduling set for this Task') ?>
                    <?php endif; ?>
                </h5>
                <div>
                    <?= \humhub\modules\task\widgets\TaskPercentageBar::widget(['task' => $task, 'filterResult' => false])?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12" style="margin-top: 10px;">
        <?= ModalButton::primary(Yii::t('TaskModule.widgets_views_wallentry', 'Open Task'))->close()->link($task->getUrl())->sm() ?>
    </div>
</div>


