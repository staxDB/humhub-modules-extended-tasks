<?php

use humhub\widgets\MarkdownField;
use humhub\widgets\ColorPickerField;

/* @var $form \humhub\widgets\ActiveForm */
/* @var $taskForm \humhub\modules\task\models\forms\TaskForm */


if ($taskForm->task->color == null && isset($taskForm->contentContainer->color)) {
    $taskForm->task->color = $taskForm->contentContainer->color;
} elseif ($taskForm->task->color == null) {
    $taskForm->tasks->color = '#d1d1d1';
}
?>

<div class="modal-body">

    <div id="event-color-field" class="form-group space-color-chooser-edit" style="margin-top: 5px;">
        <?= $form->field($taskForm->task, 'color')->widget(ColorPickerField::className(), ['container' => 'event-color-field'])->label(Yii::t('TaskModule.views_index_edit', 'Title and Color')); ?>

        <?= $form->field($taskForm->task, 'title', ['template' => '
                                    {label}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i></i>
                                        </span>
                                        {input}
                                    </div>
                                    {error}{hint}'
        ])->textInput(['placeholder' => Yii::t('TaskModule.views_index_edit', 'Title of your task'), 'maxlength' => true])->label(false) ?>

    </div>

    <?php
//    $form->field($taskForm->task, 'title')->textInput(['placeholder' => Yii::t('TaskModule.views_index_edit', 'Title of your task')]);
    ?>

    <?= $form->field($taskForm->task, 'description')->widget(MarkdownField::class, ['fileModel' => $taskForm->task, 'fileAttribute' => 'files']) ?>


    <?= $form->field($taskForm, 'is_public')->checkbox() ?>
    <?= $form->field($taskForm->task, 'scheduling')->checkbox(['data-action-change' => 'toggleScheduling']) ?>

    <?php
//    Todo
//    echo $form->field($taskForm->task, 'subTasks')->widget(TaskPickerField::class, [
//        'id' => 'subTaskPicker',
//        'selection' => $taskForm->task->subTasks,
//        'url' => $taskForm->getSubTaskPickerUrl(),
//        'placeholder' => Yii::t('TaskModule.views_index_edit', 'Add sub tasks')
//    ])
    ?>

</div>