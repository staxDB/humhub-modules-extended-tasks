<?php


/* @var $form \humhub\widgets\ActiveForm */
/* @var $taskForm \humhub\modules\task\models\forms\TaskForm */
/* @var $item \humhub\modules\task\models\TaskItem */
?>

<div class="modal-body">
     <?php foreach ($taskForm->task->items as $item) :?>
            <div class="form-group">
                <div class="input-group">
                    <input type="text" name="items[<?= $item->id ?>]"
                           value="<?= $item->title ?>"
                           class="form-control tt task_item_old_input"
                           placeholder="<?= Yii::t('TaskModule.widgets_views_form', "Edit item (empty field will be removed)...") ?>"/>
                    <div class="input-group-addon" style="cursor:pointer;" data-action-click="removeTaskItem">
                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?= humhub\modules\task\widgets\AddItemsInput::widget(['name' => 'newItems[]', 'showTitle' => true]); ?>

</div>