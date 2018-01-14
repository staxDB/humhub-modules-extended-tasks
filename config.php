<?php

use humhub\modules\task\Events;
use humhub\modules\space\widgets\Menu;
use humhub\modules\content\widgets\WallEntryAddons;
use yii\db\BaseActiveRecord;

return [
    'id' => 'task',
    'class' => 'humhub\modules\task\Module',
    'namespace' => 'humhub\modules\task',
    'events' => [
        ['class' => Menu::className(), 'event' => Menu::EVENT_INIT, 'callback' => ['humhub\modules\task\Events', 'onSpaceMenuInit']],
        ['class' => WallEntryAddons::className(), 'event' => WallEntryAddons::EVENT_INIT, 'callback' => ['humhub\modules\task\Events', 'onTaskWallEntry']],
//        ['class' => 'humhub\modules\calendar\interfaces\CalendarService', 'event' => 'getItemTypes', 'callback' => ['humhub\modules\task\Events', 'onGetCalendarItemTypes']],
//        ['class' => 'humhub\modules\calendar\interfaces\CalendarService', 'event' => 'findItems', 'callback' => ['humhub\modules\task\Events', 'onFindCalendarItems']],
    ]
];
?>