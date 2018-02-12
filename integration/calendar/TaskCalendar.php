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
    const DEFAULT_COLOR = '#F4778E';

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
            'icon' => 'fa-tasks'
        ]);
    }

    /**
     * @param $event \humhub\modules\calendar\interfaces\CalendarItemsEvent
     */
    public static function addItems($event)
    {
       $tasks = TaskCalendarQuery::findForEvent($event);

        $items = [];
        foreach ($tasks as $task) {
            $items[] = $task->getFullCalendarArray();
        }

        $event->addItems(static::ITEM_TYPE_KEY, $items);
    }

}