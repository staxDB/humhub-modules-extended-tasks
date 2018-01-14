<?php
use humhub\widgets\Button;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;
use yii\bootstrap\ActiveForm;

/* @var $chooseModel \humhub\modules\task\models\ShiftTaskChoose */

?>

<?php ModalDialog::begin(['header' => Yii::t('TaskModule.views_item_shift', '<strong>Shift</strong> agenda item'),'size' => 'small']); ?>

    <div class="shift-menu modal-body">
        <?php if(!empty($chooseModel->getItems())) : ?>
            <?= Button::info(Yii::t('TaskModule.views_item_shift', 'Chose upcoming task'))
                ->lg()->style('width:100%')->options(['data-shift-button' => '.shift-choose-task'])->loader(false); ?><br><br>
        <?php endif ?>

        <?= ModalButton::info(Yii::t('TaskModule.views_item_shift', 'Create new task'))->lg()->style('width:100%')->load($createNewUrl) ?><br><br>
        <?= ModalButton::cancel()->lg()->style('width:100%'); ?>
    </div>

    <?php if(!empty($chooseModel->getItems())) : ?>
        <?php $form = ActiveForm::begin(); ?>
            <div class="shift-choose-task modal-body" style="display:none">
                    <?= $form->field($chooseModel, 'taskId')->dropDownList($chooseModel->getItems(), ['data-ui-select2' => '', 'style' => 'width:100%'])?>
            </div>

            <div class="shift-choose-task modal-footer" style="display:none">
                <?= ModalButton::cancel()?>
                <?= ModalButton::submitModal($submitUrl); ?>
            </div>
        <?php ActiveForm::end(); ?>
    <?php endif; ?>

    <script>
        $('[data-shift-button]').on('click', function() {
            $('.modal-body').hide();
            $('.modal-footer').hide();
            $($(this).data('shiftButton')).show();
        });
    </script>

<?php ModalDialog::end(); ?>
