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
    <?= $form->field($taskForm->task, 'all_day')->checkbox(['data-action-change' => 'toggleDateTime']) ?>


    <div class="row">
        <div class="col-md-6">
            <?= $form->field($taskForm, 'start_date')->widget(DatePicker::className(), ['dateFormat' => Yii::$app->params['formatter']['defaultDateFormat'], 'clientOptions' => [], 'options' => ['class' => 'form-control']]) ?>
        </div>
        <div class="col-md-6 timeField" <?= !$taskForm->showTimeFields() ? 'style="opacity:0.2"' : '' ?>>
            <?= $form->field($taskForm, 'start_time')->widget(TimePicker::class, ['disabled' => $taskForm->task->all_day]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($taskForm, 'end_date')->widget(DatePicker::className(), ['dateFormat' => Yii::$app->params['formatter']['defaultDateFormat'], 'clientOptions' => [], 'options' => ['class' => 'form-control']]) ?>
        </div>
        <div class="col-md-6 timeField" <?= !$taskForm->showTimeFields() ? 'style="opacity:0.2"' : '' ?>>
            <?= $form->field($taskForm, 'end_time')->widget(TimePicker::class, ['disabled' => $taskForm->task->all_day]); ?>
        </div>
    </div>

    <?php Yii::$app->i18n->autosetLocale(); ?>

    <div class="row">
        <div class="col-md-6"></div>
        <div class="col-md-6 timeZoneField" >
            <?= TimeZoneDropdownAddition::widget(['model' => $taskForm])?>
        </div>
    </div>

    <?= $form->field($taskForm->task, 'subTasks')->widget(TaskPickerField::class, [
        'id' => 'subTaskPicker',
        'selection' => $taskForm->task->subTasks,
        'url' => $taskForm->getSubTaskPickerUrl(),
        'placeholder' => Yii::t('TaskModule.views_index_edit', 'Add sub tasks')
    ]) ?>
</div>