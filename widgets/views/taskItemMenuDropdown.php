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


/* @var $editUrl string */
/* @var $shiftUrl string */
/* @var $deleteUrl string */
?>

<div class="task-item-dropdown-menu pull-right" style="display:none;">
    <ul class="nav nav-pills preferences">
        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                <i class="fa fa-cog"></i>
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu pull-right">
                <li>
                    <?= ModalButton::asLink(Yii::t('TaskModule.base', 'Edit'))->icon('fa-edit')->post($editUrl)->loader(false) ?>
                </li>
                <li>
                    <?= Button::none(Yii::t('base', 'Delete'))->action('ui.modal.post', $deleteUrl)->icon('fa-trash')
                        ->confirm(Yii::t('TaskModule.views_index_editItem', '<strong>Confirm</strong> entry deletion'),
                            Yii::t('TaskModule.views_index_editItem', 'Do you really want to delete this entry?'),
                            Yii::t('base', 'Delete'))->link(); ?>
                </li>
            </ul>
        </li>
    </ul>
</div>