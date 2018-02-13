<?php

namespace humhub\modules\task\models;

use humhub\modules\calendar\interfaces\CalendarItem;
use humhub\modules\notification\models\Notification;
use humhub\modules\task\notifications\ExtensionRequest;
use humhub\modules\task\notifications\NotifyAssigned;
use humhub\modules\task\notifications\NotifyChangedDateTime;
use humhub\modules\task\notifications\NotifyResponsible;
use humhub\modules\task\notifications\NotifyStatusCompleted;
use humhub\modules\task\notifications\NotifyStatusCompletedAfterReview;
use humhub\modules\task\notifications\NotifyStatusInProgress;
use humhub\modules\task\notifications\NotifyStatusPendingReview;
use humhub\modules\task\notifications\NotifyStatusRejectedAfterReview;
use humhub\modules\task\notifications\NotifyStatusReset;
use humhub\modules\task\notifications\RemindEnd;
use humhub\modules\task\notifications\RemindStart;
use humhub\libs\Html;
use Yii;
use DateInterval;
use DateTime;
use DateTimeZone;
use humhub\libs\DbDateValidator;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\task\notifications\Invite;
use humhub\modules\task\widgets\WallEntry;
use humhub\modules\task\permissions\ManageTasks;
use humhub\modules\user\models\User;
use humhub\modules\search\interfaces\Searchable;
use yii\base\InvalidConfigException;
use yii\data\Sort;
use yii\db\ActiveQuery;
use humhub\modules\task\CalendarUtils;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Url;
use humhub\widgets\Label;

/**
 * This is the model class for table "task".
 *
 * The followings are the available columns in table 'task':
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property integer $review
 * @property integer $request_sent
 * @property integer $scheduling
 * @property integer $all_day
 * @property string $start_datetime
 * @property string $end_datetime
 * @property integer $status
 * @property integer $cal_mode
 * @property integer $parent_task_id
 * @property string $time_zone The timeZone this entry was saved, note the dates itself are always saved in app timeZone
 */
//class Task extends ContentActiveRecord implements Searchable
class Task extends ContentActiveRecord implements Searchable, CalendarItem
{

    /**
     * @inheritdocs
     */
    protected $managePermission = ManageTasks::class;

    /**
     * @inheritdocs
     */
    public $wallEntryClass = WallEntry::class;
    public $autoAddToWall = true;

    public $assignedUsers;
    public $responsibleUsers;
    public $selectedReminders;
    public $newItems;
    public $editItems;

    /**
     * Status
     */
    const STATUS_PENDING = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_PENDING_REVIEW = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_ALL = 4;

