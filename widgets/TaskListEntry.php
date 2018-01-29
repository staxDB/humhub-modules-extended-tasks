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
 * Date: 29.06.2017
 * Time: 17:52
 */

namespace humhub\modules\task\widgets;


use humhub\components\Widget;

class TaskListEntry extends Widget
{
    public $task;
    public $canEdit;
    public $contentContainer;

    public function run()
    {
        return $this->render('taskListEntry', [
            'task' => $this->task,
            'url' => $this->contentContainer->createUrl('/task/index/view', ['id' => $this->task->id]),
            'editUrl' => $this->contentContainer->createUrl('/task/index/edit', ['id' => $this->task->id]),
            'canEdit' => $this->canEdit
        ]);
    }

}