<?php if ($meeting->isToday()) : ?>
    <div class="label label-danger <?= $right ? 'pull-right' : '' ?>"><?= Yii::t('TaskModule.views_index_index', 'Today'); ?></div>
<?php elseif ($meeting->isTomorrow()) : ?>
    <div class="label label-warning <?= $right ? 'pull-right' : '' ?>"><?= Yii::t('TaskModule.views_index_index', 'Tomorrow'); ?></div>
<?php endif; ?>