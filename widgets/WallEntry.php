<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\task\widgets;

use humhub\modules\content\widgets\WallEntryControlLink;
use Yii;

/**
 * @inheritdoc
 */
class WallEntry extends \humhub\modules\content\widgets\WallEntry
{
    public $editMode = self::EDIT_MODE_MODAL;

    public function getEditUrl()
    {
        return $this->contentObject->content->container->createUrl('/task/index/edit', ['id' => $this->contentObject->id]);
    }

    public function isInModal()
    {
        return Yii::$app->request->get('cal');
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        \humhub\modules\meeting\assets\Assets::register($this->view);
        return $this->render('wallEntry', ['task' => $this->contentObject, 'justEdited' => $this->justEdited]);
    }

    public function getContextMenu()
    {
        if(!$this->isInModal() || !$this->contentObject->content->canEdit()) {
            return parent::getContextMenu();
        }

        // TODO: remove this after simplestream modal edit/delete runs as expected
        $this->controlsOptions['prevent'] = [\humhub\modules\content\widgets\EditLink::class , \humhub\modules\content\widgets\DeleteLink::class];
        $result = parent::getContextMenu();

//        $this->addControl($result, [
//            'class' => WallEntryControlLink::class,
//            'label' => Yii::t('MeetingModule.base', 'Edit'),
//            'icon' => 'fa-pencil',
//            'data-action-click' => 'task.editTask',
//            'data-action-url' => $this->contentObject->content->container->createUrl('/task/index/edit', ['id' => $this->contentObject->id, 'cal' => true]),
//            'sortOrder' => 100
//        ]);
//
//        $this->addControl($result, [
//            'class' => WallEntryControlLink::class,
//            'label' => Yii::t('MeetingModule.base', 'Delete'),
//            'icon' => 'fa-trash',
//            'data-action-click' => 'task.deleteTask',
//            'data-action-url' => $this->contentObject->content->container->createUrl('/task/index/delete', ['id' => $this->contentObject->id, 'cal' => true]),
//            'sortOrder' => 100
//        ]);

        return $result;
    }

    public function getWallEntryViewParams()
    {
        $params = parent::getWallEntryViewParams();
        if($this->isInModal()) {
            $params['showContentContainer'] = true;
        }
        return $params;
    }

}
