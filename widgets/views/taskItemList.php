<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/* @var $this \yii\web\View */
/* @var $task \humhub\modules\task\models\Task */
/* @var $items \humhub\modules\task\models\TaskItem[] */
/* @var $options array */

use humhub\libs\Html;
use humhub\modules\task\widgets\TaskItemWidget;

$divOptions = $options;
//$divOptions['class'] = 'row';

?>
<div class="<?= (count($items)) ? "task-item-container" : '' ?>">
    <?= Html::beginTag('ul', $divOptions) ?>
    <?php foreach ($items as $item): ?>
        <?= TaskItemWidget::widget(['item' => $item, 'task' => $task]); ?>
    <?php endforeach; ?>
    <?= Html::endTag('ul') ?>
</div>
