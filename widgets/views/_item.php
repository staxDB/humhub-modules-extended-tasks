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
<?= Html::beginTag('div', $options) ?>

<div class="task-item" id="item-<?= $item->id; ?>">

    <div class="col-md-12" style="padding-right: 0;">
        <?= Html::checkBox('item[' . $item->id . ']', $item->completed, ['label' => $item->title, 'itemId' => $item->id, 'data-action-change' => 'check', 'disabled' => $disabled]); ?>
    </div>

</div>
<?= Html::endTag('div') ?>

<div class="clearFloats"></div>