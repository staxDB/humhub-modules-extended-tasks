<?php


/* @var $form \humhub\widgets\ActiveForm */
/* @var $taskForm \humhub\modules\task\models\forms\TaskForm */
?>

<div class="modal-body">
        <?= humhub\modules\task\widgets\AddItemsInput::widget(['name' => 'newItems[]', 'showTitle' => true]); ?>
</div>