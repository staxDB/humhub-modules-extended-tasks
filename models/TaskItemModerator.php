<?php

namespace humhub\modules\task\models;

use Yii;
use humhub\modules\user\models\User;

/**
 * This is the model class for table "task_item_moderator".
 *
 * The followings are the available columns in table 'task_item_moderator':
 * @property integer $id
 * @property integer $task_item_id
 * @property integer $user_id
 * @property string $name
 */
class TaskItemModerator extends \humhub\components\ActiveRecord
{

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'task_item_moderator';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['task_item_id', 'required'],
            [['task_item_id', 'user_id'], 'integer'],
            ['name', 'string', 'max' => 255],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'name' => 'Name', Yii::t('TaskModule.taskitemmoderator', 'Name'),
        );
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
