<?php

use yii\helpers\Html;

echo Yii::t('TaskModule.activities', '{userName} completed task {task}.', [
        '{userName}' => Html::tag('strong', Html::encode($originator->displayName)),
        '{task}' => Html::tag('strong', Html::encode($this->context->getContentInfo($source, false))),
    ]);

?>