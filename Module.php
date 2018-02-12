<?php

namespace humhub\modules\task;


use Yii;
use yii\helpers\Url;
use humhub\modules\task\permissions\ManageTasks;
use humhub\modules\task\models\Task;
use humhub\modules\space\models\Space;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerModule;

class Module extends ContentContainerModule
{

    /**
     * @inheritdoc
     */
    public function getContentContainerTypes()
    {
        return [
            Space::className(),
        ];
    }

    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer !== null) {
            return [
                new ManageTasks()
            ];
        }

        return parent::getPermissions($contentContainer);
    }



    /**
     * @inheritdoc
     */
    public function getContentContainerDescription(ContentContainerActiveRecord $container)
    {
        return Yii::t('TaskModule.base', 'Adds a task manager to this space.');
    }

    public function getConfigUrl()
    {
        return Url::to([
            '/task/config/'
        ]);
    }

    public function getName()
    {
        return Yii::t('TaskModule.base', 'Tasks');
    }

    public function getDescription()
    {
        return Yii::t('TaskModule.base', '“Tasks” is a complete task manager, which enables you to easily create tasks, define checklists and assign users and responsible users.');
    }

    /**
     * @inheritdoc
     */
    public function disable()
    {
        foreach (Task::find()->all() as $task) {
            $task->delete();
        }
        
        parent::disable();
    }

    /**
     * @inheritdoc
     */
    public function disableContentContainer(ContentContainerActiveRecord $container)
    {
        parent::disableContentContainer($container);

        foreach (Task::find()->contentContainer($container)->all() as $task) {
            $task->delete();
        }
    }
}
