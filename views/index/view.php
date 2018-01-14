<?php
use humhub\modules\content\widgets\PinLink;
use humhub\modules\stream\assets\StreamAsset;
use humhub\modules\stream\actions\Stream;

/* @var $task \humhub\modules\task\models\Task */
/* @var $collapse boolean */
?>
<?php StreamAsset::register($this); ?>

<div data-action-component="stream.SimpleStream">
    <?= Stream::renderEntry($task, [
        'controlsOptions' => [
            'prevent' => [PinLink::class]
        ]
    ])?>
</div>

