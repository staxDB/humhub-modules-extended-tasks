<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\task\controllers;

use humhub\modules\content\permissions\ManageContent;
use humhub\modules\task\models\forms\TaskFilter;
use humhub\modules\task\models\forms\TaskForm;
use humhub\modules\task\models\TaskPicker;
use humhub\modules\task\models\TaskAssigned;
use humhub\modules\task\permissions\ManageTasks;
use humhub\modules\task\widgets\TaskListView;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\models\UserPicker;
use humhub\widgets\ModalClose;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\file\models\File;
use humhub\modules\content\models\Content;
use humhub\modules\task\models\Task;
use humhub\modules\task\models\TaskItem;
//use humhub\modules\meeting\models\MeetingTask;
use humhub\modules\stream\actions\Stream;

/**
 * Description of IndexController
 *
 * @author luke, buddh4
 */
class IndexController extends ContentContainerController
{
    /**
     * @inheritdoc
     */
    public $strictGuestMode = true;

    public function getAccessRules()
    {
        return [
            ['permission' => ManageTasks::class,
                'actions' => [
                    'task-user-picker',
                    'sub-task-picker',
                    'send-invite-notifications'.
                    'edit',
                    'delete',
                    'calendar-update'
                ]
            ],
            [
                'userGroup' => Space::USERGROUP_MEMBER,
                'actions' => 'index'
            ]
        ];
    }

    public function actionIndex()
    {
        $tasks = Task::findPendingTasks($this->contentContainer)->all();

        return $this->render("index", [
                    'pendingTasks' => $tasks,
                    'canEdit' => $this->canEdit(),
                    'contentContainer' => $this->contentContainer,
                    'filter' => new TaskFilter(['contentContainer' => $this->contentContainer])
        ]);
    }

    public function actionView($id)
    {
        $task = Task::find()->contentContainer($this->contentContainer)->where(['task.id' => $id])->one();

        if(!$task) {
            throw new HttpException(404);
        }

        if(!$task->content->canView() && !$task->isTaskAssigned()) {
            throw new HttpException(403);
        }

        return $this->render("task", [
                    'task' => $task,
                    'contentContainer' => $this->contentContainer
        ]);
    }

    public function actionModal($id, $cal)
    {
        $task = Task::find()->contentContainer($this->contentContainer)->where(['task.id' => $id])->one();

        if(!$task) {
            throw new HttpException(404);
        }

        if(!$task->content->canView()) {
            throw new HttpException(403);
        }

        return $this->renderAjax('modal', [
            'task' => $task,
            'editUrl' => $this->contentContainer->createUrl('/task/index/edit', ['id' => $task->id, 'cal' => $cal]),
            'canManageEntries' => $task->content->canEdit(),
            'contentContainer' => $this->contentContainer,
        ]);
    }

    public function actionTaskAssignedPicker($id = null, $keyword)
    {
        if($id) {
            $subQuery = TaskAssigned::find()->where(['task_user.task_id' => $id])->andWhere('task_user.user_id=user.id');
            $query = $this->getSpace()->getMembershipUser()->where(['not exists', $subQuery]);
        } else {
            $query = $this->getSpace()->getMembershipUser();
        }

        return $this->asJson(UserPicker::filter([
            'keyword' => $keyword,
            'query' => $query,
            'fillUser' => true
        ]));
    }

    public function actionSubTaskPicker($id = null, $keyword)
    {
        if($id) {
            $subQuery = Task::find()->where(['task.id' => $id]);
            $query = Task::find()->where(['task.title' => $keyword])->orWhere(['task.description' => $keyword]);
//            $query = $this->getSpace()->getMembershipUser()->where(['not exists', $subQuery]);
        } else {
            $query = Task::find()->where(['task.title' => $keyword])->orWhere(['task.description' => $keyword]);
//            $query = $this->getSpace()->getMembershipUser();
        }

//        echo '<pre>';
//        print_r($query);
//        echo '</pre>';
//        die();

        return $this->asJson(TaskPicker::filter([
            'keyword' => $keyword,
            'query' => $query,
//            'fillUser' => true
        ]));
    }

    public function actionFilterTasks()
    {
        $filter = new TaskFilter(['contentContainer' => $this->contentContainer]);
        $filter->load(Yii::$app->request->post());

        return $this->asJson([
            'success' => true,
            'output' => TaskListView::widget(['filter' => $filter])
        ]);
    }

    public function actionPrint($id)
    {
        $task = Task::find()->contentContainer($this->contentContainer)->where(['task.id' => $id])->one();

        return $this->renderPartial("print", [
                    'task' => $task,
                    'contentContainer' => $this->contentContainer
        ]);
    }

