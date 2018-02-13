<?php

use humhub\widgets\PanelMenu;
use yii\helpers\Html;
use humhub\libs\Helpers;

/* @var $taskEntries \humhub\modules\task\models\Task[] */

?>
<div class="panel calendar-upcoming-snippet" id="calendar-upcoming-events-snippet">

    <div class="panel-heading">
        <i class="fa fa-tasks"></i> <?= Yii::t('TaskModule.widgets_views_myTasks', '<strong>Your</strong> tasks'); ?>
    </div>

    <div class="panel-body" style="padding:0px;">
        <hr style="margin:0px">
        <ul class="media-list">
            <?php foreach ($taskEntries as $entry) : ?>
                <?php $color = $entry->color ? $entry->color : $this->theme->variable('info') ?>
                <a href="<?= $entry->getUrl() ?>">
                    <li style="border-left: 3px solid <?= $color?>">
                        <div class="media">
                            <div class="media-body  text-break">
                                <?=  $entry->getBadge() ?>
                                <strong>
                                    <?= Helpers::trimText(Html::encode($entry->getTitle()), 60) ?>
                                </strong>

                                <br />
                                <span class="time"><?= $entry->getFormattedDateTime() ?></span>
                            </div>
                        </div>
                    </li>
                </a>
            <?php endforeach; ?>
        </ul>
    </div>

</div>

