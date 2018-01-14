<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\task\widgets;

use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\task\models\Task;

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
        $printUrl = $this->contentContainer->createUrl('/task/index/print', ['id' => $this->task->id]);
        $shareUrl = $this->contentContainer->createUrl('/task/index/share', ['id' => $this->task->id]);
        $duplicateUrl = $this->contentContainer->createUrl('/task/index/duplicate', ['id' => $this->task->id]);

        return $this->render('taskMenuDropdown', [
                    'deleteUrl' => $deleteUrl,
                    'editUrl' => $editUrl,
                    'printUrl' => $printUrl,
                    'shareUrl' => $shareUrl,
                    'canEdit' => $this->canEdit,
                    'duplicateUrl' => $duplicateUrl
        ]);
    }

}
