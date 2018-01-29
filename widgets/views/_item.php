<?php

use yii\helpers\Html;

?>

<div class="row">
    <?php if (true) : ?>
    <div class="col-md-1" style="padding-right: 0;">
        <div class="checkbox">
            <label>
                <?= Html::checkBox('item[' . $item->id . ']'); ?>
            </label>
        </div>
    </div>
    <?php endif; ?>
</div>
<div class="clearFloats"></div>