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
 * @property string $date
 * @property string $begin
 * @property string $end
 * @property string $location
 * @property string $room
 * @property string $external_participants
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

    public $inputParticipants;

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
            [['title', 'date', 'begin', 'end'], 'required'],
            [['begin', 'end'], 'match', 'pattern' => '/[0-9]:[0-9]/u', 'message' => Yii::t('TaskModule.base', 'Format has to be HOUR : MINUTE')],
            [['begin', 'end'], 'validateTime'],
            [['date'], 'date', 'format' => $this->getDbDateFormat()],
            [['inputParticipants', 'external_participants'], 'safe'],
            [['title', 'location', 'room'], 'string', 'max' => 255],
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
            'date' => Yii::t('TaskModule.task', 'Date'),
            'begin' => Yii::t('TaskModule.task', 'Begin'),
            'end' => Yii::t('TaskModule.task', 'End'),
            'location' => Yii::t('TaskModule.task', 'Location'),
            'room' => Yii::t('TaskModule.task', 'Room'),
            'inputParticipants' => Yii::t('TaskModule.task', 'Participants'),
            'external_participants' => Yii::t('TaskModule.task', 'Participants (External)'),
        ];
    }

    public static function findPendingTasks(ContentContainerActiveRecord $container)
    {
        return self::find()
            ->contentContainer($container)
            ->orderBy(['task.date' => SORT_ASC, 'task.begin' => SORT_ASC])
            ->readable()
            ->andWhere(['>=', 'task.date', date('Y-m-d')]);
    }

    /**
     * @param ContentContainerActiveRecord $container
     * @return ActiveQuery
     */
    public static function findPastTasks(ContentContainerActiveRecord $container)
    {
        return self::find()
            ->contentContainer($container)
            ->orderBy(['task.date' => SORT_DESC, 'task.begin' => SORT_DESC])
            ->readable()
            ->andWhere(['<', 'task.date', date('Y-m-d')]);
    }

    public static function findReadable(ContentContainerActiveRecord $container)
    {
        return self::find()
            ->contentContainer($container)
            ->orderBy(['task.date' => SORT_DESC, 'task.begin' => SORT_DESC])
            ->readable();
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->content->container->createUrl('/task/index/view', ['id' => $this->id]);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->begin = substr($this->begin, 0, 5);
        $this->end = substr($this->end, 0, 5);

        return parent::afterFind();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        TaskParticipant::deleteAll(['task_id' => $this->id]);

        if(!empty($this->inputParticipants)) {
            foreach ($this->inputParticipants as $guid) {
                $this->addParticipant($guid);
            }
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    public function isParticipant($user = null)
    {
        if(!$user && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        } else if(!$user) {
            return false;
        }

        $participant = array_filter($this->participants, function(TaskParticipant $p) use ($user) {
            return $p->user_id == $user->id;
        });

        return !empty($participant);
    }

    public function addParticipant($user)
    {
        $user = (is_string($user)) ? User::findOne(['guid' => $user]) : $user ;
        
        if(!$user) {
            return false;
        }

        if(!$this->isParticipant($user)) {
            $participant = new TaskParticipant([
                'task_id' => $this->id,
                'user_id' => $user->id,
            ]);
            return $participant->save();
        }

        return false;
    }

    /**
     * Creates a duplicated model by removing id, and date and setting isNewRecord to true.
     * Note this method is only intended to render a TaskForm and not for saving the actual duplicate.
     */
    public function duplicated()
    {
        // Fetch participant users relation before resetting id!
        $this->participantUsers;
        $this->id = null;
        $this->isNewRecord = true;
        $this->date = null;
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        foreach (TaskItem::findAll(['task_id' => $this->id]) as $item) {
            $item->delete();
        }

        foreach (TaskParticipant::findAll(['task_id' => $this->id]) as $participant) {
            $participant->delete();
        }

        return parent::beforeDelete();
    }

    /**
     * Validate time interval.
     * @param $attribute
     * @param $params
     */
    public function validateTime($attribute, $params)
    {
        if ($this->end != 0) {
            if ($this->end < $this->begin) {
                $this->addError('end', Yii::t('TaskModule.models_Task', 'End must be after begin'));
            }
        }
    }

    /**
     * Invite user to this task
     */
    public function inviteUser()
    {
        Invite::instance()->from(Yii::$app->user->getIdentity())->about($this)->sendBulk($this->participantUsers);
    }

    public function newItem($title = null, $duration = 0)
    {
        return new TaskItem($this->content->container, $this->content->visibility, [
            'task_id' => $this->id,
            'sort_order' => $this->getNextIndex(),
            'title' => $title,
            'duration' => $duration
        ]);
    }

    /**
     * Returns an ActiveQuery for all task items of this task.
     *
     * @return ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(TaskItem::class, ['task_id' => 'id'])->orderBy(['begin' => SORT_ASC, 'sort_order' => SORT_ASC]);
    }

    /**
     * Returns all related task items with populated begin/end dateTime instances.
     *
     * @return \humhub\modules\content\models\Content|mixed
     */
    public function getItemsPopulated()
    {
        $start = new DateTime($this->begin);
        $items = $this->items;

        foreach ($items as $item) {
            if($item->begin) {break;} // legacy task
            $start = $this->populateTime($item, $start);
        }
        return $items;
    }

    public function isPast()
    {
        $date = new DateTime($this->date);
        $now = new DateTime();
        return $date < $now;
    }

    public function isToday()
    {
        $today = new DateTime("now", new DateTimeZone(Yii::$app->formatter->timeZone));
        return Yii::$app->formatter->asDate($this->date, "ddMMyyyy") == $today->format('dmY');
    }

    public function isTomorrow()
    {
        $today = new DateTime("now", new DateTimeZone(Yii::$app->formatter->timeZone));
        $today->add(new DateInterval('P1D'));
        return Yii::$app->formatter->asDate($this->date, "ddMMyyyy") == $today->format('dmY');
    }

    /**
     * Populates the begin and end time by means of the item duration and the given start time and returns
     * the cloned calculated end time of the item.
     *
     * @param TaskItem $item
     * @param DateTime $start
     * @return DateTime|string a clone of the calculated $item->end
     */
    private function populateTime(TaskItem $item, DateTime $start)
    {
        // Filter out legacy items
        if(!empty($item->begin)) {
            return;
        }

        if($item->duration === null) {
            $item->duration = 0;
        }

        $item->begin = clone $start;
        $item->end = clone $start;
        $item->end->add(new DateInterval('PT' . $item->duration . 'M'));
        return clone $item->end;
    }

    public function getNextIndex()
    {
        return $this->getItems()->count();
    }

    /**
     * Returns an ActiveQuery for all participants of this task.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParticipants()
    {
        return $this->hasMany(TaskParticipant::className(), ['task_id' => 'id']);
    }

    public function hasParticipants()
    {
        return !empty($this->participants) || !empty($this->external_participants);
    }

    /**
     * Returns an ActiveQuery for all participant user models of this task.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParticipantUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->via('participants');
    }

    public function getFormattedStartDate($timeZone = null, $format = 'short')
    {
        if($timeZone) {
            Yii::$app->formatter->timeZone = $timeZone;
        }

        $result = Yii::$app->formatter->asDate($this->getBeginDateTime(), $format);

        if($timeZone) {
            Yii::$app->i18n->autosetLocale();
        }

        return $result;
    }

    public function getBeginDateTime()
    {
        return new DateTime($this->date.' '.$this->begin, new DateTimeZone(Yii::$app->timeZone));
    }

    public function getEndDateTime()
    {
        $result = new DateTime($this->date.' '.$this->end, new DateTimeZone(Yii::$app->timeZone));
        if($result <= $this->getBeginDateTime()) {
            $result->add(new DateInterval('P1D'));
        }
        return $result;
    }

    public function getFormattedBeginTime($timeZone = null)
    {
        if($timeZone) {
            Yii::$app->formatter->timeZone = $timeZone;
        }

        $result = Yii::$app->formatter->asTime(new DateTime($this->begin, new DateTimeZone(Yii::$app->timeZone)), 'short');

        if($timeZone) {
            Yii::$app->i18n->autosetLocale();
        }

        return $result;
    }

    public function getFormattedEndTime($timeZone = null)
    {
        if($timeZone) {
            Yii::$app->formatter->timeZone = $timeZone;
        }

        $result = Yii::$app->formatter->asTime(new DateTime($this->end, new DateTimeZone(Yii::$app->timeZone)), 'short');

        if($timeZone) {
            Yii::$app->i18n->autosetLocale();
        }

        return $result;
    }

    public function getFormattedDateTime($timeZone = null)
    {
        return $this->getFormattedStartDate($timeZone, 'medium').' at '.$this->getFormattedBeginTime($timeZone).' - '.$this->getFormattedEndTime($timeZone);
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
            'location' => $this->location,
            'itemTitles' => $itemTitles,
            'itemNotes' => $itemNotes
        ];
    }

    public function shiftItem($itemId)
    {
        $taskItem = TaskItem::find()->contentContainer($this->content->container)->where(['task_item.id' => $itemId])->one();

        if(!$taskItem) {
            return false;
        }

        if($taskItem->content->contentcontainer_id != $this->content->contentcontainer_id) {
            throw new \InvalidArgumentException('Tried to shift item from another space!');
        }

        $taskItem->task_id = $this->id;
        $taskItem->sort_order = $this->getNextIndex();
        return $taskItem->save() && $this->refresh();
    }

    public function moveItemIndex($itemId, $newIndex)
    {
        $moveItem = TaskItem::findOne(['id' => $itemId]);
        $items = $this->items;

        // make sure no invalid index is given
        if($moveItem->sort_order === $newIndex) {
            return;
        } else if($newIndex < 0) {
            $newIndex = 0;
        } else if($newIndex >= count($items)) {
            $newIndex = count($items) -1;
        }

        array_splice($items, $moveItem->sort_order, 1);
        array_splice($items, $newIndex, 0, [$moveItem]);

        foreach ($items as $index => $item) {
            $item->sort_order = $index;
            $item->save();
        }

        $this->refresh();
    }
}
