<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: davidborn
 */

namespace humhub\modules\task\widgets;


use humhub\modules\task\models\Task;
use humhub\widgets\JsWidget;
use yii\helpers\Url;

class TaskItemList extends JsWidget
{
    /**
     * @inheritdoc
     */
    public $jsWidget = 'task.ItemList';

    /**
     * @inheritdoc
     */
    public $id = 'task-items';

    /**
     * @inheritdoc
     */
    public $init = true;

    /**
     * @var Task
     */
    public $task;

    /**
     * @var Task
     */
    public $canEdit;

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('taskItemList', [
            'options' => $this->getOptions(),
            'items' => $this->task->items,
            'task' => $this->task,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $contentContainer = $this->task->content->container;
        return [
            'task-id' => $this->task->id,
            'can-edit' => $this->canEdit
        ];
    }


}