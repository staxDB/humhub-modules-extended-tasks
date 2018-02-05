<?php

namespace humhub\modules\task\models;

use Yii;
use humhub\modules\user\models\User;
use humhub\components\ActiveRecord;

/**
 * This is the model class for table "task_responsible".
 *
 * The followings are the available columns in table 'task_responsible':
 * @property integer $id
 * @property integer $task_id
 * @property integer $user_id
 */
class TaskResponsible extends ActiveRecord
{

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'task_responsible';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['task_id', 'required'],
            [['task_id', 'user_id'], 'integer'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => 'Task',
            'user_id' => 'User'
        ];
    }

    public function getUser()
    {
        if ($this->user_id) {
            return User::findOne(['id' => $this->user_id]);
        }
        return null;
    }

    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

}
