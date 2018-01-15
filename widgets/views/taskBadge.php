<?php if (/*$task->isToday()*/ false) : ?>
    <div class="label label-danger <?= $right ? 'pull-right' : '' ?>"><?= Yii::t('TaskModule.views_index_index', 'Today'); ?></div>
<?php elseif (/*$task->isTomorrow()*/ false) : ?>
    <div class="label label-warning <?= $right ? 'pull-right' : '' ?>"><?= Yii::t('TaskModule.views_index_index', 'Tomorrow'); ?></div>
<?php endif; ?>