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
class Remind extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = 'task';

    /**
     * @inheritdoc
     */
    public $viewName = "remind";

    /**
     *  @inheritdoc
     */
    public function category()
    {
        return new TaskNotificationCategory();
    }
    
    public function html() {
        return Yii::t('TaskModule.notifications', 'You have to work on this {task}.', [
            '{task}' => '<strong>' . $this->getContentInfo($this->source) . '</strong>'
        ]);
    }

    /**
     *  @inheritdoc
     */
    public function getMailSubject()
    {
        return Yii::t('TaskModule.notifications', 'You have to work on this {task}.', [
            '{task}' => '<strong>' . $this->getContentInfo($this->source) . '</strong>'
        ]);
    }
}
