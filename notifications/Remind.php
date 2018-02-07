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
class Remind extends BaseNotification
{
    /**
     * @inheritdoc
     */
//    public $suppressSendToOriginator = false;

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
            return Yii::t('TaskModule.notifications', '{userName} reminds you to work on Task {task} in space {spaceName}.', [
                '{userName}' => Html::tag('strong', Html::encode($this->originator->displayName)),
                '{task}' => Html::tag('strong', Html::encode($this->getContentInfo($this->source, false))),
                '{spaceName}' => Html::encode($this->source->content->container->displayName)
            ]);
        } else {
            return Yii::t('TaskModule.notifications', '{userName} reminds you to work on Task {task}.', [
                'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
                '{task}' => '<strong>' . $this->getContentInfo($this->source, false) . '</strong>',
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject()
    {
        if ($this->source->content->container instanceof Space) {
            return Yii::t('TaskModule.notifications', '{userName} reminds you to work on Task {task} in space {spaceName}.', [
                '{displayName}' => Html::tag('strong', Html::encode($this->originator->displayName)),
                '{task}' => '<strong>' . $this->getContentInfo($this->source, false) . '</strong>',
                '{spaceName}' => Html::encode($this->source->content->container->displayName)
            ]);
        } else {
            return Yii::t('TaskModule.notifications', '{userName} reminds you to work on this {task}.', [
                '{displayName}' => Html::tag('strong', Html::encode($this->originator->displayName)),
                '{task}' => '<strong>' . $this->getContentInfo($this->source, false) . '</strong>'
            ]);
        }
    }
}
