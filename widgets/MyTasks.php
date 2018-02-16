<?php

namespace humhub\modules\task\widgets;

use humhub\components\Widget;
use humhub\modules\task\models\Task;
use humhub\modules\task\models\SnippetModuleSettings;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * MyTasks shows users tasks in sidebar.
 *
 * @author davidborn
 */
class MyTasks extends Widget
{

    /**
     * ContentContainer to limit tasks to. (Optional)
     *
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    /**
     * How many tasks should be shown?
     *
     * @var int
     */
    public $limit = 5;

    public function run()
    {
        $settings = SnippetModuleSettings::instantiate();
        $taskEntries = Task::findUserTasks(null, $this->limit);

        if (empty($taskEntries)) {
            return;
        }

        return $this->render('myTasks', ['taskEntries' => $taskEntries]);
    }

}
