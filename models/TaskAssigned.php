<?php

namespace humhub\modules\task\models;

use Yii;
use humhub\modules\user\models\User;
use humhub\components\ActiveRecord;

/**
 * This is the model class for table "task_assigned".
 *
 * The followings are the available columns in table 'task_assigned':
 * @property integer $id
 * @property integer $task_id
 * @property integer $user_id
 * @property integer $request_sent
 */

class TaskAssigned extends ActiveRecord
{

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'task_assigned';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['task_id', 'required'],
            [['task_id', 'user_id', 'request_sent'], 'integer'],
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
            'user_id' => 'User',
            'request_sent' => 'Extend deadline request'
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

    public function hasRequestedExtension()
    {
        return boolval($this->request_sent);
    }

}
