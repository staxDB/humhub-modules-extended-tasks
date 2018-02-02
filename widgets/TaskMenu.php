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
        $printUrl = $this->contentContainer->createUrl('/task/index/print', ['id' => $this->task->id]);
        $shareUrl = $this->contentContainer->createUrl('/task/index/share', ['id' => $this->task->id]);

        $linkLabel = '';
        $changeStatusUrl = '';
        switch ($this->task->status) {
            case Task::STATUS_PENDING:
                $linkLabel = Yii::t('TaskModule.views_index_index', 'Begin Task');
                $changeStatusUrl = $this->contentContainer->createUrl('/task/index/status', ['id' => $this->task->id, 'status' => Task::STATUS_IN_PROGRESS]);
                break;
            case Task::STATUS_IN_PROGRESS:
                if (Yii::$app->user->getIdentity() !== $this->task->getCreatedBy()) {
                    $linkLabel = Yii::t('TaskModule.views_index_index', 'Let Task Review');
                    $changeStatusUrl = $this->contentContainer->createUrl('/task/index/status', ['id' => $this->task->id, 'status' => Task::STATUS_PENDING_REVIEW]);
                }
                else {
                    $linkLabel = Yii::t('TaskModule.views_index_index', 'Finish Task');
                    $changeStatusUrl = $this->contentContainer->createUrl('/task/index/status', ['id' => $this->task->id, 'status' => Task::STATUS_COMPLETED]);
                }
                break;
            case Task::STATUS_PENDING_REVIEW:
                $linkLabel = Yii::t('TaskModule.views_index_index', 'Finish Task');
                $changeStatusUrl = $this->contentContainer->createUrl('/task/index/status', ['id' => $this->task->id, 'status' => Task::STATUS_COMPLETED]);
                break;
            case Task::STATUS_COMPLETED:
                $linkLabel = Yii::t('TaskModule.views_index_index', 'Reset Task');
                $changeStatusUrl = $this->contentContainer->createUrl('/task/index/status', ['id' => $this->task->id, 'status' => Task::STATUS_PENDING]);
                break;
        }

        return $this->render('taskMenuDropdown', [
                    'deleteUrl' => $deleteUrl,
                    'editUrl' => $editUrl,
                    'printUrl' => $printUrl,
                    'shareUrl' => $shareUrl,
                    'canEdit' => $this->canEdit,
                    'changeStatusUrl' => $changeStatusUrl,
                    'linkLabel' => $linkLabel
        ]);
    }

}
