<?php

namespace humhub\modules\task;


use Yii;
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
