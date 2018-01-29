<?php
/* @var $this \yii\web\View */
/* @var $task \humhub\modules\task\models\Task */
/* @var $item \humhub\modules\task\models\TaskItem */
/* @var $contentContainer \humhub\modules\content\components\ContentContainerActiveRecord */
/* @var $canEdit boolean */
/* @var $editUrl string */
/* @var $editMinutesUrl string */
use humhub\libs\Html;
use humhub\modules\task\widgets\TaskItemMenu;
use humhub\widgets\Button;
use humhub\widgets\MarkdownView;
use humhub\widgets\ModalButton;
?>
<?= Html::beginTag('li', $options) ?>
    <div class="task-item" id="item-<?= $item->id; ?>">

        <div class="row">
            <div class="col-md-1 agenda-time-line">
                <div class="agenda-point backgroundInfo"></div>
            </div>
            <div class="col-md-11">

                <div class="task-item-content">

                    <?= TaskItemMenu::widget(['contentContainer' => $contentContainer, 'item' => $item, 'canEdit' => $canEdit]) ?>

                    <h1 class="task-item-title">
                        <?php if (!$item->completed) : ?>
                            <div id="completed-button">
                                <?= Html::checkBox('completed', false, ['class' => 'tt', 'label' => Yii::t('TaskModule.widgets', Html::encode($item->title)), 'taskId' => $item->id, 'data-action-change' => 'confirm', 'data-action-submit', 'data-ui-loader']); ?>
                            </div>
                        <?php endif; ?>

                    </h1>
                </div>

            </div>
        </div>
<?= Html::endTag('li') ?>