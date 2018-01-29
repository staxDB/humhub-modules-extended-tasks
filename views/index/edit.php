<?php

use humhub\widgets\Button;

use humhub\widgets\ModalDialog;
use humhub\widgets\Link;
use humhub\widgets\ModalButton;
use humhub\widgets\ActiveForm;
use humhub\modules\user\widgets\UserPickerField;
use humhub\modules\task\widgets\TaskPickerField;
use humhub\widgets\TimePicker;
use humhub\widgets\TimeZoneDropdownAddition;
use yii\jui\DatePicker;
use humhub\widgets\Tabs;

/* @var $taskForm \humhub\modules\task\models\forms\TaskForm */

\humhub\modules\task\assets\Assets::register($this);

$task = $taskForm->task;

?>

<?php ModalDialog::begin(['header' => $taskForm->getTitle(), 'closable' => false]) ?>

    <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>

    <div id="task-form" data-ui-widget="task.Form" data-ui-init>

        <?= Tabs::widget([
            'viewPath' => '@task/views/index',
            'params' => ['form' => $form, 'taskForm' => $taskForm],
            'items' => [
                ['label' => Yii::t('TaskModule.views_index_edit', 'Basic'),'view' => 'edit-basic', 'linkOptions' => ['class' => 'tab-basic']],
                ['label' => Yii::t('TaskModule.views_index_edit', 'Checklist'),'view' => 'edit-checklist', 'linkOptions' => ['class' => 'tab-checklist']],
                ['label' => Yii::t('TaskModule.views_index_edit', 'Assign Users'),'view' => 'edit-assigned', 'linkOptions' => ['class' => 'tab-assigned']],
                ['label' => Yii::t('TaskModule.views_index_edit', 'Files'),'view' => 'edit-files', 'linkOptions' => ['class' => 'tab-files']]
            ]
        ]); ?>

    </div>

    <hr>

    <div class="modal-footer">
        <div class="col-md-<?= !$taskForm->task->isNewRecord ? '8 text-left': '12 text-center' ?>">
        <?= ModalButton::submitModal($taskForm->getSubmitUrl()); ?>
        <?= ModalButton::cancel(); ?>
        </div>
        <?php if (!$taskForm->task->isNewRecord): ?>
            <div class="col-md-4 text-right">
                <?= Button::danger(Yii::t('TaskModule.base', 'Delete'))->confirm(
                    Yii::t('TaskModule.views_index_edit', '<strong>Confirm</strong> task deletion'),
                    Yii::t('TaskModule.views_index_edit', 'Do you really want to delete this task?'),
                    Yii::t('TaskModule.base', 'Delete'))->action('ui.modal.post', $taskForm->getDeleteUrl()); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

<?php ModalDialog::end() ?>