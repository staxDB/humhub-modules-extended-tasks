<?php
use humhub\libs\Html;

/* @var $task \humhub\modules\task\models\Task */
/* @var $url string */
/* @var $canEdit boolean */
/* @var $duplicateUrl string */
?>

<a href="<?= $url ?>">
    <div class="media task">

        <div class="media-body">
            <?= \humhub\modules\task\widgets\TaskBadge::widget(['task' => $task, 'right' => true])?>

            <h4 class="media-heading"><?= Html::encode($task->title); ?></h4>
            <h5>
                <?= Yii::$app->formatter->asDate($task->date); ?>
                <?= Yii::t('TaskModule.views_index_index', 'at') ?>
                <?= Yii::$app->formatter->asTime(new DateTime($task->begin, new DateTimeZone(Yii::$app->timeZone)), 'short'); ?>
                - <?= Yii::$app->formatter->asTime(new DateTime($task->end, new DateTimeZone(Yii::$app->timeZone)), 'short'); ?>
                <?php if ($task->location) : ?>
                    , <?= Html::encode($task->location) ?>
                <?php endif; ?>
                <?php if ($task->room) : ?>
                    (<?= Html::encode($task->room) ?>)
                <?php endif; ?>
            <?= \humhub\widgets\Button::primary()
                ->options(['class' => 'tt', 'title' => Yii::t('TaskModule.views_index_index', 'Edit'), 'style' => 'margin-left:2px']
                )->icon('fa-pencil')->right()->xs()->action('ui.modal.load', $editUrl)->loader(false)->visible($canEdit) ?>
            <?= \humhub\widgets\Button::defaultType()
                ->options(['class' => 'tt', 'title' => Yii::t('TaskModule.views_index_index', 'Duplicate')]
                )->icon('fa-clone')->right()->xs()->action('ui.modal.load', $duplicateUrl)->loader(false)->visible($canEdit) ?>
            </h5>
        </div>

    </div>
</a>