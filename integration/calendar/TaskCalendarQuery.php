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

    public $startField = 'date';
    public $endField = 'date';
    public $dateFormat = 'Y-m-d';

    /**
     * @inheritdoc
     */
    public function filterIsParticipant()
    {
        $this->_query->leftJoin('task_participant', 'task.id=task_participant.task_id AND task_participant.user_id=:userId', [':userId' => $this->_user->id]);
        $this->_query->andWhere('task_participant.id IS NOT NULL');
    }
}