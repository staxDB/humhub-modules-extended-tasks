<?php

use yii\helpers\Html;

echo Yii::t('TaskModule.activities', '{userName} reset task {task} in space {spaceName}.', [
        '{userName}' => Html::tag('strong', Html::encode($originator->displayName)),
        '{task}' => Html::tag('strong', Html::encode($this->context->getContentInfo($source, false))),
        '{spaceName}' => Html::tag('strong', Html::encode($source->content->container->displayName))
    ]);

?>