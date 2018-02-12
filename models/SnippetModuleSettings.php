<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\task\models;

use Yii;
use \yii\base\Model;

class SnippetModuleSettings extends Model
{
    /**
     * @var boolean determines if the dashboard widget should be shown or not (default true)
     */
    public $myTasksSnippetShow = true;

    /**
     * @var int maximum amount of dashboard event items
     */
    public $myTasksSnippetMaxItems = 5;


    public function init()
    {
        $module = Yii::$app->getModule('task');
        $this->myTasksSnippetShow = $module->settings->get('myTasksSnippetShow', $this->myTasksSnippetShow);
        $this->myTasksSnippetMaxItems = $module->settings->get('myTasksSnippetMaxItems', $this->myTasksSnippetMaxItems);
    }

    public function showMyTasksSnippet()
    {
        return $this->myTasksSnippetShow;
    }

    /**
     * Static initializer
     * @return \self
     */
    public static function instantiate()
    {
        return new self;
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['myTasksSnippetShow'],  'boolean'],
            ['myTasksSnippetMaxItems',  'number', 'min' => 1, 'max' => 30]
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'myTasksSnippetShow' => Yii::t('TaskModule.config', 'Show snippet'),
            'myTasksSnippetMaxItems' => Yii::t('TaskModule.config', 'Max tasks items'),
        ];
    }

    public function save()
    {
        if(!$this->validate()) {
            return false;
        }

        $module = Yii::$app->getModule('task');
        $module->settings->set('myTasksSnippetShow', $this->myTasksSnippetShow);
        $module->settings->set('myTasksSnippetMaxItems', $this->myTasksSnippetMaxItems);
        return true;
    }
}