    public function actionEdit($id = null, $itemId = null, $cal = false)
    {
        if (!$id) {
            $taskForm = new TaskForm(['itemId' => $itemId, 'cal' => $cal]);
            $taskForm->createNew($this->contentContainer);
        } else {
            $taskForm = new TaskForm([
                'task' => Task::find()->contentContainer($this->contentContainer)->where(['task.id' => $id])->one(),
                'itemId' => $itemId,
                'cal' => $cal
            ]);
        }

        if(!$taskForm->task) {
            throw new HttpException(404);
        }

        //Set newAnswers, and editAnswers which will be saved by afterSave of the poll class
        $taskForm->task->setNewItems(Yii::$app->request->post('newItems'));
        $taskForm->task->setEditItems(Yii::$app->request->post('items'));

        if ($taskForm->load(Yii::$app->request->post()) && $taskForm->save()) {
            if($cal) {
                return ModalClose::widget(['saved' => true]);
            }

            return $this->htmlRedirect($this->contentContainer->createUrl('view', ['id' => $taskForm->task->id]));
        }

        return $this->renderAjax('edit', ['taskForm' => $taskForm]);
    }

    public function actionDuplicate($id, $itemId = null)
    {
        $task = Task::find()->contentContainer($this->contentContainer)->where(['task.id' => $id])->one();
        $task->duplicated();

        // We reset the duplicate id in case this is a shift item action, so we prevent other items from beeing copied.
        if($itemId) {
            $id = null;
        }

        return $this->renderAjax('edit', ['taskForm' => new TaskForm(['task' => $task, 'duplicateId' => $id, 'itemId' => $itemId])]);
    }

    public function actionDelete($id, $cal = false)
    {
        $this->forcePostRequest();

        $task = Task::find()->contentContainer($this->contentContainer)->where(['task.id' => $id])->one();
        if ($task) {
            $task->delete();
        } else {
            throw new HttpException(404);
        }

        if(!$cal) {
            return $this->htmlRedirect($this->contentContainer->createUrl('index'));
        } else {
            return ModalClose::widget();
        }
    }

    public function canEdit()
    {
        return $this->contentContainer->getPermissionManager()->can(new ManageTasks());
    }

    public function actionShare($id)
    {
        $task = Task::find()->contentContainer($this->contentContainer)->where(['task.id' => $id])->one();

        if(!$task->content->canView()) {
            throw new HttpException(403);
        }

        return $this->renderAjax('share', ['task' => $task, 'contentContainer' => $this->contentContainer, 'canEdit' => $this->canEdit()]);
    }

    public function actionGetIcs($id, $type = null)
    {
        $task = Task::find()->contentContainer($this->contentContainer)->where(['task.id' => $id])->one();

        if(!$task) {
            throw new HttpException(404);
        }

        if(!$task->content->canView()) {
            throw new HttpException(403);
        }

        return $this->renderAjax('getICS', ['task' => $task, 'type' => $type, 'contentContainer' => $this->contentContainer]);
    }

    public function actionCalendarUpdate($id)
    {
        $this->forcePostRequest();

        $task = Task::find()->contentContainer($this->contentContainer)->where(['task.id' => $id])->one();

        if (!$task) {
            throw new HttpException('404');
        }

        if (!($task->content->canEdit())) {
            throw new HttpException('403');
        }

        $taskForm = new TaskForm(['task' => $task]);

        if ($taskForm->updateTime(Yii::$app->request->post('start'), Yii::$app->request->post('end'))) {
            return $this->asJson(['success' => true]);
        }

        throw new HttpException(400, "Could not save! " . print_r($taskForm->getErrors()));
    }

    public function actionSendInviteNotifications($id)
    {
        $task = Task::find()->contentContainer($this->contentContainer)->where(['task.id' => $id])->one();

        if(!$task) {
            throw new HttpException(404);
        }

        if(!$this->canEdit()) {
            throw new HttpException(403);
        }

        $task->inviteUser();

        return $this->asJson([
            'success' => true
        ]);
    }

    /**
     * Confirm an Announcement
     */
    public function actionConfirm()
    {
        Yii::$app->response->format = 'json';

        $task = $this->getTaskByParameter();

        $items = Yii::$app->request->post('item');

        // Build array of answer ids
        $results = array();
        if (is_array($items)) {
            foreach ($items as $item_id => $flag) {
                $results[] = (int) $item_id;
            }
        } else {
            $results[] = $items;
        }

        $task->resetItems();
        $task->confirm($results);

        return $this->render("task", [
            'task' => $task,
            'contentContainer' => $this->contentContainer
        ]);
    }

    public function actionStatus($id, $status)
    {
//        Yii::$app->response->format = 'json';

        $task = Task::find()->contentContainer($this->contentContainer)->where(['task.id' => $id])->one();

        if(!$task) {
            throw new HttpException(404);
        }

        if(!$task->content->canView() && !$task->isTaskAssigned()) {
            throw new HttpException(403);
        }

        $task->updateAttributes(['status' => $status]);

        return $this->render("task", [
            'task' => $task,
            'contentContainer' => $this->contentContainer
        ]);
    }

    /**
     * Returns a given tasItem by given request parameter.
     *
     * This method also validates access rights of the requested task object.
     */
    private function getTaskByParameter()
    {
        $taskId = (int) Yii::$app->request->get('taskID');

        $task = Task::find()->contentContainer($this->contentContainer)->readable()->where(['task.id' => $taskId])->one();

        if ($task == null) {
            throw new HttpException(401, Yii::t('TaskModule.controller', 'Could not load Task!'));
        }

        if (!$task->content->canRead()) {
            throw new HttpException(401, Yii::t('TaskModule.controller', 'You have insufficient permissions to perform that operation!'));
        }

        return $task;
    }

}
