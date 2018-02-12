<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 14.09.2017
 * Time: 20:44
 */

namespace humhub\modules\task\integration\calendar;

use humhub\modules\calendar\interfaces\AbstractCalendarQuery;
use humhub\modules\task\models\Task;

class TaskCalendarQuery extends AbstractCalendarQuery
{
    /**
     * @inheritdoc
     */
    protected static $recordClass = Task::class;

    public $startField = 'start_datetime';
    public $endField = 'end_datetime';
    public $dateFormat = 'Y-m-d H:i:s';

    /**
     * @inheritdoc
     */
    public function filterIsParticipant()
    {
        $this->_query->leftJoin('task_assigned', 'task.id=task_assigned.task_id AND task_assigned.user_id=:userId', [':userId' => $this->_user->id]);
        $this->_query->leftJoin('task_responsible', 'task.id=task_responsible.task_id AND task_responsible.user_id=:userId', [':userId' => $this->_user->id]);
    }


}