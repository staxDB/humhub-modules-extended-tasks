<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\task\notifications;

use Yii;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\space\models\Space;
use yii\helpers\Html;

/**
 * Notifies an admin about reported content
 *
 * @since 0.5
 */
class RemindResponsibleStart extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public $suppressSendToOriginator = false;

    /**
     * @inheritdoc
     */
    public $moduleId = 'task';

    /**
     * @inheritdoc
     */
    public $viewName = "taskNotification";

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new TaskNotificationCategory();
    }

    public function html()
    {
        if ($this->source->content->container instanceof Space) {
            return Yii::t('TaskModule.notifications', '{userName}, task {task} in space {spaceName} starts at {dateTime}.', [
                '{userName}' => Html::tag('strong', Html::encode($this->originator->displayName)),
                '{task}' => Html::tag('strong', Html::encode($this->getContentInfo($this->source, false))),
                '{spaceName}' => Html::tag('strong', Html::encode($this->source->content->container->displayName)),
                '{dateTime}' => Html::encode($this->source->formattedStartDateTime)
            ]);
        } else {
            return Yii::t('TaskModule.notifications', '{userName}, task {task} starts at {dateTime}.', [
                'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
                '{task}' => Html::tag('strong', Html::encode($this->getContentInfo($this->source, false))),
                '{dateTime}' => Html::encode($this->source->formattedStartDateTime)
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject()
    {
        if ($this->source->content->container instanceof Space) {
            return Yii::t('TaskModule.notifications', '{userName}, task {task} in space {spaceName} starts at {dateTime}.', [
                '{displayName}' => Html::tag('strong', Html::encode($this->originator->displayName)),
                '{task}' => Html::tag('strong', Html::encode($this->getContentInfo($this->source, false))),
                '{spaceName}' => Html::tag('strong', Html::encode($this->source->content->container->displayName)),
                '{dateTime}' => Html::encode($this->source->formattedStartDateTime)
            ]);
        } else {
            return Yii::t('TaskModule.notifications', '{userName}, task {task} starts at {dateTime}.', [
                '{displayName}' => Html::tag('strong', Html::encode($this->originator->displayName)),
                '{task}' => Html::tag('strong', Html::encode($this->getContentInfo($this->source, false))),
                '{dateTime}' => Html::encode($this->source->formattedStartDateTime)
            ]);
        }
    }
}
