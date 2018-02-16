<?php

use humhub\modules\space\widgets\Menu;
use humhub\commands\IntegrityController;
use humhub\commands\CronController;
use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\space\widgets\Sidebar as SpaceSidebar;
use humhub\modules\space\models\Membership;

return [
    'id' => 'task',
    'class' => 'humhub\modules\task\Module',
    'namespace' => 'humhub\modules\task',
    'events' => [
        ['class' => Menu::className(), 'event' => Menu::EVENT_INIT, 'callback' => ['humhub\modules\task\Events', 'onSpaceMenuInit']],
        ['class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => ['humhub\modules\task\Events', 'onIntegrityCheck']],
        ['class' => CronController::className(), 'event' => CronController::EVENT_ON_HOURLY_RUN, 'callback' => ['humhub\modules\task\Events', 'onCronRun']],
        ['class' => Sidebar::className(), 'event' => Sidebar::EVENT_INIT, 'callback' => ['humhub\modules\task\Events', 'onDashboardSidebarInit']],
        ['class' => SpaceSidebar::className(), 'event' => SpaceSidebar::EVENT_INIT, 'callback' => ['humhub\modules\task\Events', 'onSpaceSidebarInit']],
        ['class' => Membership::className(), 'event' => Membership::EVENT_MEMBER_REMOVED, 'callback' => ['humhub\modules\task\Events', 'onMemberRemoved']],
        ['class' => 'humhub\modules\calendar\interfaces\CalendarService', 'event' => 'getItemTypes', 'callback' => ['humhub\modules\task\Events', 'onGetCalendarItemTypes']],
        ['class' => 'humhub\modules\calendar\interfaces\CalendarService', 'event' => 'findItems', 'callback' => ['humhub\modules\task\Events', 'onFindCalendarItems']],
    ]
];
?>