<?php

use humhub\modules\task\Events;
use humhub\modules\space\widgets\Menu;
use humhub\commands\IntegrityController;
use humhub\commands\CronController;
use humhub\modules\content\widgets\WallEntryAddons;
use yii\db\BaseActiveRecord;

return [
    'id' => 'task',
    'class' => 'humhub\modules\task\Module',
    'namespace' => 'humhub\modules\task',
    'events' => [
        ['class' => Menu::className(), 'event' => Menu::EVENT_INIT, 'callback' => ['humhub\modules\task\Events', 'onSpaceMenuInit']],
        ['class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => ['humhub\modules\task\Events', 'onIntegrityCheck']],
        ['class' => CronController::className(), 'event' => CronController::EVENT_ON_HOURLY_RUN, 'callback' => ['humhub\modules\task\Events', 'onCronRun']],

//        ['class' => WallEntryAddons::className(), 'event' => WallEntryAddons::EVENT_INIT, 'callback' => ['humhub\modules\task\Events', 'onTaskWallEntry']],
//        ['class' => 'humhub\modules\calendar\interfaces\CalendarService', 'event' => 'getItemTypes', 'callback' => ['humhub\modules\task\Events', 'onGetCalendarItemTypes']],
//        ['class' => 'humhub\modules\calendar\interfaces\CalendarService', 'event' => 'findItems', 'callback' => ['humhub\modules\task\Events', 'onFindCalendarItems']],
    ]
];
?>