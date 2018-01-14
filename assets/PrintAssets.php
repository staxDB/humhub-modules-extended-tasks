<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\task\assets;

use yii\web\AssetBundle;

class PrintAssets extends AssetBundle
{

    public $sourcePath = '@task/resources';

    public $css = [
        'css/task_print.css',
    ];

    public $js = [
    ];
}
