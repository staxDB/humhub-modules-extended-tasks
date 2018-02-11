<?php
namespace humhub\modules\task\models;

use DateTime;
use humhub\components\ActiveRecord;
use Yii;
use humhub\modules\task\permissions\ManageTaskReminders;
use humhub\modules\user\models\User;

/**
 * This is the model class for table "task_reminder".
 *
 * The followings are the available columns in table 'task_reminder':
 * @property integer $id
 * @property integer $task_id
 * @property integer $remind_mode
 * @property integer $start_reminder_sent
 * @property integer $end_reminder_sent
 */
class TaskReminder extends ActiveRecord
{
    /**
     * @inheritdocs
     */
    protected $managePermission = ManageTaskReminders::class;

    /**
     * @inheritdocs
     */
    protected $streamChannel = null;

    /**
     * Remind Mode
     */
    const REMIND_NONE = 0;
    const REMIND_ONE_HOUR = 1;
    const REMIND_TWO_HOURS = 2;
    const REMIND_ONE_DAY = 3;
    const REMIND_TWO_DAYS = 4;
    const REMIND_ONE_WEEK = 5;
    const REMIND_TWO_WEEKS = 6;
    const REMIND_THREE_WEEKS = 7;
    const REMIND_ONE_MONTH = 8;

    /**
     * @var array all given remind modes as array
     */
    public static $remindModes = [
        self::REMIND_NONE,
        self::REMIND_ONE_HOUR,
        self::REMIND_TWO_HOURS,
        self::REMIND_ONE_DAY,
        self::REMIND_TWO_DAYS,
        self::REMIND_TWO_WEEKS,
        self::REMIND_THREE_WEEKS,
        self::REMIND_ONE_MONTH
    ];

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'task_reminder';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'remind_mode'], 'required'],
            [['task_id', 'start_reminder_sent', 'end_reminder_sent'], 'integer'],
            [['remind_mode'], 'in', 'range' => self::$remindModes],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return $scenarios;
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => Yii::t('TaskModule.models_taskReminder', 'Task'),
            'remind_mode' => Yii::t('TaskModule.models_taskReminder', 'Remind Mode'),
            'remind_sent' => Yii::t('TaskModule.models_task', 'Reminder sent'),
        ];
    }

    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

    public static function getRemindModeItems()
    {
        return [
            self::REMIND_NONE => Yii::t('TaskModule.models_taskReminder', 'Do not remind'),
            self::REMIND_ONE_HOUR => Yii::t('TaskModule.models_taskReminder', 'About 1 Hour before'),
            self::REMIND_TWO_HOURS => Yii::t('TaskModule.models_taskReminder', 'About 2 Hours before'),
            self::REMIND_ONE_DAY => Yii::t('TaskModule.models_taskReminder', '1 Day before'),
            self::REMIND_TWO_DAYS => Yii::t('TaskModule.models_taskReminder', '2 Days before'),
            self::REMIND_ONE_WEEK => Yii::t('TaskModule.models_taskReminder', '1 Week before'),
            self::REMIND_TWO_WEEKS => Yii::t('TaskModule.models_taskReminder', '2 Weeks before'),
            self::REMIND_THREE_WEEKS => Yii::t('TaskModule.models_taskReminder', '3 Weeks before'),
            self::REMIND_ONE_MONTH => Yii::t('TaskModule.models_taskReminder', '1 Month before'),
        ];
    }

    public function getRemindMode()
    {
        switch ($this->remind_mode){
            case (self::REMIND_NONE):
                return Yii::t('TaskModule.models_taskReminder', 'Do not remind');
                break;
            case (self::REMIND_ONE_HOUR):
                return Yii::t('TaskModule.models_taskReminder', 'About 1 Hour before');
                break;
            case (self::REMIND_TWO_HOURS):
                return Yii::t('TaskModule.models_taskReminder', 'About 2 Hours before');
                break;
            case (self::REMIND_ONE_DAY):
                return Yii::t('TaskModule.models_taskReminder', '1 Day before');
                break;
            case (self::REMIND_TWO_DAYS):
                return Yii::t('TaskModule.models_taskReminder', '2 Days before');
                break;
            case (self::REMIND_ONE_WEEK):
                return Yii::t('TaskModule.models_taskReminder', '1 Week before');
                break;
            case (self::REMIND_TWO_WEEKS):
                return Yii::t('TaskModule.models_taskReminder', '2 Weeks before');
                break;
            case (self::REMIND_THREE_WEEKS):
                return Yii::t('TaskModule.models_taskReminder', '3 Weeks before');
                break;
            case (self::REMIND_ONE_MONTH):
                return Yii::t('TaskModule.models_taskReminder', '1 Month before');
                break;
            default:
                return;
        }
    }

    public function canSendRemind(DateTime $now, DateTime $dateTime)
    {
        if ($now === '' || $dateTime === '')
            return false;

        $modifiedTime = clone $dateTime;
        $modifiedEnd = clone $dateTime;

//        echo ($now->format('Y-m-d H:i:s') . ', ' . $dateTime->format('Y-m-d H:i:s') . ', allday=' . $allday);

//        if ($allday) {
//            $modifiedTime = $modifiedTime->setTime('00', '00', '00');
//            $modifiedEnd = $modifiedEnd->setTime('23', '59', '59');
//        }

        switch ($this->remind_mode) {
            case self::REMIND_NONE :
                return false;
                break;
            case self::REMIND_ONE_HOUR :
                // if has task reminder 2 hours and not sent yet --> skip this one
                $modifiedTime = $modifiedTime->modify('-1 hour');
                break;
            case self::REMIND_TWO_HOURS :
                $modifiedTime = $modifiedTime->modify('-2 hours');
                $modifiedEnd = $modifiedEnd->modify('-1 hour');
                break;
            case self::REMIND_ONE_DAY :
                $modifiedTime = $modifiedTime->modify('-1 day');
                $modifiedTime = $modifiedTime->setTime('00', '00', '00');
                $modifiedEnd = $modifiedEnd->modify('-1 day');
                $modifiedEnd = $modifiedEnd->setTime('23', '59', '59');
                break;
            case self::REMIND_TWO_DAYS :
                $modifiedTime = $modifiedTime->modify('-2 days');
                $modifiedTime = $modifiedTime->setTime('00', '00', '00');
                $modifiedEnd = $modifiedEnd->modify('-2 days');
                $modifiedEnd = $modifiedEnd->setTime('23', '59', '59');
                break;
            case self::REMIND_ONE_WEEK :
                $modifiedTime = $modifiedTime->modify('-1 week');
                $modifiedTime = $modifiedTime->setTime('00', '00', '00');
                $modifiedEnd = $modifiedEnd->modify('-1 week');
                $modifiedEnd = $modifiedEnd->setTime('23', '59', '59');
                break;
            case self::REMIND_TWO_WEEKS :
                $modifiedTime = $modifiedTime->modify('-2 weeks');
                $modifiedTime = $modifiedTime->setTime('00', '00', '00');
                $modifiedEnd = $modifiedEnd->modify('-2 weeks');
                $modifiedEnd = $modifiedEnd->setTime('23', '59', '59');
                break;
            case self::REMIND_THREE_WEEKS :
                $modifiedTime = $modifiedTime->modify('-3 weeks');
                $modifiedTime = $modifiedTime->setTime('00', '00', '00');
                $modifiedEnd = $modifiedEnd->modify('-3 weeks');
                $modifiedEnd = $modifiedEnd->setTime('23', '59', '59');
                break;
            case self::REMIND_ONE_MONTH :
                $modifiedTime = $modifiedTime->modify('-1 month');
                $modifiedTime = $modifiedTime->setTime('00', '00', '00');
                $modifiedEnd = $modifiedEnd->modify('-1 month');
                $modifiedEnd = $modifiedEnd->setTime('23', '59', '59');
                break;
            default:
                return false;
                break;
        }



//        echo ($modifiedTime->format('Y-m-d H:i:s') . ' <= ' . $now->format('Y-m-d H:i:s') . ' <= ' . $dateTime->format('Y-m-d H:i:s'));
//        echo ($dateTime->format('Y-m-d H:i:s') . ' >= ' . $now->format('Y-m-d H:i:s') . ' && ' . $modifiedTime->format('Y-m-d H:i:s') . ' <= ' . $now->format('Y-m-d H:i:s'));
//        echo ('true = '. ($dateTime > $now && $modifiedTime <= $now));
//        die();

        if ($modifiedEnd > $now && $modifiedTime <= $now)
            return true;
        else
            return false;
    }

    public function handleRemind(DateTime $now, Task $task)
    {
        
        if (!$this->start_reminder_sent) {
            if (self::canSendRemind($now, $task->getStartDateTime())) {
                $task->remindUserOfStart();
                $this->updateAttributes(['start_reminder_sent' => 1]);
                return true;
            }
        }
        if ($task->getStartDateTime() < $now)
            $this->updateAttributes(['start_reminder_sent' => 1]);

        if (!$this->end_reminder_sent) {
            if (self::canSendRemind($now, $task->getEndDateTime())) {
                $task->remindUserOfEnd();
                $this->updateAttributes(['end_reminder_sent' => 1]);
                return true;
            }
        }
        if ($task->getEndDateTime() < $now)
            $this->updateAttributes(['end_reminder_sent' => 1]);

        return false;
    }
}