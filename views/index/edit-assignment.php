<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\user\widgets\UserPickerField;
use humhub\widgets\Link;

/* @var $form \humhub\widgets\ActiveForm */
/* @var $taskForm \humhub\modules\task\models\forms\TaskForm */
/* @var $responsible [] \humhub\modules\user\models\User */

$responsible = $taskForm->task->taskResponsibleUsers;
array_push($responsible, Yii::$app->user->getIdentity()); // add creator to responsible users
?>

<div class="modal-body">

    <?= $form->field($taskForm->task, 'assignedUsers')->widget(UserPickerField::class, [
        'id' => 'taskUserPicker',
        'selection' => $taskForm->task->taskAssignedUsers,
        'url' => $taskForm->getTaskAssignedPickerUrl(),
        'placeholder' => Yii::t('TaskModule.views_index_edit', 'Assign users')
    ]) ?>

    <?= Link::userPickerSelfSelect('#taskUserPicker'); ?>


    <?= $form->field($taskForm->task, 'responsibleUsers')->widget(UserPickerField::class, [
        'id' => 'taskResponsibleUserPicker',
        'selection' => $responsible,
        'url' => $taskForm->getTaskResponsiblePickerUrl(),
        'placeholder' => Yii::t('TaskModule.views_index_edit', 'Add responsible users'),
    ]) ?>

    <?= Link::userPickerSelfSelect('#taskResponsibleUserPicker'); ?>

    <br>
    <?= $form->field($taskForm->task, 'review')->checkbox() ?>

    <br>
    <?= $form->field($taskForm->task, 'cal_mode')->dropDownList($taskForm->task->getCalModeItems()) ?>

</div>