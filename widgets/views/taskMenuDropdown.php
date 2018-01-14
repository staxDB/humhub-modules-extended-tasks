<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
?>

<?php

use humhub\widgets\Button;
use humhub\widgets\Link;
use humhub\widgets\ModalButton;
?>

<div class="pull-right">
    <ul class="nav nav-pills preferences">
        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                <i class="fa fa-cog"></i>
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu pull-right">

                <li>
                    <?= ModalButton::asLink(Yii::t('TaskModule.base', 'Edit'))->post($editUrl)->icon('fa-edit')->visible($canEdit) ?>
                </li>
                <li>
                    <?= ModalButton::asLink( Yii::t('TaskModule.views_index_index', 'Duplicate'))->post($duplicateUrl)->icon('fa-clone')->visible($canEdit) ?>
                </li>
                <li>
                    <?= Button::asLink(Yii::t('TaskModule.views_index_index', 'Print agenda'), $printUrl)->icon('fa-print')->options(['target' => '_blank']);?>
                </li>
                <li>
                    <?= ModalButton::asLink(Yii::t('TaskModule.views_index_index', 'Share task'))->load($shareUrl)->icon('fa-share-alt')?>
                </li>
                <li>
                    <?= Link::asLink(Yii::t('TaskModule.base', 'Delete'))->action('ui.modal.post', $deleteUrl)->icon('fa-trash')
                        ->confirm(Yii::t('TaskModule.views_index_edit', '<strong>Confirm</strong> task deletion'),
                            Yii::t('TaskModule.views_index_edit', 'Do you really want to delete this task?'),
                            Yii::t('TaskModule.base', 'Delete'))->visible($canEdit); ?>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</div>