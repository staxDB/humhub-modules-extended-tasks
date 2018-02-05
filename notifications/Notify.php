<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\task\notifications;

use Yii;
use humhub\modules\notification\components\BaseNotification;
use yii\helpers\Html;

/**
 * Notifies an admin about reported content
 *
 * @since 0.5
 */
class Notify extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = 'task';

    /**
     * @inheritdoc
     */
    public $viewName = "notify";

    /**
     *  @inheritdoc
     */
    public function category()
    {
        return new TaskNotificationCategory();
    }
    
    public function html() {
        return Yii::t('TaskModule.views_notifications_invited', '{userName} assigned you to {task}.', [
            '{userName}' => '<strong>' . Html::encode($this->originator->displayName) . '</strong>',
            '{task}' => '<strong>' . $this->getContentInfo($this->source) . '</strong>'
        ]);
    }

    /**
     *  @inheritdoc
     */
    public function getMailSubject()
    {
        return Yii::t('TaskModule.views_notifications_invited', '{userName} assigned you to {task}.', [
            '{userName}' => '<strong>' . Html::encode($this->originator->displayName) . '</strong>',
            '{task}' => '<strong>' . $this->getContentInfo($this->source) . '</strong>'
        ]);
    }
}
