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

    /**
     * @var int defines the snippet widgets sort order 
     */
    public $myTasksSnippetSortOrder = 0;

    /**
     * @var boolean determines if the calendar top menu item adn dashboard widget should only be shown if the user installed the calendar module in his profile
     */
    public $showIfInstalled = false;

    public function init()
    {
        $module = Yii::$app->getModule('task');
        $this->myTasksSnippetShow = $module->settings->get('myTasksSnippetShow', $this->myTasksSnippetShow);
        $this->myTasksSnippetSortOrder = $module->settings->get('myTasksSnippetSortOrder', $this->myTasksSnippetSortOrder);
        $this->myTasksSnippetMaxItems = $module->settings->get('myTasksSnippetMaxItems', $this->myTasksSnippetMaxItems);
        $this->showIfInstalled = $module->settings->get('showIfInstalled', $this->showIfInstalled);
    }

    public function showMyTasksSnippet()
    {
        return $this->myTasksSnippetShow && $this->showGlobalCalendarItems();
    }

    public function showGlobalCalendarItems()
    {
        return !self::instantiate()->showIfInstalled || (!Yii::$app->user->isGuest && Yii::$app->user->getIdentity()->isModuleEnabled('task'));
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
            [['myTasksSnippetShow', 'showIfInstalled'],  'boolean'],
            ['myTasksSnippetSortOrder',  'number', 'min' => 0],
            ['myTasksSnippetMaxItems',  'number', 'min' => 1, 'max' => 30]
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'myTasksSnippetShow' => Yii::t('CalendarModule.config', "Show snippet"),
            'myTasksSnippetSortOrder' => Yii::t('CalendarModule.config', 'Sort order'),
            'myTasksSnippetMaxItems' => Yii::t('CalendarModule.config', 'Max event items'),
            'showIfInstalled' => Yii::t('CalendarModule.config', 'Only show top menu item and snippet if the module is installed in the users profile'),
        ];
    }

    public function getDurationItems()
    {
        return [
            self::DURATION_WEEK => Yii::t('CalendarModule.config', 'One week'),
            self::DURATION_MONTH => Yii::t('CalendarModule.config', 'One month'),
            self::DURATION_HALF_YEAR => Yii::t('CalendarModule.config', 'Half a year'),
            self::DURATION_YEAR => Yii::t('CalendarModule.config', 'One year'),
        ];
    }

    public function save()
    {
        if(!$this->validate()) {
            return false;
        }

        $module = Yii::$app->getModule('calendar');
        $module->settings->set('myTasksSnippetShow', $this->myTasksSnippetShow);
        $module->settings->set('myTasksSnippetSortOrder', $this->myTasksSnippetSortOrder);
        $module->settings->set('myTasksSnippetMaxItems', $this->myTasksSnippetMaxItems);
        $module->settings->set('showIfInstalled', $this->showIfInstalled);
        return true;
    }
}
