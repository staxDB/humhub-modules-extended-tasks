<?php
use humhub\libs\Html;

/* @var $task \humhub\modules\task\models\Task */
/* @var $url string */
/* @var $canEdit boolean */
/* @var $filterResult boolean */
?>

<a href="<?= $url ?>">
    <div class="media task">

        <div class="media-body">
            <?= \humhub\modules\task\widgets\TaskBadge::widget(['task' => $task, 'right' => true])?>

            <h4 class="media-heading">
                <?= Html::encode($task->title); ?>
            </h4>

            <h5>
                <?= Yii::t('TaskModule.views_index_index', 'Deadline at') ?>
                <?= $task->getFormattedEndDateTime() ?>
                <?= \humhub\widgets\Button::primary()
                ->options(['class' => 'tt', 'title' => Yii::t('TaskModule.views_index_index', 'Edit'), 'style' => 'margin-left:2px']
                )->icon('fa-pencil')->right()->xs()->action('ui.modal.load', $editUrl)->loader(false)->visible($canEdit) ?>
            </h5>
            <h5>
                <?= \humhub\modules\task\widgets\TaskPercentageBar::widget(['task' => $task, 'filterResult' => $filterResult])?>
            </h5>
        </div>

    </div>
</a>