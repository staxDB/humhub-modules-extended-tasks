<?php
use humhub\widgets\ContentTagDropDown;
use humhub\widgets\MarkdownField;
use humhub\widgets\TimePicker;
use humhub\widgets\TimeZoneDropdownAddition;
use humhub\modules\task\widgets\TaskPickerField;
use yii\jui\DatePicker;

/* @var $form \humhub\widgets\ActiveForm */
/* @var $taskForm \humhub\modules\task\models\forms\TaskForm */
?>

<div class="modal-body">


    <?= $form->field($taskForm->task, 'title')->textInput(['placeholder' => Yii::t('TaskModule.views_index_edit', 'Title of your task')]); ?>

    <?= $form->field($taskForm->task, 'description')->widget(MarkdownField::class, ['fileModel' => $taskForm->task, 'fileAttribute' => 'files'])->label(false) ?>


    <?= $form->field($taskForm, 'is_public')->checkbox() ?>
    <?= $form->field($taskForm->task, 'scheduling')->checkbox(['data-action-change' => 'toggleScheduling']) ?>

    <?= $form->field($taskForm->task, 'subTasks')->widget(TaskPickerField::class, [
        'id' => 'subTaskPicker',
        'selection' => $taskForm->task->subTasks,
        'url' => $taskForm->getSubTaskPickerUrl(),
        'placeholder' => Yii::t('TaskModule.views_index_edit', 'Add sub tasks')
    ]) ?>
</div>