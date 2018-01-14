<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\meeting;

use humhub\modules\meeting\integration\calendar\MeetingCalendar;
use humhub\modules\meeting\models\MeetingTask;
use humhub\modules\meeting\widgets\TaskAddon;
use Yii;
use yii\base\Object;

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 14.09.2017
 * Time: 12:12
 */
class Events extends Object
{
//    /**
//     * @param $event \humhub\modules\calendar\interfaces\CalendarItemTypesEvent
//     * @return mixed
//     */
//    public static function onGetCalendarItemTypes($event)
//    {
//        $contentContainer = $event->contentContainer;
//
//        if(!$contentContainer || $contentContainer->isModuleEnabled('task')) {
//            MeetingCalendar::addItemTypes($event);
//        }
//    }
//
//    /**
//     * @param $event \humhub\modules\calendar\interfaces\CalendarItemsEvent;
//     */
//    public static function onFindCalendarItems($event)
//    {
//        $contentContainer = $event->contentContainer;
//
//        if(!$contentContainer || $contentContainer->isModuleEnabled('task')) {
//            MeetingCalendar::addItems($event);
//        }
//    }

    public static function onSpaceMenuInit($event)
    {
        /* @var $space \humhub\modules\space\models\Space */

        $space = $event->sender->space;

        if ($space->isModuleEnabled('task') && $space->isMember()) {

            $event->sender->addItem([
                'label' => Yii::t('TaskModule.base', 'Tasks'),
                'group' => 'modules',
                'url' => $space->createUrl('//task/index'),
                'icon' => '<i class="fa fa-calendar-o"></i>',
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'task'),
            ]);
        }
    }

}