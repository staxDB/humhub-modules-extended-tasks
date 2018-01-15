<?php
\humhub\modules\task\assets\Assets::register($this);

use humhub\modules\task\widgets\TaskListEntry;
use humhub\modules\task\widgets\TaskListView;
use humhub\widgets\Button;
use humhub\widgets\ModalButton;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $canEdit boolean */
/* @var $contentContainer \humhub\modules\content\components\ContentContainerActiveRecord */
/* @var $pendingTasks \humhub\modules\task\models\Task[] */
/* @var $tasksPastProvider \yii\data\ActiveDataProvider */
/* @var $filter \humhub\modules\task\models\forms\TaskFilter */

$createUrl = $contentContainer->createUrl('/task/index/edit');
$filterUrl = $contentContainer->createUrl('/task/index/filter-tasks');
$emptyText = ($canEdit) ? Yii::t('TaskModule.views_index_index', "Start now, by creating a new task!")
    : Yii::t('TaskModule.views_index_index', 'There are currently no upcoming tasks!.');

?>
<div class="panel panel-default task-overview">
    <div class="panel-heading">
        <i class="fa fa-calendar-o"></i> <?= Yii::t('TaskModule.views_index_index', '<strong>Next</strong> tasks'); ?>
        <?php if ($canEdit) : ?>
            <?= ModalButton::success(Yii::t('TaskModule.views_index_index', 'New task'))->post($createUrl)->sm()->icon('fa-plus')->right();?>
        <?php endif; ?>
    </div>

    <?php if (empty($pendingTasks)) : ?>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12 text-center">
                    <?= $emptyText ?>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="panel-body">
            <ul class="media-list">
                <?php foreach ($pendingTasks as $task): ?>
                    <li>
                        <?= TaskListEntry::widget(['task' => $task, 'contentContainer' => $contentContainer, 'canEdit' => $canEdit]) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<div class="panel panel-default task-overview">
    <div class="panel-heading">
        <i class="fa fa-calendar-check-o"></i> <?= Yii::t('TaskModule.views_index_index', '<strong>Task</strong> overview'); ?>
    </div>

    <div class="task-filter">
        <?php $form = ActiveForm::begin(['action' => $filterUrl,  'options' => [ 'data-ui-widget' => 'task.TaskFilter', 'data-ui-init' => ''], 'enableClientValidation' => false]) ?>
                <?= $form->field($filter, 'title')->textInput(['id' => 'taskfilter-title', 'placeholder' => Yii::t('TaskModule.views_index_index', 'Filter tasks by title')])->label(false) ?>
        <div id="task-filter-loader" class="pull-right"></div>

                <div class="checkbox-filter">
                    <?= $form->field($filter, 'past')->checkbox(['style' => 'float:left']); ?>
                </div>
                <div class="checkbox-filter">
                    <?= $form->field($filter, 'taskUser')->checkbox(['style' => 'float:left']); ?>
                </div>
                <div class="checkbox-filter">
                    <?= $form->field($filter, 'own')->checkbox(['style' => 'float:left']); ?>
                </div>
        <?php ActiveForm::end() ?>
    </div>

    <div id="filter-tasks-list" class="panel-body">
        <?= TaskListView::widget(['filter' => $filter, 'canEdit' => $canEdit]) ?>
    </div>
</div>
