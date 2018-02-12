<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\task;

//use humhub\modules\task\integration\calendar\TaskCalendar;
use humhub\modules\task\jobs\SendReminder;
use humhub\modules\task\models\SnippetModuleSettings;
use humhub\modules\task\models\Task;
use humhub\modules\task\models\TaskAssigned;
use humhub\modules\task\models\TaskItem;
use humhub\modules\task\models\TaskReminder;
use humhub\modules\task\models\TaskResponsible;
use humhub\modules\task\integration\calendar\TaskCalendar;
use humhub\modules\task\widgets\MyTasks;
use Yii;
use yii\base\Object;

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 14.09.2017
 * Time: 12:12
 */
class Events extends Object
{
    /**
     * @param $event \humhub\modules\calendar\interfaces\CalendarItemTypesEvent
     * @return mixed
     */
    public static function onGetCalendarItemTypes($event)
    {
        $contentContainer = $event->contentContainer;

        if(!$contentContainer || $contentContainer->isModuleEnabled('task')) {
            TaskCalendar::addItemTypes($event);
        }
    }

    /**
     * @param $event \humhub\modules\calendar\interfaces\CalendarItemsEvent;
     */
    public static function onFindCalendarItems($event)
    {
        $contentContainer = $event->contentContainer;

        if(!$contentContainer || $contentContainer->isModuleEnabled('task')) {
            TaskCalendar::addItems($event);
        }
    }

    public static function onDashboardSidebarInit($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $settings = SnippetModuleSettings::instantiate();

        if ($settings->showMyTasksSnippet()) {
            $event->sender->addWidget(MyTasks::className(), [], ['sortOrder' => $settings->myTasksSnippetSortOrder]);
        }
    }

    public static function onSpaceMenuInit($event)
    {
        /* @var $space \humhub\modules\space\models\Space */

        $space = $event->sender->space;

        if ($space->isModuleEnabled('task') && $space->isMember()) {

            $event->sender->addItem([
                'label' => Yii::t('TaskModule.base', 'Tasks'),
                'group' => 'modules',
                'url' => $space->createUrl('//task/index'),
                'icon' => '<i class="fa fa-tasks"></i>',
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'task'),
            ]);
        }
    }

    /**
     * Callback to validate module database records.
     *
     * @param Event $event
     * @throws \Exception
     */
    public static function onIntegrityCheck($event)
    {
        $integrityController = $event->sender;
        $integrityController->showTestHeadline("Tasks Module - Entries (" . Task::find()->count() . " entries)");

        // check for taskItems without task
        foreach (TaskItem::find()->all() as $taskItem) {
            if ($taskItem->task === null) {
                if ($integrityController->showFix("Deleting task item id " . $taskItem->id . " without existing task!")) {
                    $taskItem->delete();
                }
            }
        }

        // check for task responsible users without task or existing user
        foreach (TaskResponsible::find()->all() as $taskResponsible) {
            if ($taskResponsible->task === null) {
                if ($integrityController->showFix("Deleting task responsible user id " . $taskResponsible->id . " without existing task!")) {
                    $taskResponsible->delete();
                }
            }
            if ($taskResponsible->user === null) {
                if ($integrityController->showFix("Deleting task responsible user id " . $taskResponsible->id . " without existing user!")) {
                    $taskResponsible->delete();
                }
            }
        }

        // check for task assigned users without task or existing user
        foreach (TaskAssigned::find()->all() as $taskAssigned) {
            if ($taskAssigned->task === null) {
                if ($integrityController->showFix("Deleting task assigned user id " . $taskAssigned->id . " without existing task!")) {
                    $taskAssigned->delete();
                }
            }
            if ($taskAssigned->user === null) {
                if ($integrityController->showFix("Deleting task assigned user id " . $taskAssigned->id . " without existing user!")) {
                    $taskAssigned->delete();
                }
            }
        }

        // check for task reminders without task
        foreach (TaskReminder::find()->all() as $taskReminder) {
            if ($taskReminder->task === null) {
                if ($integrityController->showFix("Deleting task reminder id " . $taskReminder->id . " without existing task!")) {
                    $taskReminder->delete();
                }
            }
        }
    }


    public static function onCronRun($event)
    {
        if (Yii::$app->controller->action->id == 'hourly') {
            Yii::$app->queue->push( new SendReminder());
        }
//        if (Yii::$app->controller->action->id == 'hourly') {
//            Yii::$app->queue->push( new SendReminder() );
//        }
    }

}