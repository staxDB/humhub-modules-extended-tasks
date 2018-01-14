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
use humhub\modules\task\widgets\TaskAddon;
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
     * @var integer Task id in case we duplicate a task
     */
    public $duplicateId;

    /**
     * @var Task instance of task to duplicate
     */
    public $duplicate;

    /**
     * @var int whether or not to duplicate items if a task duplicate is given
     */
    public $duplicateItems = 1;

    /**
     * @var string startDate of the task
     */
    public $startDate;

    /**
     * @var string endDate of the task
     */
    public $endDate;

    /**
     * @var string time zone of the task
     */
    public $timeZone;

    /**
     * @var string start time of the task
     */
    public $startTime;

    /**
     * @var string end time of the task
     */
    public $endTime;

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
            [['timeZone'], 'in', 'range' => DateTimeZone::listIdentifiers()],
            [['startDate'], DbDateValidator::className(), 'format' => Yii::$app->params['formatter']['defaultDateFormat'], 'timeAttribute' => 'startTime', 'timeZone' => $this->timeZone],
            [['endDate'], DbDateValidator::className(), 'format' => Yii::$app->params['formatter']['defaultDateFormat'], 'timeAttribute' => 'endTime', 'timeZone' => $this->timeZone],
            [['startTime', 'endTime'], 'date', 'type' => 'time', 'format' => $this->getTimeFormat()],
            [['duplicateId', 'itemId'], 'integer'],
            ['duplicateItems', 'integer', 'min' => 0, 'max' => 1],
            [['duplicate'], 'validateDuplicate'],
        ];
    }

    public function getTimeFormat()
    {
        return Yii::$app->formatter->isShowMeridiem() ? 'h:mm a' : 'php:H:i';
    }

    public function validateDuplicate()
    {
        if($this->duplicateId && !$this->duplicate) {
            throw new \InvalidArgumentException('Task to duplicate not found!');
        }

        if($this->duplicate && $this->duplicate->content->contentcontainer_id != $this->task->content->contentcontainer_id) {
            throw new \InvalidArgumentException('Tried to duplicate a task from another space!');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'endTime' => Yii::t('TaskModule.task', 'End'),
            'startTime' => Yii::t('TaskModule.task', 'Begin'),
            'startDate' => Yii::t('TaskModule.task', 'Date'),
            'duplicateItems' => Yii::t('TaskModule.task', 'Duplicate agenda entries'),
        ]);
    }

    public function getTitle()
    {
        if($this->itemId) {
            return Yii::t('TaskModule.base', '<strong>Shift</strong> agenda entry to new task');
        }

        if($this->duplicateId) {
            return Yii::t('TaskModule.base', '<strong>Duplicate</strong> task');
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

        if($this->duplicateId) {
            $this->duplicate = Task::findOne($this->duplicateId);
        }

        return $this->task->load($data);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        // Before DbDateValidator translates the time zones from user to system time zone we use the cloned startDate as endDate but with the endTime
        if (!empty($this->startDate)) {
            $this->endDate = $this->startDate;
        }
        return true;
    }

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

        $this->task->date = $this->startDate;

        Yii::$app->formatter->timeZone = Yii::$app->timeZone;
        $this->task->begin = Yii::$app->formatter->asTime(new DateTime($this->startDate), 'php:H:i:s');
        $this->task->end = Yii::$app->formatter->asTime(new DateTime($this->endDate), 'php:H:i:s');
        Yii::$app->i18n->autosetLocale();

        if ($this->task->save()) {
            if($this->duplicate && $this->duplicateItems) {
                foreach ($this->duplicate->items as $itemToDuplicate) {
                    $itemToDuplicate->duplicate($this->task)->save();
                }
            }

            // If an itemId is given we shift the given item to the current task
            if ($this->itemId) {
                $this->task->shiftItem($this->itemId);
            }
            return true;
        }

        return false;
    }

    /**
     * Translates startDate/time and endDate/time of the given task from system time zone to given time zone.
     */
    public function translateToUserTimeZone()
    {
        $startTime = $this->getTaskDateTime($this->task->begin);
        $endTime = $this->getTaskDateTime($this->task->end);

        Yii::$app->formatter->timeZone = $this->timeZone;

        $this->startDate = Yii::$app->formatter->asDateTime($startTime, 'php:Y-m-d');
        $this->startTime = Yii::$app->formatter->asTime($startTime, $this->getTimeFormat());

        $this->endDate = Yii::$app->formatter->asDateTime($endTime, 'php:Y-m-d');
        $this->endTime = Yii::$app->formatter->asTime($endTime, $this->getTimeFormat());

        Yii::$app->i18n->autosetLocale();
    }

    private function getTaskDateTime($timeVal)
    {
        return new DateTime($this->task->date . ' ' . $timeVal, new DateTimeZone(Yii::$app->timeZone));
    }

    public function getFormattedStartDate()
    {
        return $this->task->getFormattedStartDate($this->isUserTimeZone() ? null : $this->timeZone);
    }

    public function getFormattedBeginTime()
    {
        // We just change the formatter timezone if it another timezone was selected by user.
        return $this->task->getFormattedBeginTime($this->isUserTimeZone() ? null : $this->timeZone);
    }

    public function getFormattedEndTime($timeZone = null)
    {
        return $this->task->getFormattedEndTime($this->isUserTimeZone() ? null : $this->timeZone);
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

    public function getParticipantPickerUrl()
    {
        return $this->task->content->container->createUrl('/task/index/participant-picker', ['id' => $this->task->id]);
    }

    public function updateTime($start = null, $end = null)
    {
        $startDate = new DateTime($start, new DateTimeZone($this->getUserTimeZone()));
        $endDate = new DateTime($end, new DateTimeZone($this->getUserTimeZone()));

        Yii::$app->formatter->timeZone = Yii::$app->timeZone;

        // Note we ignore the end date (just use the time) since a task can't span over several days
        $this->task->date = Yii::$app->formatter->asDatetime($startDate, 'php:Y-m-d H:i:s');
        $this->task->begin = Yii::$app->formatter->asTime($startDate, 'php:H:i:s');
        $this->task->end = Yii::$app->formatter->asTime($endDate, 'php:H:i:s');

        Yii::$app->i18n->autosetLocale();

        return $this->task->save();
    }

}
