<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
use humhub\modules\task\widgets\TaskListEntry;
?>
<?= TaskListEntry::widget(['task' => $model, 'contentContainer' => $contentContainer, 'canEdit' => $canEdit])?>
