<?php
/**
 * Created by PhpStorm.
 * User: Buddha
 * Date: 21.06.2017
 * Time: 13:59
 */

namespace humhub\modules\task\widgets;


use humhub\modules\task\models\Task;
use humhub\modules\task\models\TaskItem;
use humhub\modules\user\models\fieldtype\DateTime;
use humhub\widgets\JsWidget;

class TaskItemWidget extends JsWidget
{
    /**
     * @inheritdoc
     */
    public $jsWidget = 'task.Item';

    /**
     * @inheritdoc
     */
    public $init = true;

    /**
     * @var Task
     */
    public $task;

    /**
     * @var TaskItemWidget
     */
    public $item;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $contentContainer = $this->task->content->container;
        return $this->render('_item', [
            'options' => $this->getOptions(),
            'task' => $this->task,
            'item' => $this->item,
            'contentContainer' => $this->task->content->container,
        ]);
    }

    public function getData()
    {
        $contentContainer = $this->task->content->container;

        return [
            'task-id' => $this->task->id,
            'item-id' => $this->item->id,
            'sort-order' => $this->item->sort_order,
            'can-resort' => $this->task->isTaskResponsible(),
            'check-url' => $contentContainer->createUrl('/task/index/check-item', ['id' => $this->item->id, 'taskId' => $this->task->id]),
        ];
    }
}