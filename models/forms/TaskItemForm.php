<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\task\models\forms;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\task\models\TaskItemModerator;

class TaskItemForm extends \yii\base\Model
{
    /**
     * @var \humhub\modules\task\models\TaskItem
     */
    public $model;

    /**
     * @var array
     */
    public $fileList = [];

    public function init()
    {
        parent::init();
    }

    public function rules()
    {
        return [
            ['fileList', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
        ];
    }

    public function attributeHints()
    {
        return [
        ];
    }

    public function load($data, $formName = null)
    {
        return parent::load($data) && $this->model->load($data);
    }

    public function save()
    {
        if(!$this->validate()) {
            return false;
        }

        if($this->model->save()) {
            $this->model->fileManager->attach($this->fileList);
            return true;
        }
        return false;
    }
}