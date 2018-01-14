<?php

use yii\helpers\Html;
use humhub\modules\tasks\models\Task;

\humhub\modules\task\assets\PrintAssets::register($this);

?>

<div class="print-body">
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

    <?=
    implode(", ", array_map(function ($p) {
                return ($p->getUser()) ? Html::encode($p->getUser()->displayName) : Html::encode($p->name);
            }, $task->participants));
    ?>
    <?php if ($task->external_participants != null) : ?>
        <br><br>
        <em><strong><?= Yii::t('TaskModule.views_index_index', 'External participants') ?>:</strong></em><br>
        <?= $task->external_participants; ?>
    <?php endif; ?>
    <hr>
</div>


<div class="task-details">
    <div class="task-item-container">
        <div class="agenda-time-line">
            <?php foreach ($task->items as $item): ?>
                <div class="agenda-point"><i style="font" class="fa fa-circle" aria-hidden="true"></i></div>
                <div style="margin-left:40px;padding-right:15px;">
                    
                    <!-- Title + Description start -->
                    <h1>
                        <?php if ($item->begin != "00:00" && $item->end != "00:00") : ?>
                            <?= Html::encode($item->getTimeRangeText()); ?> -
                        <?php endif; ?>
                        <?= Html::encode($item->title); ?>
                    </h1>
                    <?= \humhub\widgets\MarkdownView::widget(array('markdown' => $item->description)); ?>
                    <!-- Title + Description end -->
                    
                    <br />
                    
                    <!-- Task Infos start -->
                    <table class="task-print" style="border-spacing: 5px;">
                       
                        <!-- MODERATORS start -->
                        <tr>
                            <td style="vertical-align: top;width:120px;">
                                <strong><?= Yii::t('TaskModule.views_index_index', 'Moderators'); ?></strong>:&nbsp;
                            </td>
                            <td style="vertical-align: top;">
                                <?=
                                implode(", ", array_map(function ($p) {
                                            return ($p->getUser()) ? Html::encode($p->user->displayName) : Html::encode($p->name);
                                        }, $item->moderators));
                                ?>
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
                        <!-- MODERATORS end -->
                        
                        <!-- PROTOCOL start -->
                        <?php if ($item->notes != null) : ?>
                            <tr>
                                <td style="vertical-align: top;">
                                    <strong><?= Yii::t('TaskModule.views_index_index', 'Protocol'); ?></strong>:
                                </td>
                                <td style="vertical-align: top;"><?= \humhub\widgets\MarkdownView::widget(array('markdown' => $item->notes)); ?></td>
                            </tr>
                        <?php endif; ?>
                        <!-- PROTOCOL end -->

                        <!-- TASKS start -->
                        <?php if ($contentContainer->isModuleEnabled('tasks') && count($item->getTasks()) != 0) : ?>
                            <tr>
                                <td style="vertical-align: top;"><strong><?= Yii::t('TaskModule.views_index_index', 'Tasks'); ?></strong>:</td>
                                <td style="vertical-align: top;">
                                    <?php foreach ($item->getTasks() as $task): ?>
                                        <?php $taskStatus = ($task->status == Task::STATUS_FINISHED) ? "finished" : "open"; ?>
                                        <?php if ($taskStatus == "open") : ?>
                                            <i class="fa fa-square-o"> </i>
                                        <?php else: ?>
                                            <i class="fa fa-check-square-o"> </i>
                                        <?php endif; ?>
                                            
                                        &nbsp;<?= $task->title; ?>
                                        
                                        <?php if ($task->hasDeadline()) : ?>
                                            (
                                            <?php
                                                $timestamp = strtotime($task->deadline);
                                                $style = "";

                                                if (date("d.m.yy", $timestamp) <= date("d.m.yy", time())) {
                                                    $style = "label label-danger";
                                                }
                                            ?>
                                            <span style="<?= $style; ?>"><?= Yii::$app->formatter->asDate(new DateTime($task->deadline), 'short'); ?></span>
                                            )

                                        <?php endif; ?>
                                        <?php $assignedUsers = $task->assignedUsers; ?>

                                        <?php if (count($assignedUsers) != 0) : ?>
                                            &nbsp;<i class="fa fa-caret-right" aria-hidden="true"></i>&nbsp;
                                            <?=
                                            implode(", ", array_map(function ($p) {
                                                        return Html::encode($p->displayName);
                                                    }, $assignedUsers));
                                            ?>

                                        <?php endif; ?><br>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <!-- TASKS end -->
                    </table>
                    <!-- Task Infos end -->
                    
                </div>
                <div class="print-body">
                <hr>
                </div>
<?php endforeach; ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    window.print();
</script>
