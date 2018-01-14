<?php

use yii\helpers\Html;
?>

<h1><strong><?= Html::encode($task->title); ?></strong></h1>
<h2>
    <?= Yii::$app->formatter->asDate($task->date); ?>
    at <?= substr($task->begin, 0, 5); ?> - <?= substr($task->end, 0, 5); ?>
    <?= Html::encode($task->location) ?> <?php
    if ($task->room != null) {
        echo "(" . Html::encode($task->room) . ")";
    }
    ?>
</h2>

<em><strong><?= Yii::t('TaskModule.views_index_index', 'Participants') ?>:</strong></em><br>
<?php $participantCount = 0 ?>
<?php foreach ($task->participants as $participant) : ?>
    <?php if($participantCount != 0) : ?>
       <?= ', ' ?>
    <?php endif; ?>
    <?php $user = $participant->user; ?>
    <?php if ($user): ?>
        <?= Html::encode($user->displayName); ?>
    <?php else: ?>
        <?= Html::encode($participant->name); ?>
    <?php endif; ?>
    <?php $participantCount++ ?>
<?php endforeach; ?>
<?php if ($task->external_participants != null) : ?>
    <br><br>
    <em><strong><?= Yii::t('TaskModule.views_index_index', 'External participants') ?>:</strong></em><br>
    <?= $task->external_participants; ?>
<?php endif; ?>
<br>
<br>
<hr>

<?php foreach ($task->items as $item): ?>
    <h1>
        <?php if ($item->begin != "00:00" && $item->end != "00:00") : ?>
            <?= Html::encode($item->getTimeRangeText()); ?> -
        <?php endif; ?>
        <?= Html::encode($item->title); ?>
    </h1>
    <?= \humhub\widgets\MarkdownView::widget(array('markdown' => $item->description)); ?>
    <table style="border-spacing: 5px;">
        <tr>
            <td style="vertical-align: top;">
                <strong><?= Yii::t('TaskModule.views_index_index', 'Moderators'); ?></strong>:
            </td>
            <td style="vertical-align: top;">
                <?php $moderatorCount = 0 ?>
                <?php foreach ($item->moderators as $moderator) : ?>
                    <?= ($moderatorCount != 0) ? ', ': '' ?>
                    <?php $user = $moderator->user; ?>
                    <?php if ($user): ?>
                        <?= Html::encode($user->displayName); ?>
                    <?php else: ?>
                        <?= Html::encode($moderator->name); ?>
                    <?php endif; ?>
                    <?php $moderatorCount++ ?>
                <?php endforeach; ?>
                <?php if ($item->external_moderators != null) : ?>
                    <?php
                    if (count($item->moderators) != 0) {
                        echo ", ";
                    }
                    ?>
                    <?= $item->external_moderators; ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php if ($item->notes != null) : ?>
            <tr>
                <td style="vertical-align: top;">
                    <strong><?= Yii::t('TaskModule.views_index_index', 'Protocol'); ?></strong>:
                </td>
                <td style="vertical-align: top;"><?= \humhub\widgets\MarkdownView::widget(array('markdown' => $item->notes)); ?></td>
            </tr>
        <?php endif; ?>

        <?php if ($contentContainer->isModuleEnabled('tasks') && count($item->getTasks()) != 0) : ?>
            <tr>
                <td style="vertical-align: top;"><strong><?= Yii::t('TaskModule.views_index_index', 'Tasks'); ?></strong>:</td>
                <td style="vertical-align: top;">
                    <?php foreach ($item->getTasks() as $task): ?>
                        <?php
                        $taskStatus = "open";
                        if ($task->status == \humhub\modules\tasks\models\Task::STATUS_FINISHED) {
                            $taskStatus = "finished";
                        }
                        ?>
                        <?php if ($taskStatus == "open") : ?>
                            <i class="fa fa-square-o"> </i>
                        <?php else: ?>
                            <i class="fa fa-check-square-o"> </i>
                        <?php endif; ?>
                        - <?= $task->title; ?>
                        <?php if ($task->hasDeadline()) : ?>
                            (
                            <?php
                            $timestamp = strtotime($task->deadline);
                            $style = "";

                            if (date("d.m.yy", $timestamp) <= date("d.m.yy", time())) {
                                $style = "label label-danger";
                            }
                            ?>
                            <span style="<?= $style; ?>"><?= date("d. M", $timestamp); ?></span>
                            )
                            
                        <?php endif; ?>
                        <?php $assignedUsers = $task->assignedUsers; ?>
                        
                        <?php if (count($assignedUsers) != 0) : ?>
                            &rarr
                            <?php $userCount = 0 ?>
                            <?php foreach ($assignedUsers as $user): ?>
                                <?= ($userCount == 0) ? '' : ', ' ?>
                                <?= Html::encode($user->displayName); ?>
                                <?php $userCount++ ?>
                            <?php endforeach; ?>
                        <?php endif; ?><br>
                    <?php endforeach; ?>
                </td>
            </tr>

        <?php endif; ?>

    </table>
    <hr>
<?php endforeach; ?>

<script type="text/javascript">
    window.print();
</script>
