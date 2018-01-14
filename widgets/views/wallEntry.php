<?php
 /* @var $meeting \humhub\modules\meeting\models\Meeting */

use humhub\libs\Html;
use humhub\widgets\ModalButton;


?>
<div class="media meeting">
    <div class="media-body">
        <div class="row">
            <div class="col-md-1"><i class="fa fa-calendar-o meeting-wall-icon colorDefault" style="font-size: 38px;"></i><br><br></div>
            <div class="col-md-11">
                <h4 class="media-heading"><a href="<?= $meeting->getUrl(); ?>"><?= Html::encode($meeting->title); ?></a></h4>
                <h5>
                    <?= $meeting->getFormattedDateTime(); ?>
                </h5>
                <br />
                <?= ModalButton::primary(Yii::t('MeetingModule.widgets_views_wallentry', 'Open Meeting'))->close()->link($meeting->getUrl())->sm() ?>
            </div>
        </div>
    </div>
</div>


