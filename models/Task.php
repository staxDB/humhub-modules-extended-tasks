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

/**
 * This is the model class for table "task".
 *
 * The followings are the available columns in table 'task':
 * @property integer $id
 * @property string $title
 * @property string $deadline
 * @property integer $percent
 * @property integer $status
 */
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

    public $assignedUsers;


    // Status
    const STATUS_OPEN = 1;
    const STATUS_FINISHED = 5;

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
            [['title'], 'required'],
            [['deadline'], 'datetime', 'format' => $this->getDbDateFormat()],
            [['percent', 'status'], 'integer'],
            [['assignedUsers'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function getDbDateFormat() {
        return 'php:'.Yii::createObject(DbDateValidator::class)->convertToFormat;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => Yii::t('TaskModule.task', 'Title'),
            'deadline' => Yii::t('TaskModule.task', 'Deadline'),
            'percent' => Yii::t('TaskModule.task', 'Percent'),
            'status' => Yii::t('TaskModule.task', 'Status'),
            'assignedUsers' => Yii::t('TaskModule.task', 'Assigned user(s)'),
        ];
    }

    /**
     * Returns an ActiveQuery for all taskUsers of this task.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskUsers()
    {
        $query = $this->hasMany(TaskUser::className(), ['task_id' => 'id']);
        return $query;
    }

    public function hasTaskUsers()
    {
        return !empty($this->taskUsers);
    }

    /**
     * Returns an ActiveQuery for all participant user models of this meeting.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskUserUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->via('taskUsers');
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        foreach (TaskItem::findAll(['task_id' => $this->id]) as $item) {
            $item->delete();
        }

        foreach (TaskUser::findAll(['task_id' => $this->id]) as $taskUser) {
            $taskUser->delete();
        }

        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {

        TaskUser::deleteAll(['task_id' => $this->id]);

        if(!empty($this->assignedUsers)) {
            foreach ($this->assignedUsers as $guid) {
                $this->addTaskUser($guid);
            }
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {

//        foreach ($this->assignedUsers as $user) {
//            $this->assignedUsers .= $user->guid . ",";
//        }

        return parent::afterFind();
    }

    public function isTaskUser($user = null)
    {
        if(!$user && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        } else if(!$user) {
            return false;
        }

        $taskUser = array_filter($this->taskUsers, function(TaskUser $p) use ($user) {
            return $p->user_id == $user->id;
        });

        return !empty($taskUser);
    }

    public function addTaskUser($user)
    {
        $user = (is_string($user)) ? User::findOne(['guid' => $user]) : $user ;

        if(!$user) {
            return false;
        }

        if(!$this->isTaskUser($user)) {
            $participant = new TaskUser([
                'task_id' => $this->id,
                'user_id' => $user->id,
            ]);
            return $participant->save();
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
            ->orderBy(['task.deadline' => SORT_ASC])
            ->readable()
            ->andWhere(['>=', 'task.deadline', date('Y-m-d')]);
    }

    public static function GetUsersOpenTasks()
    {
        $query = self::find();
        $query->leftJoin('task_user', 'task.id=task_user.task_id');
        $query->where(['task_user.user_id' => Yii::$app->user->id, 'task.status' => self::STATUS_OPEN]);

        return $query->all();
    }

    public function isOverdue()
    {
        if (!$this->hasDeadline()) {
            return false;
        }

        return (strtotime($this->deadline) < time());
    }

//    public function isPast()
//    {
//        $date = new DateTime($this->date);
//        $now = new DateTime();
//        return $date < $now;
//    }

//    public function isToday()
//    {
//        $today = new DateTime("now", new DateTimeZone(Yii::$app->formatter->timeZone));
//        return Yii::$app->formatter->asDate($this->deadline, "ddMMyyyy") == $today->format('dmY');
//    }
//
//    public function isTomorrow()
//    {
//        $today = new DateTime("now", new DateTimeZone(Yii::$app->formatter->timeZone));
//        $today->add(new DateInterval('P1D'));
//        return Yii::$app->formatter->asDate($this->date, "ddMMyyyy") == $today->format('dmY');
//    }

    /**
     * @param ContentContainerActiveRecord $container
     * @return ActiveQuery
     * @throws \yii\base\Exception
     */
    public static function findPastTasks(ContentContainerActiveRecord $container)
    {
        return self::find()
            ->contentContainer($container)
            ->orderBy(['task.deadline' => SORT_DESC])
            ->readable()
            ->andWhere(['<', 'task.deadline', date('Y-m-d')]);
    }

    public static function findReadable(ContentContainerActiveRecord $container)
    {
        return self::find()
            ->contentContainer($container)
            ->orderBy(['task.deadline' => SORT_DESC])
            ->readable();
    }

    public function changePercent($newPercent)
    {
        if ($this->percent != $newPercent) {
            $this->percent = $newPercent;
            $this->save();
        }

        if ($newPercent == 100) {
            $this->changeStatus(Task::STATUS_FINISHED);
        }

        if ($this->percent != 100 && $this->status == Task::STATUS_FINISHED) {
            $this->changeStatus(Task::STATUS_OPEN);
        }

        return true;
    }

    public function changeStatus($newStatus)
    {
        $this->status = $newStatus;

        if ($newStatus == Task::STATUS_FINISHED) {

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
        if ($this->deadline != '0000-00-00 00:00:00' && $this->deadline != '' && $this->deadline != 'NULL') {
            return true;
        }
        return false;
    }

    /**
     * Invite user to this task
     */
    public function inviteUser()
    {
//        Invite::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk($this->assignedUsers);
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
    public function getItems()
    {
        return $this->hasMany(TaskItem::class, ['task_id' => 'id']);
    }

    public function getFormattedDeadline($timeZone = null, $format = 'short')
    {
        if($timeZone) {
            Yii::$app->formatter->timeZone = $timeZone;
        }

        $result = Yii::$app->formatter->asDatetime($this->getDeadlineDateTime(), $format);

        if($timeZone) {
            Yii::$app->i18n->autosetLocale();
        }

        return $result;
    }

    public function getDeadlineDateTime()
    {
        return new DateTime($this->deadline, new DateTimeZone(Yii::$app->timeZone));
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
            $itemNotes .= $item->notes;
        }

        return [
            'title' => $this->title,
            'itemTitles' => $itemTitles,
            'itemNotes' => $itemNotes
        ];
    }
}
