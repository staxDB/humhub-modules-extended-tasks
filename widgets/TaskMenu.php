<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\task\widgets;

use Yii;
use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\task\models\Task;
use humhub\modules\task\models\TaskItem;

/**
 * Widget for rendering the menue buttons for a Task.
 * @author buddh4
 */
class TaskMenu extends \yii\base\Widget
{

    /**
     * @var Task
     */
    public $task;

    /**
     * @var \humhub\modules\content\components\ContentContainerActiveRecord Current content container.
     */
    public $contentContainer;

    /**
     * @var boolean Determines if the user has write permissions.
     */
    public $canEdit;

    /**
     * @inheritdoc
     */
    public function run()
    {

        $deleteUrl = $this->contentContainer->createUrl('/task/index/delete', ['id' => $this->task->id]);
        $editUrl = $this->contentContainer->createUrl('/task/index/edit', ['id' => $this->task->id]);
        $extensionRequestUrl = $this->contentContainer->createUrl('/task/index/extend', ['id' => $this->task->id]);
        $resetUrl = $this->contentContainer->createUrl('/task/index/reset', ['id' => $this->task->id]);

        return $this->render('taskMenuDropdown', [
                    'deleteUrl' => $deleteUrl,
                    'editUrl' => $editUrl,
                    'canEdit' => $this->canEdit,
                    'extensionRequestUrl' => $extensionRequestUrl,
                    'canRequestExtension' => ( $this->task->canRequestExtension()),
                    'resetUrl' => $resetUrl,
                    'canReset' => $this->task->canResetTask()
        ]);
    }

}
