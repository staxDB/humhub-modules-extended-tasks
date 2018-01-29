<?php
 /* @var $task \humhub\modules\task\models\Task */

use humhub\libs\Html;
use humhub\widgets\ModalButton;


?>
<div class="media meeting">
    <div class="media-body">
        <div class="row">
            <div class="col-md-1"><i class="fa fa-tasks meeting-wall-icon colorDefault" style="font-size: 38px;"></i><br><br></div>
            <div class="col-md-11">
                <h4 class="media-heading"><a href="<?= $task->getUrl(); ?>"><?= Html::encode($task->title); ?></a></h4>
                <h5>
                    <?= Yii::t('TaskModule.widgets_views_wallentry', 'Deadline at') . ' ' . $task->getFormattedEndDateTime(); ?>
                </h5>
                <br />
                <?= ModalButton::primary(Yii::t('TaskModule.widgets_views_wallentry', 'Open Task'))->close()->link($task->getUrl())->sm() ?>
            </div>
        </div>
    </div>
</div>


