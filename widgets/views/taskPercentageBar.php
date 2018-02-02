<?php if ($task->hasItems()) : ?>
    <!--    Progress Bar    -->
    <?php
    $percent = round($task->getPercent());
//    $percent = round(68);
    $color = "progress-bar-info";
    ?>
    <div class="col-md-5" style="padding-left: 0; padding-right: 30px;">
    <div class="progress">
        <div id="task_progress_<?= $task->id; ?>"
             class="progress-bar <?= $color; ?>"
             role="progressbar"
             aria-valuenow="<?= $percent; ?>" aria-valuemin="0" aria-valuemax="100"
             style="width: 0%">
        </div>
    </div>
    <script type="text/javascript">
        $('#task_progress_<?= $task->id; ?>').css('width', '<?= $percent; ?>%');
    </script>
    </div>
<?php else : ?>
<?php endif; ?>