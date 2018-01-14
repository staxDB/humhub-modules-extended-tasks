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
                <?= $form->field($taskForm, 'startDate')->widget(DatePicker::className(), ['dateFormat' => 'short', 'clientOptions' => [], 'options' => ['class' => 'form-control', 'placeholder' => Yii::t('base', 'Date')]]); ?>
            </div>
        </div>
        <div class="col-md-3" style="padding-left:0px;">
            <?= $form->field($taskForm, 'startTime')->widget(TimePicker::class)?>
        </div>
        <div class="col-md-3"  style="padding-left:0px;">
            <?= $form->field($taskForm, 'endTime')->widget(TimePicker::class)?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
        </div>
        <div class="col-md-6">
            <?= $form->field($taskForm, 'timeZone')->widget(TimeZoneDropdownAddition::class)->label(false)?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($task, 'location')->textInput(['placeholder' => Yii::t('TaskModule.views_index_edit', 'Location')]); ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($task, 'room')->textInput(['id' => 'task-end', 'placeholder' => Yii::t('TaskModule.views_index_edit', 'Room')]); ?>
        </div>
    </div>

    <?= $form->field($task, 'inputParticipants')->widget(UserPickerField::class, [
            'id' => 'participantPicker',
            'selection' => $task->participantUsers,
            'url' => $taskForm->getParticipantPickerUrl(),
            'placeholder' => Yii::t('TaskModule.views_index_edit', 'Add participants')
    ]) ?>

    <?= Link::userPickerSelfSelect('#participantPicker'); ?>

    <?php if(!empty($taskForm->duplicateId)) :?>
        <?= $form->field($taskForm, 'duplicateId')->hiddenInput()->label(false) ?>
        <?= $form->field($taskForm, 'duplicateItems')->checkbox() ?>
    <?php endif ?>

    <div class="row">
        <div class="col-md-12">
            <p>
                <a data-toggle="collapse" id="external-participants-link" href="#collapse-external-participants"
                   style="font-size: 11px;">
                    <i class="fa <?= empty($task->external_participants) ? "fa-caret-right" : "fa-caret-down" ?>"></i>
                    <?= Yii::t('TaskModule.views_index_edit', 'External participants') ?>
                </a>
            </p>

            <div id="collapse-external-participants"
                 class="panel-collapse <?= empty($task->external_participants) ? "collapse" : "in" ?>">
                <?= $form->field($task, 'external_participants')->textInput(['id' => 'external_participants', 'placeholder' => Yii::t('TaskModule.views_index_edit', 'Add external participants (free text)')]); ?>
                <br>
            </div>
        </div>
    </div>

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

<script type="text/javascript">
    $('#collapse-external-participants').on('show.bs.collapse', function () {
        $('#external-participants-link i').switchClass('fa-caret-right', 'fa-caret-down', 0);
    }).on('hide.bs.collapse', function () {
        $('#external-participants-link i').switchClass('fa-caret-down', 'fa-caret-right', 0);
    }).on('shown.bs.collapse', function () {
        $('#external_participants').focus();
    });
</script>