    /**
     * @var array all given statuses as array
     */
    public static $statuses = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_PENDING_REVIEW,
        self::STATUS_COMPLETED
    ];

    /**
     * Cal Modes
     */
    const CAL_MODE_NONE = 0;
    const CAL_MODE_SPACE = 1;

    /**
     * @var array all given cal modes as array
     */
    public static $calModes = [
        self::CAL_MODE_NONE,
        self::CAL_MODE_SPACE
    ];


    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'task';
    }

    /**
     * @inheritdoc
     */
    public function getContentName()
    {
        return Yii::t('TaskModule.base', 'Task');
    }

    /**
     * @inheritdoc
     */
    public function getContentDescription()
    {
        return $this->title;
    }

    public function getIcon()
    {
        return 'fa-tasks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['start_datetime', 'end_datetime'], 'required', 'when' => function ($model) {
                return $model->scheduling == 1;
            }, 'whenClient' => "function (attribute, value) {
                return $('#task-scheduling').val() == 1;
            }"],
            [['start_datetime'], DbDateValidator::className()],
            [['end_datetime'], DbDateValidator::className()],
            [['all_day', 'scheduling', 'review', 'request_sent'], 'integer'],
            [['cal_mode'], 'in', 'range' => self::$calModes],
            [['assignedUsers', 'description', 'responsibleUsers', 'selectedReminders'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => Yii::t('TaskModule.models_task', 'Title'),
            'description' => Yii::t('TaskModule.models_task', 'Description'),
            'review' => Yii::t('TaskModule.models_task', 'Review by responsible user required'),
            'request_sent' => Yii::t('TaskModule.models_task', 'Extend deadline request'),
            'scheduling' => Yii::t('TaskModule.models_task', 'Scheduling'),
            'all_day' => Yii::t('TaskModule.models_task', 'All Day'),
            'start_datetime' => Yii::t('TaskModule.models_task', 'Start'),
            'end_datetime' => Yii::t('TaskModule.models_task', 'End'),
            'status' => Yii::t('TaskModule.models_task', 'Status'),
            'cal_mode' => Yii::t('TaskModule.models_task', 'Calendar Mode'),
            'parent_task_id' => Yii::t('TaskModule.models_task', 'Parent Task'),
            'newItems' => Yii::t('TaskModule.models_task', 'Checklist Items'),
            'editItems' => Yii::t('TaskModule.models_task', 'Checklist Items'),
            'assignedUsers' => Yii::t('TaskModule.models_task', 'Assigned user(s)'),
            'responsibleUsers' => Yii::t('TaskModule.models_task', 'Responsible user(s)'),
            'selectedReminders' => Yii::t('TaskModule.models_task', 'Reminders'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->content->container->createUrl('/task/index/view', ['id' => $this->id]);
    }

    public static function findUserTasks(User $user = null, $limit = 5)
    {
        if (!$user && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        } else if (!$user) {
            return [];
        }

        $query1 = self::find()
            ->where(['!=', 'task.status', Task::STATUS_COMPLETED])
            ->leftJoin('task_responsible', 'task.id=task_responsible.task_id', [])
            ->andWhere(['task_responsible.user_id' => $user->id]);

        $query2 = self::find()
            ->where(['!=', 'task.status', Task::STATUS_COMPLETED])
            ->leftJoin('task_assigned', 'task.id=task_assigned.task_id', [])
            ->andWhere(['task_assigned.user_id' => $user->id]);

        $query3 = self::find()
            ->where(['!=', 'task.status', Task::STATUS_COMPLETED])
            ->leftJoin('task_responsible', 'task.id=task_responsible.task_id', [])
            ->where('ISNULL(task_responsible.task_id)');

        $query4 = self::find()
            ->where(['!=', 'task.status', Task::STATUS_COMPLETED])
            ->leftJoin('task_assigned', 'task.id=task_assigned.task_id', [])
            ->where('ISNULL(task_assigned.task_id)');

        $query5 = $query1->union($query2)->union($query3)->union($query4)
            ->orderBy([new Expression('-task.end_datetime DESC')])
            ->readable();

        return self::find()
            ->select('*')
            ->from([
                $query5,
            ])
            ->limit($limit)
            ->all();

//
//        return $query1->union($query2)->union($query3)->union($query4)
//            ->limit(2)
//            ->orderBy([new Expression('-task.end_datetime DESC')])
//            ->readable()
//            ->all();

//        return self::find()
//            ->leftJoin('task_assigned', 'task.id=task_assigned.task_id', [])
//            ->where(['task_assigned.user_id' => $user->id])
//            ->leftJoin('task_responsible', 'task.id=task_responsible.task_id', [])
//            ->where(['task_responsible.user_id' => $user->id])
//            ->andWhere(['!=', 'task.status', Task::STATUS_COMPLETED])
//            ->orderBy([new Expression('-task.end_datetime DESC')])
//            ->readable()
//            ->all();
    }

    public static function findPendingTasks(ContentContainerActiveRecord $container)
    {
        return self::find()
            ->contentContainer($container)
            ->orderBy([new Expression('-task.end_datetime DESC')])
            ->readable()
            ->andWhere(['!=', 'task.status', Task::STATUS_COMPLETED]);
    }

    public static function findReadable(ContentContainerActiveRecord $container)
    {
        return self::find()
            ->contentContainer($container)
            ->orderBy(['task.end_datetime' => SORT_DESC])
            ->readable();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // Check is a full day span
        if ($this->all_day == 0 && CalendarUtils::isFullDaySpan(new DateTime($this->start_datetime), new DateTime($this->end_datetime))) {
            $this->all_day = 1;
        }
        if (!$this->scheduling) {
            $this->start_datetime = new Expression("NULL");
            $this->end_datetime = new Expression("NULL");
        }

        if ($this->isAttributeChanged('start_datetime', true) || $this->isAttributeChanged('end_datetime', true)) {
            if ($this->request_sent) {
                $this->request_sent = 0;
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        foreach (TaskItem::findAll(['task_id' => $this->id]) as $item) {
            $item->delete();
        }

        foreach (TaskAssigned::findAll(['task_id' => $this->id]) as $taskAssigned) {
            $taskAssigned->delete();
        }

        foreach (TaskResponsible::findAll(['task_id' => $this->id]) as $taskResponsible) {
            $taskResponsible->delete();
        }

        foreach (TaskReminder::findAll(['task_id' => $this->id]) as $taskReminder) {
            $taskReminder->delete();
        }

        return parent::beforeDelete();
    }

    /**
     * Saves new items (if set) and updates items given edititems (if set)
     * @param type $insert
     * @param type $changedAttributes
     * @return boolean
     */
    public function afterSave($insert, $changedAttributes)
    {

        TaskAssigned::deleteAll(['task_id' => $this->id]);

        if (!empty($this->assignedUsers)) {
            foreach ($this->assignedUsers as $guid) {
                $this->addTaskAssigned($guid);
            }
        }

        TaskResponsible::deleteAll(['task_id' => $this->id]);

        if (!empty($this->responsibleUsers)) {
            foreach ($this->responsibleUsers as $guid) {
                $this->addTaskResponsible($guid);
            }
        }

        TaskReminder::deleteAll(['task_id' => $this->id]);

        if (!empty($this->selectedReminders)) {
            foreach ($this->selectedReminders as $remind_mode) {
                $this->addTaskReminder($remind_mode);
            }
        }

        parent::afterSave($insert, $changedAttributes);

        if (!$insert) {
            $this->updateItems();

            if (array_key_exists('start_datetime', $changedAttributes) || array_key_exists('end_datetime', $changedAttributes)) {
                self::notifyDateTimeChanged();
            }
        }

        $this->saveNewItems();

        return true;
    }


    // ###########  handle assigned users  ###########

    /**
     * Returns an ActiveQuery for all assigned task users of this task.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskAssigned()
    {
        $query = $this->hasMany(TaskAssigned::className(), ['task_id' => 'id']);
        return $query;
    }

    public function hasTaskAssigned()
    {
        return !empty($this->taskAssigned);
    }

    /**
     * Returns an ActiveQuery for all assigned user models of this task.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskAssignedUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->via('taskAssigned');
    }

    public function isTaskAssigned($user = null)
    {
        if (!$user && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        } else if (!$user) {
            return false;
        }

        $taskAssigned = array_filter($this->taskAssigned, function (TaskAssigned $p) use ($user) {
            return $p->user_id == $user->id;
        });

        return !empty($taskAssigned);
    }

    public function addTaskAssigned($user)
    {
        $user = (is_string($user)) ? User::findOne(['guid' => $user]) : $user;

        if (!$user) {
            return false;
        }

        if (!$this->isTaskAssigned($user)) {
            $taskAssigned = new TaskAssigned([
                'task_id' => $this->id,
                'user_id' => $user->id,
            ]);
            return $taskAssigned->save();
        }

        return false;
    }


    // ###########  handle responsible users  ###########

    /**
     * Returns an ActiveQuery for all responsible task users of this task.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskResponsible()
    {
        $query = $this->hasMany(TaskResponsible::className(), ['task_id' => 'id']);
        return $query;
    }

    public function hasTaskResponsible()
    {
        return !empty($this->taskResponsible);
    }

    /**
     * Returns an ActiveQuery for all responsible user models of this task.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskResponsibleUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->via('taskResponsible');
    }

    public function isTaskResponsible($user = null)
    {
        if (!$user && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        } else if (!$user) {
            return false;
        }

        $taskResponsible = array_filter($this->taskResponsible, function (TaskResponsible $p) use ($user) {
            return $p->user_id == $user->id;
        });

        return !empty($taskResponsible);
    }

    public function addTaskResponsible($user)
    {
        $user = (is_string($user)) ? User::findOne(['guid' => $user]) : $user;

        if (!$user) {
            return false;
        }

        if (!$this->isTaskResponsible($user)) {
            $taskResponsible = new TaskResponsible([
                'task_id' => $this->id,
                'user_id' => $user->id,
            ]);
            return $taskResponsible->save();
        }

        return false;
    }


    // ###########  handle reminder  ###########

    /**
     * Returns an ActiveQuery for all assigned task users of this task.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskReminder()
    {
        $query = $this->hasMany(TaskReminder::className(), ['task_id' => 'id']);
        return $query;
    }

    public function hasTaskReminder()
    {
        return !empty($this->taskReminder);
    }

    public function isTaskReminder($remind_mode)
    {
        if (!$remind_mode) {
            return false;
        }

        $taskReminder = $this->getTaskReminder()->where(['remind_mode' => $remind_mode])->one();

        return !empty($taskReminder);
    }

    public function addTaskReminder($remind_mode)
    {
        if (!$remind_mode) {
            return false;
        }

        if (!$this->isTaskReminder($remind_mode)) {
            $taskReminder = new TaskReminder([
                'task_id' => $this->id,
                'remind_mode' => $remind_mode,
            ]);
            return $taskReminder->save();
        }

        return false;
    }


    // ###########  handle task items  ###########

    /**
     * Returns an ActiveQuery for all task items of this task.
     *
     * @return ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(TaskItem::class, ['task_id' => 'id']);
    }

    public function hasItems()
    {
        // Todo check task_items and subtask-Items
        return !empty($this->items);
    }

    public function saveNewItems()
    {
        if ($this->newItems == null) {
            return;
        }

        foreach ($this->newItems as $itemText) {
            $this->addItem($itemText);
        }

        // Reset cached items
        unset($this->items);
    }

    public function addItem($itemText)
    {
        if (trim($itemText) === '') {
            return;
        }

        $item = new TaskItem();
        $item->task_id = $this->id;
        $item->title = $itemText;
        $item->save();
        return $item;
    }

    public function updateItems()
    {
        if (!isset($this->editItems))
            return;

        foreach ($this->items as $item) {
            if (!array_key_exists($item->id, $this->editItems)) {
                $item->delete();
            } else if ($item->title !== $this->editItems[$item->id]) {
                $item->title = $this->editItems[$item->id];
                $item->update();
            }
        }
    }

    /**
     * Sets the newItems array, which is used for creating and updating (afterSave)
     * the task, by saving all valid item title contained in the given array.
     * @param type $newItemArr
     */
    public function setNewItems($newItemArr)
    {
        $this->newItems = TaskItem::filterValidItems($newItemArr);
    }

    /**
     * Sets the editItems array, which is used for updating (afterSave)
     * the task. The given array has to contain task item ids as key and an title
     * as values.
     * @param type $editItemArr
     */
    public function setEditItems($editItemArr)
    {
        $this->editItems = TaskItem::filterValidItems($editItemArr);
    }

    /**
     * @param array $items
     * @throws \yii\db\Exception
     */
    public function confirm($items = array())
    {
        foreach ($items as $itemID) {
            $item = TaskItem::findOne(['id' => $itemID, 'task_id' => $this->id]);
            if ($item) {
                $item->completed = 1;
                $item->save();
            }
        }
    }

    /**
     * @throws \yii\db\Exception
     */
    public function completeItems()
    {
        Yii::$app->db->createCommand()
            ->update(
                TaskItem::tableName(),
                ['completed' => 1], //columns and values
                ['task_id' => $this->id] //condition, similar to where()
            )
            ->execute();
    }

    /**
     * Returns the total number of confirmed users got this message
     *
     * @return int
     */
    public function getConfirmedCount()
    {
        return $this->getItems()->where(['completed' => true])->count();
    }


    // ###########  handle status  ###########

    /**
     * @param $newStatus
     * @return bool
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function changeStatus($newStatus)
    {
        if (!in_array($newStatus, self::$statuses))
            return false;

        switch ($newStatus) {

            case Task::STATUS_PENDING:
                if (!(self::isCompleted()))
                    return false;
                self::notifyReset();
                break;
            case Task::STATUS_IN_PROGRESS:
                if (self::isPendingReview())
                    self::notifyRejectedReview();
                elseif (self::isPending())
                    self::notifyInProgress();
                else
                    return false;
                break;
            case Task::STATUS_PENDING_REVIEW:
                if (!$this->review || !(self::isInProgress()))
                    return false;
                self::notifyPendingReview();
                break;
            case Task::STATUS_COMPLETED:
                if (!(self::isInProgress() || self::isPendingReview()))
                    return false;
                if ($this->hasItems()) {
                    $this->completeItems();
                }
                self::notifyCompleted();
                break;
        }
        // Todo: example notification and activity
//            $activity = new \humhub\modules\task\activities\Finished();
//            $activity->source = $this;
//            $activity->originator = Yii::$app->user->getIdentity();
//            $activity->create();
//
//            if ($this->created_by != Yii::$app->user->id) {
//                $notification = new \humhub\modules\task\notifications\Finished();
//                $notification->source = $this;
//                $notification->originator = Yii::$app->user->getIdentity();
//                $notification->send($this->content->user);
//            }

        $this->updateAttributes(['status' => $newStatus]);

        return true;
    }

    /**
     * Returns an array of statusItems.
     * Primary used in TaskFilter
     *
     * @return array
     */
    public static function getStatusItems()
    {
        return [
            self::STATUS_PENDING => Yii::t('TaskModule.views_index_index', 'Pending'),
            self::STATUS_IN_PROGRESS => Yii::t('TaskModule.views_index_index', 'In Progress'),
            self::STATUS_PENDING_REVIEW => Yii::t('TaskModule.views_index_index', 'Pending Review'),
            self::STATUS_COMPLETED => Yii::t('TaskModule.views_index_index', 'Completed'),
            self::STATUS_ALL => Yii::t('TaskModule.views_index_index', 'All'),
        ];
    }

    public function isPending()
    {
        return ($this->status === self::STATUS_PENDING);
    }

    public function isInProgress()
    {
        return ($this->status === self::STATUS_IN_PROGRESS);
    }

    public function isPendingReview()
    {
        return ($this->status === self::STATUS_PENDING_REVIEW);
    }

    public function isCompleted()
    {
        return ($this->status === self::STATUS_COMPLETED);
    }

    /**
     * send link for change-status button
     * @return string $statusLink
     */
    public function getStatusLink()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                $statusLink = $this->content->container->createUrl('/task/index/status', ['id' => $this->id, 'status' => self::STATUS_IN_PROGRESS]);
                break;
            case self::STATUS_IN_PROGRESS:
                if ($this->review)
                    $statusLink = $this->content->container->createUrl('/task/index/status', ['id' => $this->id, 'status' => self::STATUS_PENDING_REVIEW]);
                else
                    $statusLink = $this->content->container->createUrl('/task/index/status', ['id' => $this->id, 'status' => self::STATUS_COMPLETED]);
                break;
            case self::STATUS_PENDING_REVIEW:
                $statusLink = $this->content->container->createUrl('/task/index/status', ['id' => $this->id, 'status' => self::STATUS_COMPLETED]);
                break;
            default :
                $statusLink = '';
        }

        return $statusLink;
    }

    /**
     * send label for change-status button
     * @return string $statusLabel
     */
    public function getStatusLabel()
    {
        switch ($this->status) {
            case Task::STATUS_PENDING:
                $statusLabel = Yii::t('TaskModule.views_index_index', 'Begin Task');
                break;
            case Task::STATUS_IN_PROGRESS:
                if ($this->review)
                    $statusLabel = Yii::t('TaskModule.views_index_index', 'Let Task Review');
                else
                    $statusLabel = Yii::t('TaskModule.views_index_index', 'Finish Task');
                break;
            case Task::STATUS_PENDING_REVIEW:
                $statusLabel = Yii::t('TaskModule.views_index_index', 'Finish Task');
                break;
            default :
                $statusLabel = '';
        }

        return $statusLabel;
    }

    /**
     * send link for change-status button
     * @return string $statusLink
     */
    public function getRejectReviewLink()
    {
        if (self::isPendingReview() && self::canReviewTask())
            return $this->content->container->createUrl('/task/index/reject-review', ['id' => $this->id]);
        return '';
    }

    /**
     * send label for change-status button
     * @return string $statusLabel
     */
    public function getRejectReviewLabel()
    {
        if (self::isPendingReview() && self::canReviewTask())
            return Yii::t('TaskModule.views_index_index', 'Reject review');
        return '';
    }


    // ###########  handle notifications  ###########

    /**
     * Filters responsible users from the list of assigned users
     *
     * @return array|User[]
     */
    private function filterResponsibleAssigned()
    {
        $responsible = $this->getTaskResponsibleUsers()->select(['id']);

        $filteredAssigned = $this->getTaskAssignedUsers()
            ->where(['not in', 'id', $responsible])
            ->all();
        return $filteredAssigned;
    }

    /**
     * Notify users about created task
     * @throws \yii\base\InvalidConfigException
     */
    public function notifyCreated()
    {
//        if (self::hasTaskAssigned())
        NotifyAssigned::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk(self::filterResponsibleAssigned());
//        if (self::hasTaskResponsible())
        NotifyResponsible::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk($this->taskResponsibleUsers);
    }

    /**
     * Remind users
     */
    public function remindUserOfStart()
    {
        if (self::hasTaskAssigned())
            RemindStart::instance()->from($this->content->user)->about($this)->sendBulk(self::filterResponsibleAssigned());
        if (self::hasTaskResponsible())
            RemindStart::instance()->from($this->content->user)->about($this)->sendBulk($this->taskResponsibleUsers);
    }

    /**
     * Remind users
     */
    public function remindUserOfEnd()
    {
        if (self::hasTaskAssigned())
            RemindEnd::instance()->from($this->content->user)->about($this)->sendBulk(self::filterResponsibleAssigned());
        if (self::hasTaskResponsible())
            RemindEnd::instance()->from($this->content->user)->about($this)->sendBulk($this->taskResponsibleUsers);
    }

    /**
     * Request deadline extension
     * @throws \yii\base\InvalidConfigException
     */
    public function sendExtensionRequest()
    {
        if ($this->hasTaskResponsible())
            ExtensionRequest::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk($this->taskResponsibleUsers);
    }

    /**
     * Notify users about status change
     * @throws \yii\base\InvalidConfigException
     */
    public function notifyDateTimeChanged()
    {
        if (!empty($this->taskAssignedUsers)) {
            NotifyChangedDateTime::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk(self::filterResponsibleAssigned());
        }

        if (!empty($this->taskResponsibleUsers)) {
            NotifyChangedDateTime::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk($this->taskResponsibleUsers);
        }
    }

    /**
     * Notify users about status change
     * @throws \yii\base\InvalidConfigException
     */
    public function notifyReset()
    {
        if ($this->hasTaskAssigned())
            NotifyStatusReset::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk(self::filterResponsibleAssigned());

        if ($this->hasTaskResponsible())
            NotifyStatusReset::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk($this->taskResponsibleUsers);
    }

    /**
     * Notify users about status change
     * @throws \yii\base\InvalidConfigException
     */
    public function notifyInProgress()
    {
        if ($this->hasTaskResponsible())
            NotifyStatusInProgress::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk($this->taskResponsibleUsers);
    }

    /**
     * Notify users about status change
     * @throws \yii\base\InvalidConfigException
     */
    public function notifyPendingReview()
    {
        if ($this->review && $this->hasTaskResponsible())
            NotifyStatusPendingReview::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk($this->taskResponsibleUsers);
    }

    /**
     * Notify users about status change
     */
    public function notifyCompleted()
    {
        if ($this->review && $this->hasTaskAssigned())
            NotifyStatusCompletedAfterReview::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk(self::filterResponsibleAssigned());
        elseif ($this->hasTaskResponsible())
            NotifyStatusCompleted::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk($this->taskResponsibleUsers);
    }

    /**
     * Notify users about status change
     * @param User $requestingUser
     * @throws \yii\base\InvalidConfigException
     */
    public function notifyRejectedReview()
    {
        if ($this->review && $this->hasTaskAssigned())
            NotifyStatusRejectedAfterReview::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk(self::filterResponsibleAssigned());
    }


    // ###########  handle calendar entries  ###########

    /**
     * Returns an array of calendarModes.
     *
     * @return array
     */
    public static function getCalModeItems()
    {
        return [
            self::CAL_MODE_NONE => Yii::t('TaskModule.models_task', 'Don\'t add to calendar'),
            self::CAL_MODE_SPACE => Yii::t('TaskModule.models_task', 'Add Deadline to space calendar'),
        ];
    }

    public function getCalMode()
    {
        switch ($this->cal_mode) {
            case (self::CAL_MODE_NONE):
                return Yii::t('TaskModule.models_task', 'Don\'t add to calendar');
                break;
            case (self::CAL_MODE_SPACE):
                return Yii::t('TaskModule.models_task', 'Add to space calendar');
                break;
            default:
                return;
        }
    }

    /**
     * @inheritdoc
     */
    public function getFullCalendarArray()
    {
        $end = $this->getEndDateTime();

        if(!Yii::$app->user->isGuest) {
            Yii::$app->formatter->timeZone = Yii::$app->user->getIdentity()->time_zone;
        }

        $title = Yii::t('TaskModule.models_task', 'Deadline: ') . Html::encode($this->title);

        return [
            'id' => $this->id,
            'title' => $title,
//            'editable' => ($this->content->canEdit() || self::isTaskResponsible()),
            'editable' => false,
//            'backgroundColor' => Html::encode($this->color),
            'allDay' => $this->all_day,
            'updateUrl' => $this->content->container->createUrl('/task/index/edit-ajax', ['id' => $this->id]),
            'viewUrl' => $this->content->container->createUrl('/task/index/modal', ['id' => $this->id, 'cal' => '1']),
//            'start' => Yii::$app->formatter->asDatetime($this->start_datetime, 'php:c'),
//            'start' => $this->getStartDateTime(),
            'start' => $end,
            'end' => $end,
        ];
    }


    /**
     * Access url of the source content or other view
     *
     * @return string the timezone this item was originally saved, note this is
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns a badge for the snippet
     *
     * @return string the timezone this item was originally saved, note this is
     */
    public function getBadge()
    {
        if (self::isTaskResponsible())
            return Label::info(Yii::t('TaskModule.widgets_views_myTasks', 'Responsible'))->right();
        elseif (self::isTaskAssigned())
            return Label::info(Yii::t('TaskModule.widgets_views_myTasks', 'Assigned'))->right();
        elseif (self::canAnyoneProcessTask())
            return Label::info(Yii::t('TaskModule.widgets_views_myTasks', 'For all'))->right();
        return null;
    }

    public function getOverdueBadge()
    {
        if (self::isOverdue())
            return Label::danger(Yii::t('TaskModule.views_index_index', 'Overdue'))->options(['style' => 'margin-right: 3px;'])->right();
        return null;
    }


    // ###########  handle datetime  ###########

    /**
     * @inheritdoc
     */
    public function getTimezone()
    {
        return $this->time_zone;
    }

    public function getStartDateTime()
    {
        return new DateTime($this->start_datetime, new DateTimeZone(Yii::$app->timeZone));
    }

    public function getEndDateTime()
    {
        return new DateTime($this->end_datetime, new DateTimeZone(Yii::$app->timeZone));
    }

    public function getFormattedEndDateTime($timeZone = null, $format = 'short')
    {
        if ($timeZone) {
            Yii::$app->formatter->timeZone = $timeZone;
        }

        if ($this->all_day) {
            $result = Yii::$app->formatter->asDate($this->getEndDateTime(), $format);
        } else {
            $result = Yii::$app->formatter->asDatetime($this->getEndDateTime(), $format);
        }
        if ($timeZone) {
            Yii::$app->i18n->autosetLocale();
        }

        return $result;
    }

    public function getFormattedStartDateTime($timeZone = null, $format = 'short')
    {
        if ($timeZone) {
            Yii::$app->formatter->timeZone = $timeZone;
        }

        if ($this->all_day) {
            $result = Yii::$app->formatter->asDate($this->getStartDateTime(), $format);
        } else {
            $result = Yii::$app->formatter->asDatetime($this->getStartDateTime(), $format);
        }

        if ($timeZone) {
            Yii::$app->i18n->autosetLocale();
        }

        return $result;
    }

    public function getFormattedDateTime($timeZone = null, $format = 'short')
    {
        if ($timeZone) {
            Yii::$app->formatter->timeZone = $timeZone;
        }

        if (!$this->scheduling)
            $result = Yii::t('TaskModule.views_index_index', 'No Scheduling set for this Task');
        else {
            $result = Yii::t('TaskModule.views_index_index', 'Deadline at');
            if ($this->all_day) {
                $result .= ' ' . Yii::$app->formatter->asDate($this->getEndDateTime(), $format);
            }
            else {
                $result .= ' ' . Yii::$app->formatter->asDatetime($this->getEndDateTime(), $format);
            }
        }

        if ($timeZone) {
            Yii::$app->i18n->autosetLocale();
        }

        return $result;
    }

    // TODO calc remaining days for notifications
    public function getRemainingDays()
    {
//        $datetime1 = new DateTime('2009-10-11');
//        $datetime2 = new DateTime('2009-10-13');
//        $interval = $datetime1->diff($datetime2);
//        echo $interval->format('%R%a days');
    }

    /**
     * @return boolean weather or not this item spans exactly over a whole day
     */
    public function isAllDay()
    {
        if ($this->all_day === null) {
            return true;
        }

        return (boolean)$this->all_day;
    }


    // ###########  handle extends  ###########

    /**
     * @inheritdoc
     */
    public function getSearchAttributes()
    {
        $itemTitles = "";
        $itemDescriptions = "";

        foreach ($this->items as $item) {
            $itemTitles .= $item->title;
            $itemDescriptions .= $item->description;
        }

        return [
            'title' => $this->title,
            'description' => $this->description,
            'itemTitles' => $itemTitles,
            'itemDescriptions' => $itemDescriptions
        ];
    }


    // ###########  handle deadline extension  ###########

    public function hasRequestedExtension()
    {
        return (boolean)($this->request_sent);
    }

    // ###########  handle reset  ###########

    /**
     * Handles task reset
     *
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function reset()
    {
        if (self::hasItems()) {
            self::resetItems();
        }
        self::changeStatus(self::STATUS_PENDING);
        self::resetDateTimes();
    }

    /**
     * Resets items
     *
     * @throws \yii\db\Exception
     */
    public function resetItems()
    {
        Yii::$app->db->createCommand()
            ->update(
                TaskItem::tableName(),
                ['completed' => 0], //columns and values
                ['task_id' => $this->id] //condition, similar to where()
            )
            ->execute();
    }

    /**
     * Resets start_datetime & end_datetime
     *
     * @throws \yii\base\InvalidConfigException
     */
    protected function resetDateTimes()
    {
        if (!$this->scheduling)
            return;

        // calculate duration between start and end
        $newStart = $this->getStartDateTime();
        $interval = $newStart->diff($this->getEndDateTime());

        $newStart->setDate(date("Y"), date("m"), date("d"));

        $temp = clone $newStart;
        $newEnd = $temp->add($interval);
        unset($temp);

        $dateFormat = 'php:Y-m-d H:i:s';

        self::updateAttributes([
            'start_datetime' => Yii::$app->formatter->asDateTime($newStart, $dateFormat),
            'end_datetime' => Yii::$app->formatter->asDateTime($newEnd, $dateFormat),
        ]);
    }


    // ###########  handle task-permissions  ###########

    /**
     * handle task specific permissions
     * @return bool
     */
    public function canAnyoneProcessTask()
    {
        return (!$this->hasTaskAssigned() && $this->content->getSpace()->isMember());
    }

    /**
     * handle task specific permissions
     * @return bool
     */
    public function canCheckItems()
    {
        return ((self::isTaskResponsible() || self::isTaskAssigned() || self::canAnyoneProcessTask()) && (!(self::isCompleted() || self::isPending())));
    }

    /**
     * handle task specific permissions
     * @return bool
     */
    public function canRequestExtension()
    {
        if (!$this->scheduling)
            return false;
        return ((!self::isTaskResponsible() && self::hasTaskResponsible() && (self::isTaskAssigned() || self::canAnyoneProcessTask()) && (!(self::isCompleted() || self::isPending())) && (!self::hasRequestedExtension())));
    }

    /**
     * handle task specific permissions
     * @return bool
     */
    public function canChangeStatus()
    {
        return ((self::isTaskResponsible() || self::isTaskAssigned() || self::canAnyoneProcessTask()) && !(self::isCompleted()));
    }

    /**
     * Only responsible users can review task
     * @return bool
     */
    public function canReviewTask()
    {
        return (self::isTaskResponsible());
    }

    /**
     * handle task specific permissions
     * @return bool
     */
    public function canSeeStatusButton()
    {
        if (self::isCompleted())
            return false;
        elseif ($this->review && self::isPendingReview())
            return self::canReviewTask();
        return self::canChangeStatus();
    }

    /**
     * handle task specific permissions
     * @return bool
     */
    public function canResetTask()
    {
        return (self::isTaskResponsible() && (self::isCompleted()));
    }




    // ###########  handle view-specific  ###########

    /**
     * Returns the percentage of task
     *
     * @return int
     */
    public function getPercent()
    {
//        $denominator = TaskItem::find()->where(['task_id' => $this->id])->count();
        $denominator = $this->getItems()->count();
        // add STATUS_IN_PROGRESS and STATUS_COMPLETED
        $denominator += 2;
        // handle special status STATUS_PENDING_REVIEW
        if ($this->review) {
            $denominator += 1;
        }
        if ($denominator == 0)
            return 0;


        $counter = $this->getConfirmedCount();
        if (self::isInProgress())
            $counter += 1;
        elseif (self::isCompleted() && !$this->review)
            $counter += 2;
        elseif (self::isPendingReview() && $this->review)
            $counter += 2;
        elseif (self::isCompleted() && $this->review)
            $counter += 3;

        return $counter / $denominator * 100;
    }

    /**
     * Returns additional labels
     *
     * @param array $labels
     * @param bool $includeContentName
     * @return Label[]|string[]
     */
    public function getLabels($labels = [], $includeContentName = true)
    {
        switch ($this->status) {
            case self::STATUS_PENDING :
                $labels[] = Label::defaultType(Yii::t('TaskModule.views_index_index', 'Pending'))->icon('fa fa-info-circle')->sortOrder(350);
                break;
            case self::STATUS_IN_PROGRESS :
                $labels[] = Label::info(Yii::t('TaskModule.views_index_index', 'In Progress'))->icon('fa fa-edit')->sortOrder(350);
                break;
            case self::STATUS_PENDING_REVIEW :
                $labels[] = Label::warning(Yii::t('TaskModule.views_index_index', 'Pending Review'))->icon('fa fa-exclamation-triangle')->sortOrder(350);
                break;
            case self::STATUS_COMPLETED :
                $labels[] = Label::success(Yii::t('TaskModule.views_index_index', 'Completed'))->icon('fa fa-check-square')->sortOrder(350);
                break;
            default:
                break;
        }

        if (self::isOverdue())
            $labels[] = Label::danger(Yii::t('TaskModule.views_index_index', 'Overdue'))->icon('fa fa-exclamation-triangle')->sortOrder(360);

        return parent::getLabels($labels, $includeContentName);
    }


    // ###########  Todo  ###########

    /**
     * Returns an ActiveQuery for all available sub-tasks.
     *
     * @return ActiveQuery
     */
    public function getSubTasks()
    {
        return $this->hasMany(self::class, ['id' => 'parent_task_id']);
    }

    public function hasSubTasks()
    {
        // Todo check task_items and subtask-Items
        return !empty($this->subTasks);
    }

    public function isOverdue()
    {
        if (!$this->scheduling) {
            return false;
        }

        return (strtotime($this->end_datetime) < time() && !$this->isCompleted());
    }
}
