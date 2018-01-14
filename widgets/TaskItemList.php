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
 * Time: 22:57
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
            'items' => $this->task->getItemsPopulated(),
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
            'drop-url' => $contentContainer->createUrl('/task/item/drop', ['taskId' => $this->task->id]),
            'can-edit' => $this->canEdit
        ];
    }


}