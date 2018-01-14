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
 * Date: 01.07.2017
 * Time: 19:24
 */

namespace humhub\modules\task\widgets;


use humhub\components\Widget;
use humhub\modules\task\models\Task;

class TaskBadge extends Widget
{
    /**
     * @var Task
     */
    public $task;

    /**
     * @var Task
     */
    public $right;

    public function run()
    {
        return $this->render('taskBadge', [
            'task' => $this->task,
            'right' => $this->right
        ]);
    }

}