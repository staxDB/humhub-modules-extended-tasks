<?php

namespace humhub\modules\task\models;

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
use yii\db\ActiveQuery;
use humhub\modules\task\CalendarUtils;

/**
 * This is the model class for table "task".
 *
 * The followings are the available columns in table 'task':
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property string $start_datetime
 * @property string $end_datetime
 * @property integer $all_day
 * @property integer $percent
 * @property integer $status
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
//    public $autoAddToWall = true;

    public $assignedUsers;


    /**
     * Status
     */
    const STATUS_OPEN = 1;
    const STATUS_PENDING = 2;
    const STATUS_IN_PROGRESS = 3;
    const STATUS_PENDING_REVIEW = 4;
    const STATUS_COMPLETED = 5;

    /**
     * @var array all given statuses as array
     */
    public static $statuses = [
        self::STATUS_OPEN,
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_PENDING_REVIEW,
        self::STATUS_COMPLETED
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
        return Yii::t('TaskModule.base', "Task");
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
        return 'fa-calendar-o';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'start_datetime'], 'required'],
//            [['start', 'end'], 'datetime', 'format' => $this->getDbDateFormat()],
            [['start_datetime'], DbDateValidator::className()],
            [['end_datetime'], DbDateValidator::className()],
            [['all_day', 'percent'], 'integer'],
            [['status'], 'in', 'range' => self::$statuses],
            [['assignedUsers', 'description'], 'safe'],
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
            'title' => Yii::t('TaskModule.task', 'Title'),
            'description' => Yii::t('TaskModule.task', 'Description'),
            'start_datetime' => Yii::t('TaskModule.task', 'Start'),
            'end_datetime' => Yii::t('TaskModule.task', 'End'),
            'all_day' => Yii::t('TaskModule.task', 'All Day'),
            'status' => Yii::t('TaskModule.task', 'Status'),
            'percent' => Yii::t('TaskModule.task', 'Percent'),
            'parent_task_id' => Yii::t('TaskModule.task', 'Parent Task'),
            'assignedUsers' => Yii::t('TaskModule.task', 'Assigned user(s)'),
        ];
    }

    /**
     * Returns an ActiveQuery for all taskUsers of this task.
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
     * Returns an ActiveQuery for all participant user models of this meeting.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskAssignedUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->via('taskAssigned');
    }

    public function beforeSave($insert)
    {

        // Check is a full day span
        if ($this->all_day == 0 && CalendarUtils::isFullDaySpan(new DateTime($this->start_datetime), new DateTime($this->end_datetime))) {
            $this->all_day = 1;
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

        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {

        TaskAssigned::deleteAll(['task_id' => $this->id]);

        if(!empty($this->assignedUsers)) {
            foreach ($this->assignedUsers as $guid) {
                $this->addTaskAssigned($guid);
            }
        }

        return parent::afterSave($insert, $changedAttributes);
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
            ->orderBy(['task.end_datetime' => SORT_ASC])
            ->readable()
            ->andWhere(['>=', 'task.end_datetime', date('Y-m-d')]);
    }

    public static function GetUsersOpenTasks()
    {
        $query = self::find();
        $query->leftJoin('task_assigned', 'task.id=task_assigned.task_id');
        $query->where(['task_assigned.user_id' => Yii::$app->user->id, 'task.status' => self::STATUS_OPEN]);

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

    public function changePercent($newPercent)
    {
        if ($this->percent != $newPercent) {
            $this->percent = $newPercent;
            $this->save();
        }

        if ($newPercent == 100) {
            $this->changeStatus(Task::STATUS_COMPLETED);
        }

        if ($this->percent != 100 && $this->status == self::STATUS_COMPLETED) {
            $this->changeStatus(self::STATUS_OPEN);
        }

        return true;
    }

    public function changeStatus($newStatus)
    {
        $this->status = $newStatus;

        if ($newStatus == Task::STATUS_COMPLETED) {

            // Todo: add notification and activity
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

            $this->percent = 100;
        } else {
            // Try to delete TaskFinishedNotification if exists
//            $notification = new \humhub\modules\task\notifications\Finished();
//            $notification->source = $this;
//            $notification->delete($this->content->user);
        }

        $this->save();

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
     * Returns an ActiveQuery for all task items of this task.
     *
     * @return ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(TaskItem::class, ['task_id' => 'id']);
    }

    public function newItem($title = null)
    {
        return new TaskItem($this->content->container, $this->content->visibility, [
            'task_id' => $this->id,
            'title' => $title,
        ]);
    }

    /**
     * Returns an ActiveQuery for all task items of this task.
     *
     * @return ActiveQuery
     */
    public function getSubTasks()
    {
        return $this->hasMany(self::class, ['id' => 'parent_task_id']);
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
        $itemNotes = "";

        foreach ($this->items as $item) {
            $itemTitles .= $item->title;
            $itemNotes .= $item->description;
        }

        return [
            'title' => $this->title,
            'itemTitles' => $itemTitles,
            'itemNotes' => $itemNotes
        ];
    }


    public static function getStatusItems()
    {
        return [
            self::STATUS_OPEN => Yii::t('TaskModule.task', 'Open'),
            self::STATUS_PENDING => Yii::t('TaskModule.task', 'Pending'),
            self::STATUS_IN_PROGRESS => Yii::t('TaskModule.task', 'In Progress'),
            self::STATUS_PENDING_REVIEW => Yii::t('TaskModule.task', 'Pending Review'),
            self::STATUS_COMPLETED => Yii::t('TaskModule.task', 'Completed'),
        ];
    }

    public function getStatus()
    {
        switch ($this->status){
            case (self::STATUS_OPEN):
                return Yii::t('TaskModule.task', 'Open');
                break;
            case (self::STATUS_PENDING):
                return Yii::t('TaskModule.task', 'Pending');
                break;
            case (self::STATUS_IN_PROGRESS):
                return Yii::t('TaskModule.task', 'In Progress');
                break;
            case (self::STATUS_PENDING_REVIEW):
                return Yii::t('TaskModule.task', 'Pending Review');
                break;
            case (self::STATUS_COMPLETED):
                return Yii::t('TaskModule.task', 'Completed');
                break;
            default:
                return;
        }
    }
}
