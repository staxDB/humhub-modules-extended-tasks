<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
use humhub\widgets\Button;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;

/* @var $this \humhub\components\View */
/* @var $task \humhub\modules\task\models\Task  */
/* @var $canManageEntries boolean  */
/* @var $editUrl string  */

$deleteUrl = $contentContainer->createUrl('/task/index/delete', ['id' => $task->id, 'cal' => 1]);
?>

<?php ModalDialog::begin(['size' => 'large', 'closable' => true]); ?>
    <div class="modal-body" style="padding-bottom:0px">
        <?= $this->renderAjax('view', ['task' => $task])?>
    </div>
    <div class="modal-footer">
        <?php if($canManageEntries): ?>
            <?= ModalButton::primary(Yii::t('TaskModule.base', 'Edit'))->load($editUrl)->loader(true); ?>
        <?php endif; ?>
        <?= ModalButton::cancel(Yii::t('TaskModule.base', 'Close')) ?>
    </div>
<?php ModalDialog::end(); ?>