<?php

namespace humhub\modules\task\widgets;

use humhub\components\Widget;
//use humhub\modules\calendar\interfaces\CalendarService;
use humhub\modules\task\models\Task;
use humhub\modules\task\models\SnippetModuleSettings;
use humhub\modules\content\components\ContentContainerActiveRecord;
use Yii;
use yii\helpers\Url;

/**
 * UpcomingEvents shows next events in sidebar.
 *
 * @package humhub.modules_core.calendar.widgets
 * @author luke
 */
class MyTasks extends Widget
{

    /**
     * ContentContainer to limit events to. (Optional)
     *
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    /**
     * How many days in future events should be shown?
     *
     * @var int
     */
    public $daysInFuture = 7;

    public function run()
    {
        $settings = SnippetModuleSettings::instantiate();
        $taskEntries = Task::findUserTasks();

        if (empty($taskEntries)) {
            return;
        }

        return $this->render('myTasks', ['taskEntries' => $taskEntries]);
    }

}
