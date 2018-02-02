<?php
namespace humhub\modules\task\models;
use DateTime;
use humhub\components\ActiveRecord;
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
 * @property string $title
 * @property integer $completed
 * @property string $notes
 */
class TaskItem extends ActiveRecord
{
    /**
     * @inheritdocs
     */
    protected $managePermission = ManageTasks::class;

    /**
     * @inheritdocs
     */
//    protected $streamChannel = null;

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
            [['task_id', 'completed'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['description', 'notes'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
//    public function scenarios()
//    {
//        $scenarios = parent::scenarios();
//        return $scenarios;
//    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => Yii::t('TaskModule.models_taskitem', 'Title'),
            'completed' => Yii::t('TaskModule.models_taskitem', 'Completed'),
            'notes' => Yii::t('TaskModule.models_taskitem', 'Notiz'),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        return parent::afterSave($insert, $changedAttributes);
    }

    public function beforeDelete()
    {
        return parent::beforeDelete();
    }

    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

//    public function getUrl()
//    {
//        return $this->content->container->createUrl('/task/index/view', ['id' => $this->task_id]);
//    }

//    public function getContentName()
//    {
//        return Yii::t('TaskModule.base', "Task Item");
//    }

//    public function getContentDescription()
//    {
//        return $this->title;
//    }

    public static function filterValidItems($itemArr)
    {
        if($itemArr == null) {
            return [];
        }

        $result = [];
        foreach ($itemArr as $key => $itemText) {
            if($itemText != null && $itemText !== '') {
                $result[$key] = $itemText;
            }
        }
        return $result;
    }
}