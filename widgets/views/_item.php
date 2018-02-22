<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/* @var $this \yii\web\View */
/* @var $task \humhub\modules\task\models\Task */
/* @var $item \humhub\modules\task\models\TaskItem */

/* @var $options array */

use humhub\libs\Html;

$disabled = ($task->canCheckItems()) ? false : 'true';
?>
<?= Html::beginTag('li', $options) ?>

<div class="task-item" id="item-<?= $item->id; ?>">

    <div class="row">
        <div class="col-md-12" style="padding-right: 0;">

            <div class="task-item-content">
<!--                <div class="task-item-head">-->
                    <span class="task-drag-icon tt" title="<?= Yii::t('TaskModule.views_index_index', 'Drag entry')?>" style="display:none">
                        <i class="fa fa-arrows"></i>&nbsp;
                    </span>
<!--                </div>-->

                <?= Html::checkBox('item[' . $item->id . ']', $item->completed, ['label' => $item->title, 'itemId' => $item->id, 'data-action-change' => 'check', 'disabled' => $disabled]); ?>

            </div>
        </div>
    </div>

</div>
<?= Html::endTag('li') ?>
