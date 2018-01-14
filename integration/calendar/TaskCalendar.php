<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\task\integration\calendar;

use DateTime;
use humhub\modules\task\models\Task;
use humhub\widgets\Label;
use Yii;
use yii\base\Object;
use yii\helpers\Url;

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 14.09.2017
 * Time: 12:28
 */

class TaskCalendar extends Object
{
    /**
     * Default color of task type calendar items.
     */
    const DEFAULT_COLOR = '#2c99d6';

    const ITEM_TYPE_KEY = 'task';

    /**
     * @param $event \humhub\modules\calendar\interfaces\CalendarItemTypesEvent
     * @return mixed
     */
    public static function addItemTypes($event)
    {
        $event->addType(static::ITEM_TYPE_KEY, [
            'title' => Yii::t('TaskModule.base', 'Task'),
            'color' => static::DEFAULT_COLOR,
            'icon' => 'fa-calendar-o'
        ]);
    }

    /**
     * @param $event \humhub\modules\calendar\interfaces\CalendarItemsEvent
     */
    public static function addItems($event)
    {
        /* @var $tasks Task[] */
        $tasks = TaskCalendarQuery::findForEvent($event);

        $items = [];
        foreach ($tasks as $task) {
            $items[] = [
                'start' => $task->getBeginDateTime(),
                'end' => $task->getEndDateTime(),
                'title' => $task->title,
                'editable' => true,
                'icon' => 'fa-calendar-o',
                'viewUrl' => $task->content->container->createUrl('/task/index/modal', ['id' => $task->id, 'cal' => true]),
                'openUrl' => $task->content->getUrl(),
                'updateUrl' => $task->content->container->createUrl('/task/index/calendar-update', ['id' => $task->id]),
            ];
        }

        $event->addItems(static::ITEM_TYPE_KEY, $items);
    }

}