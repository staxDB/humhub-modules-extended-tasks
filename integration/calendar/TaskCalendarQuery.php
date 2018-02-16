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
        $this->_query->leftJoin('task_user', 'task.id=task_user.task_id AND task_user.user_id=:userId', [':userId' => $this->_user->id]);
    }



    /**
     * Builds and executes the filter query.
     * This method will filter out entries not readable by the current logged in user.
     * @return [] result
     */
    public function all()
    {
        $this->_query
            ->andWhere(['task.scheduling' => 1])
            ->andWhere(['task.cal_mode' => Task::CAL_MODE_SPACE])
            ->andWhere(['!=', 'task.status', Task::STATUS_COMPLETED]);

        try {
            if (!$this->_built) {
                $this->setupQuery();
            }

            return $this->_query->all();
        } catch(FilterNotSupportedException $e) {
            return [];
        }
    }

}