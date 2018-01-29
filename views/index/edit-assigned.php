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
?>

<div class="modal-body">

    <?= $form->field($taskForm->task, 'assignedUsers')->widget(UserPickerField::class, [
        'id' => 'taskUserPicker',
        'selection' => $taskForm->task->taskAssignedUsers,
        'url' => $taskForm->getTaskAssignedPickerUrl(),
        'placeholder' => Yii::t('TaskModule.views_index_edit', 'Add task users')
    ]) ?>

    <?= Link::userPickerSelfSelect('#taskUserPicker'); ?>

</div>