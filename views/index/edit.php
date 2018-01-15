<?php

use humhub\widgets\Button;

use humhub\widgets\ModalDialog;
use humhub\widgets\Link;
use humhub\widgets\ModalButton;
use humhub\widgets\ActiveForm;
use humhub\modules\user\widgets\UserPickerField;
use humhub\widgets\TimePicker;
use humhub\widgets\TimeZoneDropdownAddition;
use yii\jui\DatePicker;

/* @var $taskForm \humhub\modules\task\models\forms\TaskForm */

\humhub\modules\task\assets\Assets::register($this);

$task = $taskForm->task;

?>

<?php ModalDialog::begin(['header' => $taskForm->getTitle()]) ?>

<div class="modal-body">
    <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>
    <br>

    <?= $form->field($task, 'title')->textInput(['placeholder' => Yii::t('TaskModule.views_index_edit', 'Title of your task')]); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <?= $form->field($taskForm, 'deadline')->widget(DatePicker::className(), ['dateFormat' => 'short', 'clientOptions' => [], 'options' => ['class' => 'form-control', 'placeholder' => Yii::t('base', 'Date')]]); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
        </div>
        <div class="col-md-6">
            <?= $form->field($taskForm, 'timeZone')->widget(TimeZoneDropdownAddition::class)->label(false)?>
        </div>
    </div>

    <?= $form->field($task, 'assignedUsers')->widget(UserPickerField::class, [
            'id' => 'taskUserPicker',
            'selection' => $task->taskUsers,
            'url' => $taskForm->getTaskUserPickerUrl(),
            'placeholder' => Yii::t('TaskModule.views_index_edit', 'Add task users')
    ]) ?>

    <?= Link::userPickerSelfSelect('#taskUserPicker'); ?>

    <div class="row">
        <div class="col-md-<?= !$task->isNewRecord ? '8 text-left': '12 text-center' ?>">
            <?= ModalButton::cancel(); ?>
            <?= ModalButton::submitModal($taskForm->getSubmitUrl())?>
        </div>
        <?php if (!$task->isNewRecord): ?>
            <div class="col-md-4 text-right">
                    <?= Button::danger(Yii::t('TaskModule.base', 'Delete'))->confirm(
                        Yii::t('TaskModule.views_index_edit', '<strong>Confirm</strong> task deletion'),
                        Yii::t('TaskModule.views_index_edit', 'Do you really want to delete this task?'),
                        Yii::t('TaskModule.base', 'Delete'))->action('ui.modal.post', $taskForm->getDeleteUrl()); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php ModalDialog::end() ?>