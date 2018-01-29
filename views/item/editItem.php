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
    ? Yii::t('TaskModule.views_index_editItem', '<strong>Create</strong> new item')
    : Yii::t('TaskModule.views_index_editItem', '<strong>Edit</strong> item');

?>
<?php \humhub\widgets\ModalDialog::begin(['header' => $title, 'size' => 'large']) ?>
    <div class="modal-body">
        <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($item, 'title')->textInput(['placeholder' => Yii::t('TaskModule.views_index_editItem', 'Title of subtask')]); ?>

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