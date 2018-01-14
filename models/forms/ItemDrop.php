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
 * Date: 25.06.2017
 * Time: 23:23
 */

namespace humhub\modules\task\models\forms;


use humhub\modules\task\models\Task;
use yii\base\Model;

class ItemDrop extends Model
{
    /**
     * @var integer
     */
    public $taskId;

    /**
     * @var Task
     */
    public $task;

    /**
     * @var integer
     */
    public $index;

    /**
     * @var integer
     */
    public $itemId;


    public function init()
    {
        $this->task = Task::findOne(['id' => $this->taskId]);
    }

    public function save()
    {
        $this->task->moveItemIndex($this->itemId, $this->index);
        return true;
    }

    public function rules()
    {
        return [
            [['itemId', 'index'], 'integer']
        ];
    }

}