<?php

namespace humhub\modules\task\models;

use DateTime;
use Yii;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\task\permissions\ManageTasks;
use humhub\modules\user\models\User;

/**
 * This is the model class for table "task_item".
 *
 * The followings are the available columns in table 'task_item':
 * @property integer $id
 * @property integer $task_id
 * @property string $begin
 * @property string $end
 * @property string $title
 * @property string $description
 * @property string $notes
 * @property string $external_moderators
 * @property integer $sort_order
 * @property integer $duration
 */
class TaskItem extends ContentActiveRecord
{
    /**
     * @inheritdocs
     */
    protected $managePermission = ManageTasks::class;

    /**
     * @inheritdocs
     */
    protected $streamChannel = null;

    public $inputModerators;

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'task_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'title'], 'required'],
            [['task_id', 'sort_order', 'duration'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['inputModerators', 'description', 'external_moderators', 'notes'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['editMinutes'] = ['notes'];
        return $scenarios;
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => Yii::t('TaskModule.taskitem', 'Title'),
            'description' => Yii::t('TaskModule.taskitem', 'Description'),
            'duration' => Yii::t('TaskModule.taskitem', 'Duration (hh:mm)'),
            'notes' => Yii::t('TaskModule.taskitem', 'Minutes'),
        ];
    }

    /**
     * Creates a duplicated model by removing id and notes isNewRecord to true.
     * Note this method is only intended to render a TaskForm and not for saving the actual duplicate.
     */
    public function duplicate(Task $task)
    {
        // Fetch participant users relation before resetting id!
        $duplicate = new TaskItem($this->content->container, $this->content->visibility, [
            'task_id' => $task->id,
            'begin' => $this->begin,
            'end' => $this->end,
            'title' => $this->title,
            'description' => $this->description,
            'external_moderators' => $this->external_moderators,
            'duration' => $this->duration,
            'sort_order' => $this->sort_order
        ]);

        return $duplicate;
    }

    public function getTimeRangeText()
    {
        $formatter = Yii::$app->formatter;

        if(is_string($this->begin)) {
            return substr($this->begin, 0, 5) . " - " . substr($this->end, 0, 5);
        } else if($this->begin instanceof DateTime) {
            return $formatter->asTime($this->begin, 'short')." - ".$formatter->asTime($this->end, 'short');
        }
    }


    public function getTasks()
    {
        $query = \humhub\modules\tasks\models\Task::find();
        $query->leftJoin('task_task', 'task_task.task_id=task.id');
        $query->andWhere(['task_task.task_item_id' => $this->id]);
        $query->contentContainer($this->content->container);
        $query->orderBy(['task.deadline' => SORT_ASC]);
        return $query->all();
    }

    public function afterSave($insert, $changedAttributes)
    {
        Yii::$app->search->update($this->task);
        return parent::afterSave($insert, $changedAttributes);
    }

    public function beforeDelete()
    {
        foreach (TaskItemModerator::findAll(['task_item_id' => $this->id]) as $moderator) {
            $moderator->delete();
        }

        return parent::beforeDelete();
    }

    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }


    public function getModerators()
    {
        return $this->hasMany(TaskItemModerator::className(), ['task_item_id' => 'id']);
    }

    public function getModeratorUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->via('moderators');
    }

    public function getUrl()
    {
        return $this->content->container->createUrl('/task/index/view', ['id' => $this->task_id]);
    }

    public function getContentName()
    {
        return Yii::t('TaskModule.base', "Agenda Entry");
    }

    public function getContentDescription()
    {
        return $this->title;
    }

}
