<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\user\widgets\UserPickerField;
use humhub\widgets\MarkdownField;
use humhub\widgets\ModalButton;
use yii\bootstrap\ActiveForm;

/* @var $itemForm \humhub\modules\task\models\forms\TaskItemForm */
/* @var $deleteUrl string */
/* @var $saveUrl string */

$item = $itemForm->model;

$title = $item->isNewRecord
    ? Yii::t('TaskModule.views_index_editItem', '<strong>Create</strong> new entry')
    : Yii::t('TaskModule.views_index_editItem', '<strong>Edit</strong> entry');

?>
<?php \humhub\widgets\ModalDialog::begin(['header' => $title, 'size' => 'large']) ?>
    <div class="modal-body">
        <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($item, 'title')->textInput(['placeholder' => Yii::t('TaskModule.views_index_editItem', 'Title of this entry')]); ?>

            <?= $form->field($item, 'description')->widget(MarkdownField::class, [
                    'placeholder' => Yii::t('TaskModule.views_index_editItem', 'Subject'),
                    'fileModel' => $itemForm,
                    'fileAttribute' => 'fileList'
            ]); ?>

            <?= $form->field($itemForm, 'duration')->textInput(['id' => 'itemDuration', 'placeholder' => Yii::t('TaskModule.views_index_edit', 'hh:mm')]); ?>

            <?= $form->field($itemForm, 'inputModerators')->widget(UserPickerField::class, [
                    'placeholder' => Yii::t('TaskModule.views_index_editItem', 'Add moderator'),
                    'defaultResults' => $item->task->participantUsers
            ]) ?>

            <div class="row">
                <div class="col-md-12">
                    <p>
                        <a data-toggle="collapse" id="external-moderators-link" href="#collapse-external-moderators" style="font-size: 11px;">
                            <i class="fa <?= empty($item->external_moderators) ? "fa-caret-right" : "fa-caret-down" ?>"></i>
                            <?= Yii::t('TaskModule.views_index_editItem', 'External moderators') ?>
                        </a>
                    </p>

                    <div id="collapse-external-moderators" class="panel-collapse <?= empty($item->external_moderators) ? "collapse" : "in" ?>">
                        <?= $form->field($item, 'external_moderators')->textInput(['id' => 'external_moderators', 'placeholder' => Yii::t('TaskModule.views_index_editItem', 'Add external moderators (free text)')]); ?>
                        <br>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-<?= !$item->isNewRecord ? '8 text-left': '12 text-center' ?>">
                    <?= ModalButton::submitModal($saveUrl); ?>
                    <?= ModalButton::cancel(); ?>
                </div>
                <?php if (!$item->isNewRecord): ?>
                <div class="col-md-4 text-right">
                        <?= ModalButton::danger(Yii::t('base', 'Delete'))->confirm(
                            Yii::t('TaskModule.views_index_editItem', '<strong>Confirm</strong> entry deletion'),
                            Yii::t('TaskModule.views_index_editItem', 'Do you really want to delete this entry?'),
                            Yii::t('base', 'Delete'))->post($deleteUrl); ?>
                </div>
                <?php endif; ?>
            </div>
        <?php ActiveForm::end(); ?>
    </div>

<?php \humhub\widgets\ModalDialog::end() ?>

<script type="text/javascript">
    $('#itemDuration').timeEntry({show24Hours: true, unlimitedHours: true, defaultTime: '01:00', timeSteps: [1, 1, 15], spinnerImage: ''});

    $('#collapse-external-moderators').on('show.bs.collapse', function () {
        $('#external-moderators-link i').switchClass('fa-caret-right', 'fa-caret-down', 0);
    }).on('hide.bs.collapse', function () {
        $('#external-moderators-link i').switchClass('fa-caret-down', 'fa-caret-right', 0);
    }).on('shown.bs.collapse', function () {
        $('#external_moderators').focus();
    });
</script>