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
 * Date: 02.07.2017
 * Time: 21:24
 */

namespace humhub\modules\task\models;


use Yii;
use yii\base\Model;

class ShiftTaskChoose extends Model
{
    public $taskId;

    public $itemId;

    /**
     * @var Task[]
     */
    public $tasks = [];

    public $contentContainer;

    private $item;

    private $items;

    public function rules()
    {
        return [
            ['taskId', 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'taskId' => Yii::t('TaskModule.views_item_shift', 'Choose upcoming task')
        ];
    }

    public function getItem()
    {
        if(!$this->item) {
            $this->item = TaskItem::find()->contentContainer($this->contentContainer)->andWhere(['task_item.id' => $this->itemId])->one();
        }
        return $this->item;
    }

    public function getItems()
    {
        if($this->items === null) {
            $item = $this->getItem();
            $this->tasks = Task::findPendingTasks($this->contentContainer)->andWhere(['<>', 'task.id', $item->task_id])->all();

            $this->items = [];
            foreach ($this->tasks as $task) {
                $this->items[$task->id] = $task->title.' - '.$task->getFormattedStartDate();
            }
        }

        return $this->items;
    }

    public function shiftItem()
    {
        if(!$this->validate()) {
            return false;
        }

        // Load new task and shift item.
        $task = Task::find()->contentContainer($this->contentContainer)->where(['task.id' => $this->taskId])->one();
        return $task->shiftItem($this->itemId);
    }
}