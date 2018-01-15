<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 13.07.2017
 * Time: 22:00
 */

namespace humhub\modules\task\models\forms;

use DateTime;
use DateTimeZone;
use humhub\libs\DbDateValidator;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\task\models\Task;
use Yii;
use yii\base\Model;

class TaskForm extends Model
{

    /**
     * @var Task
     */
    public $task;

    /**
     * @var integer TaskItem id used in case we shift an item to a new task
     */
    public $itemId;

    /**
     * @var string $deadline of the task
     */
    public $deadline;

    /**
     * @var string time zone of the task
     */
    public $timeZone;

    /**
     * @var boolean defines if the request came from a calendar
     */
    public $cal;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->timeZone) {
            $this->timeZone = Yii::$app->formatter->timeZone;
        }

        if ($this->task) {
            $this->translateToUserTimeZone();
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['deadline'], DbDateValidator::className(), 'format' => Yii::$app->params['formatter']['defaultDateFormat'], 'timeZone' => $this->timeZone],
            [['itemId'], 'integer'],
        ];
    }

//    public function getTimeFormat()
//    {
//        return Yii::$app->formatter->isShowMeridiem() ? 'h:mm a' : 'php:H:i';
//    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'deadline' => Yii::t('TaskModule.task', 'Deadline'),
        ]);
    }

    public function getTitle()
    {
        if($this->itemId) {
            return Yii::t('TaskModule.base', '<strong>Shift</strong> agenda entry to new task');
        }

        if($this->task->isNewRecord) {
           return Yii::t('TaskModule.views_index_edit', '<strong>Create</strong> new task');
        }

        return Yii::t('TaskModule.views_index_edit', '<strong>Edit</strong> task');
    }

    /**
     * Instantiates a new task for the given ContentContainerActiveRecord.
     *
     * @param ContentContainerActiveRecord $contentContainer
     */
    public function createNew(ContentContainerActiveRecord $contentContainer)
    {
        $this->task = new Task($contentContainer, Content::VISIBILITY_PRIVATE);
    }

    /**
     * Loads this model and the task model with the given data.
     *
     * @inheritdoc
     *
     * @param array $data
     * @param null $formName
     * @return bool
     */
    public function load($data, $formName = null)
    {
        parent::load($data);

        return $this->task->load($data);
    }

    /**
     * @inheritdoc
     */
//    public function beforeValidate()
//    {
//        // Before DbDateValidator translates the time zones from user to system time zone we use the cloned startDate as endDate but with the endTime
//        if (!empty($this->deadline)) {
//            $this->endDate = $this->startDate;
//        }
//        return true;
//    }

    /**
     * Validates and saves the task instance.
     * @return bool
     */
    public function save()
    {
        $isNew = $this->task->isNewRecord;

        if (!$this->validate()) {
            $this->task->validate();
            return false;
        }

        $this->task->deadline = $this->deadline;

        Yii::$app->formatter->timeZone = Yii::$app->timeZone;
        Yii::$app->i18n->autosetLocale();

        if ($this->task->save()) {
            return true;
        }

        return false;
    }

    /**
     * Translates startDate/time and endDate/time of the given task from system time zone to given time zone.
     */
    public function translateToUserTimeZone()
    {
        $deadline = $this->getTaskDateTime();

        Yii::$app->formatter->timeZone = $this->timeZone;

        $this->deadline = Yii::$app->formatter->asDateTime($deadline, 'php:Y-m-d');

        Yii::$app->i18n->autosetLocale();
    }

    private function getTaskDateTime()
    {
        return new DateTime($this->task->deadline, new DateTimeZone(Yii::$app->timeZone));
    }

    public function isUserTimeZone()
    {
        return $this->timeZone === $this->getUserTimeZone();
    }

    public function getUserTimeZone()
    {
        return Yii::$app->formatter->timeZone;
    }

    public function getSubmitUrl()
    {
        return $this->task->content->container->createUrl('/task/index/edit', [
            'id' => $this->task->id,
            'itemId' => $this->itemId,
            'cal' => $this->cal
        ]);
    }

    public function getDeleteUrl()
    {
        return $this->task->content->container->createUrl('delete', [
            'id' => $this->task->id,
            'cal' => $this->cal
        ]);
    }

    public function getTaskUserPickerUrl()
    {
        return $this->task->content->container->createUrl('/task/index/task-user-picker', ['id' => $this->task->id]);
    }
}
