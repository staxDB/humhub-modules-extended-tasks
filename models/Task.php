<?php

namespace humhub\modules\task\models;

use humhub\modules\task\notifications\RemindAssignedEnd;
use humhub\modules\task\notifications\RemindAssignedStart;
use humhub\modules\task\notifications\RemindResponsibleStart;
use humhub\modules\task\notifications\RemindResponsibleEnd;
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
use yii\data\Sort;
use yii\db\ActiveQuery;
use humhub\modules\task\CalendarUtils;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Url;

/**
 * This is the model class for table "task".
 *
 * The followings are the available columns in table 'task':
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property integer $review
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
class Task extends ContentActiveRecord implements Searchable
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
    const CAL_MODE_USERS = 1;
    const CAL_MODE_SPACE = 2;

    /**
     * @var array all given cal modes as array
     */
    public static $calModes = [
        self::CAL_MODE_NONE,
        self::CAL_MODE_USERS,
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
            [['start_datetime', 'end_datetime'], 'required', 'when' => function($model) {
                return $model->scheduling == 1;
            }, 'whenClient' => "function (attribute, value) {
                return $('#task-scheduling').val() == 1;
            }"],
            [['start_datetime'], DbDateValidator::className()],
            [['end_datetime'], DbDateValidator::className()],
            [['all_day', 'scheduling', 'review'], 'integer'],
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

        if(!empty($this->assignedUsers)) {
            foreach ($this->assignedUsers as $guid) {
                $this->addTaskAssigned($guid);
            }
        }

        TaskResponsible::deleteAll(['task_id' => $this->id]);

        if(!empty($this->responsibleUsers)) {
            foreach ($this->responsibleUsers as $guid) {
                $this->addTaskResponsible($guid);
            }
        }

        TaskReminder::deleteAll(['task_id' => $this->id]);

        if(!empty($this->selectedReminders)) {
            foreach ($this->selectedReminders as $remind_mode) {
                $this->addTaskReminder($remind_mode);
            }
        }

        parent::afterSave($insert, $changedAttributes);

        if (!$insert) {
            $this->updateItems();
        }

        $this->saveNewItems();

        return true;
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

    public function isTaskAssigned($user = null)
    {
        if(!$user && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        } else if(!$user) {
            return false;
        }

        $taskAssigned = array_filter($this->taskAssigned, function(TaskAssigned $p) use ($user) {
            return $p->user_id == $user->id;
        });

        return !empty($taskAssigned);
    }

    public function addTaskAssigned($user)
    {
        $user = (is_string($user)) ? User::findOne(['guid' => $user]) : $user ;

        if(!$user) {
            return false;
        }

        if(!$this->isTaskAssigned($user)) {
            $taskAssigned = new TaskAssigned([
                'task_id' => $this->id,
                'user_id' => $user->id,
            ]);
            return $taskAssigned->save();
        }

        return false;
    }

    public function isTaskResponsible($user = null)
    {
        if(!$user && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        } else if(!$user) {
            return false;
        }

        $taskResponsible = array_filter($this->taskResponsible, function(TaskResponsible $p) use ($user) {
            return $p->user_id == $user->id;
        });

        return !empty($taskResponsible);
    }

    public function addTaskResponsible($user)
    {
        $user = (is_string($user)) ? User::findOne(['guid' => $user]) : $user ;

        if(!$user) {
            return false;
        }

        if(!$this->isTaskResponsible($user)) {
            $taskResponsible = new TaskResponsible([
                'task_id' => $this->id,
                'user_id' => $user->id,
            ]);
            return $taskResponsible->save();
        }

        return false;
    }

    public function isTaskReminder($remind_mode)
    {
        if(!$remind_mode) {
            return false;
        }

        $taskReminder = $this->getTaskReminder()->where(['remind_mode' => $remind_mode])->one();

        return !empty($taskReminder);
    }

    public function addTaskReminder($remind_mode)
    {
        if(!$remind_mode) {
            return false;
        }

        if(!$this->isTaskReminder($remind_mode)) {
            $taskReminder = new TaskReminder([
                'task_id' => $this->id,
                'remind_mode' => $remind_mode,
            ]);
            return $taskReminder->save();
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->content->container->createUrl('/task/index/view', ['id' => $this->id]);
    }

    public static function findPendingTasks(ContentContainerActiveRecord $container)
    {
        return self::find()
            ->contentContainer($container)
            ->orderBy([new Expression('-task.end_datetime DESC')])
            ->readable()
            ->andWhere(['!=', 'task.status', Task::STATUS_COMPLETED]);
    }

    public static function GetUsersOpenTasks()
    {
        $query = self::find();
        $query->leftJoin('task_assigned', 'task.id=task_assigned.task_id');
        $query->where(['task_assigned.user_id' => Yii::$app->user->id, 'task.status' => self::STATUS_PENDING]);

        return $query->all();
    }

    public function isOverdue()
    {
        if (!$this->hasDeadline()) {
            return false;
        }

        return (strtotime($this->end_datetime) < time());
    }

    /**
     * @param ContentContainerActiveRecord $container
     * @return ActiveQuery
     * @throws \yii\base\Exception
     */
    public static function findPastTasks(ContentContainerActiveRecord $container)
    {
        return self::find()
            ->contentContainer($container)
            ->orderBy(['task.end_datetime' => SORT_DESC])
            ->readable()
            ->andWhere(['<', 'task.end_datetime', date('Y-m-d')]);
    }

    public static function findReadable(ContentContainerActiveRecord $container)
    {
        return self::find()
            ->contentContainer($container)
            ->orderBy(['task.end_datetime' => SORT_DESC])
            ->readable();
    }

    public function changeStatus($newStatus)
    {
        if (!in_array($newStatus, self::$statuses))
            return false;

        switch ($newStatus) {
            case Task::STATUS_IN_PROGRESS:
                // Todo: Notify responsible Person, e.g. creator
                break;

            case Task::STATUS_PENDING_REVIEW:
                if (!$this->review)
                    return false;
                // Todo: Notify responsible Person, e.g. creator
                break;

            case Task::STATUS_COMPLETED:
                if ($this->hasItems()) {
                    $this->completeItems();
                }
                // Todo: Notify responsible Person, e.g. assigned persons or creator (if finisher is not creator)
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

            // Try to delete TaskFinishedNotification if exists
//            $notification = new \humhub\modules\task\notifications\Finished();
//            $notification->source = $this;
//            $notification->delete($this->content->user);
//        }

        $this->updateAttributes(['status' => $newStatus]);

        return true;
    }

    public function hasDeadline()
    {
        if ($this->end_datetime != '0000-00-00 00:00:00' && $this->end_datetime != '' && $this->end_datetime != 'NULL') {
            return true;
        }
        return false;
    }

    public function hasSubTasks()
    {
        // Todo check task_items and subtask-Items
        return !empty($this->subTasks);
    }

    /**
     * Invite user to this task
     */
    public function inviteUser()
    {
        // Todo
//        Invite::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk($this->assignedUsers);
    }

    /**
     * Remind users
     */
    public function remindAssignedUserOfStart()
    {
        RemindAssignedStart::instance()
            ->from($this->content->user)
            ->about($this)
            ->sendBulk($this->taskAssignedUsers);
    }

    /**
     * Remind users
     */
    public function remindAssignedUserOfEnd()
    {
        RemindAssignedEnd::instance()
            ->from($this->content->user)
            ->about($this)
            ->sendBulk($this->taskAssignedUsers);
    }

    /**
     * Remind users
     */
    public function remindResponsibleUserOfStart()
    {
        RemindResponsibleStart::instance()
            ->from($this->content->user)
            ->about($this)
            ->sendBulk($this->taskAssignedUsers);
    }

    /**
     * Remind users
     */
    public function remindResponsibleUserOfEnd()
    {
        RemindResponsibleEnd::instance()
            ->from($this->content->user)
            ->about($this)
            ->sendBulk($this->taskAssignedUsers);
    }

//
//    public function newItem($title = null)
//    {
//        return new TaskItem($this->content->container, $this->content->visibility, [
//            'task_id' => $this->id,
//            'title' => $title,
//        ]);
//    }

    /** TODO
     * Returns an ActiveQuery for all available sub-tasks.
     *
     * @return ActiveQuery
     */
    public function getSubTasks()
    {
        return $this->hasMany(self::class, ['id' => 'parent_task_id']);
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

    /**
     * Returns an array of calendarModes.
     *
     * @return array
     */
    public static function getCalModeItems()
    {
        return [
            self::CAL_MODE_NONE => Yii::t('TaskModule.models_task', 'Don\'t add to calendar'),
            self::CAL_MODE_USERS => Yii::t('TaskModule.models_task', 'Add in users calendar'),
            self::CAL_MODE_SPACE => Yii::t('TaskModule.models_task', 'Add to space calendar'),
        ];
    }

    public function getCalMode()
    {
        switch ($this->cal_mode){
            case (self::CAL_MODE_NONE):
                return Yii::t('TaskModule.models_task', 'Don\'t add to calendar');
                break;
            case (self::CAL_MODE_USERS):
                return Yii::t('TaskModule.models_task', 'Add in users calendar');
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
        if($timeZone) {
            Yii::$app->formatter->timeZone = $timeZone;
        }

        if ($this->all_day) {
            $result = Yii::$app->formatter->asDate($this->getEndDateTime(), $format);
        } else {
            $result = Yii::$app->formatter->asDatetime($this->getEndDateTime(), $format);
        }
        if($timeZone) {
            Yii::$app->i18n->autosetLocale();
        }

        return $result;
    }

    public function getFormattedStartDateTime($timeZone = null, $format = 'short')
    {
        if($timeZone) {
            Yii::$app->formatter->timeZone = $timeZone;
        }

        if ($this->all_day) {
            $result = Yii::$app->formatter->asDate($this->getStartDateTime(), $format);
        } else {
            $result = Yii::$app->formatter->asDatetime($this->getStartDateTime(), $format);
        }

        if($timeZone) {
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
        if($this->all_day === null) {
            return true;
        }

        return (boolean) $this->all_day;
    }

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


    /**
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
     * checks if a user is assigned to task
     * @param User|null $user
     * @return bool
     */
    public function isUserAssigned(User $user = null)
    {
        if ($user === null) {
            $user = Yii::$app->user->getIdentity();
        }

        if (!$this->hasTaskAssigned()) {
            return false;
        }

        return !empty($this->getTaskAssignedUsers()->where(['id' => $user->id])->one());

    }

    /**
     * checks if a user is responsible for task
     * @param User|null $user
     * @return bool
     */
    public function isUserResponsible(User $user = null)
    {
        if ($user === null) {
            $user = Yii::$app->user->getIdentity();
        }

        if (!$this->hasTaskResponsible()) {
            return false;
        }

        return !empty($this->getTaskResponsibleUsers()->where(['id' => $user->id])->one());

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



    public function isCompleted()
    {
        return ($this->status === self::STATUS_COMPLETED);
    }

    public function isPending()
    {
        return ($this->status === self::STATUS_PENDING);
    }

    public function canAnyoneProcessTask()
    {
        return (!$this->hasTaskAssigned());
    }

    /**
     * handle task specific permissions
     * @return bool
     */
    public function canCheckItems()
    {
        return ( (self::isTaskResponsible() || self::isTaskAssigned() || self::canAnyoneProcessTask()) && ( !(self::isCompleted() || self::isPending()) ) );
    }

    /**
     * handle task specific permissions
     * @return bool
     */
    public function canChangeStatus()
    {
        return ( (self::isTaskResponsible() || self::isTaskAssigned() || self::canAnyoneProcessTask()) && !(self::isCompleted()) );
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
        if ($this->isCompleted())
            return false;
        elseif ($this->review)
            return $this->canReviewTask();
        return $this->canChangeStatus();
    }

    /**
     * Todo
     * handle task specific permissions
     * @return bool
     */
//    public function canResetTask()
//    {
//        return (self::isTaskResponsible());
//    }

    /**
     * send link for change-status button
     * @return string $statusLink
     */
    public function getStatusLink()
    {
        switch ($this->status) {
            case Task::STATUS_PENDING:
                $statusLink = $this->content->container->createUrl('/task/index/status', ['id' => $this->id, 'status' => self::STATUS_IN_PROGRESS]);
                break;
            case Task::STATUS_IN_PROGRESS:
                if ($this->review)
                    $statusLink = $this->content->container->createUrl('/task/index/status', ['id' => $this->id, 'status' => self::STATUS_PENDING_REVIEW]);
                else
                    $statusLink = $this->content->container->createUrl('/task/index/status', ['id' => $this->id, 'status' => self::STATUS_COMPLETED]);
                break;
            case Task::STATUS_PENDING_REVIEW:
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
     * Returns the percentage of tasj confirmed this message
     *
     * @return int
     */
    public function getPercent()
    {
//        $total = TaskItem::find()->where(['task_id' => $this->id])->count();
        $total = $this->getItems()->count();
        if ($total == 0)
            return 0;

        return $this->getConfirmedCount() / $total * 100;
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
}
