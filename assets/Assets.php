<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\task\assets;

use yii\web\AssetBundle;

class Assets extends AssetBundle
{

    public $publishOptions = [
        'forceCopy' => false
    ];

    public $sourcePath = '@task/resources';

    public $css = [
        'css/task.css',
    ];

    // We have to use the timeentry lib for the duration since the TimePicker widget uses an older version without maxHour setting...
    public $js = [
        'js/timeentry/jquery.plugin.min.js',
        'js/timeentry/jquery.timeentry.min.js',
        'js/humhub.task.js'
    ];
}
