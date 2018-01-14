<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\task\widgets;

use humhub\modules\file\handler\FileHandlerCollection;

/**
 * Widget for rendering the menue buttons for the TaskItem.
 * @author buddh4
 */
class TaskItemMenu extends \yii\base\Widget
{

    /**
     * var TaskItem
     */
    public $item;

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
        if(!$this->canEdit) {
            return;
        }

        //$fileHandlerImport = FileHandlerCollection::getByType(FileHandlerCollection::TYPE_IMPORT);
        //$fileHandlerCreate = FileHandlerCollection::getByType(FileHandlerCollection::TYPE_CREATE);

        $deleteUrl = $this->contentContainer->createUrl('/task/item/delete', ['id' => $this->item->id]);
        $editUrl = $this->contentContainer->createUrl('/task/item/edit', ['id' => $this->item->id]);
        $shiftUrl = $this->contentContainer->createUrl('/task/item/shift', ['id' => $this->item->id]);
        //$uploadUrl = $this->contentContainer->createUrl('upload', ['openGalleryId' => $this->gallery->id]);

        return $this->render('taskItemMenuDropdown', [
                    'deleteUrl' => $deleteUrl,
                    'editUrl' => $editUrl,
                    'shiftUrl' => $shiftUrl,
                    'item' => $this->item
        ]);
    }

}
