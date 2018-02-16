<?php

namespace humhub\modules\task\controllers;

use Yii;
use yii\web\Controller;


/**
 * todo.
 * Search Controller provides action for searching tasks.
 *
 * @author davidborn
 */
class SearchController extends Controller
{

    /**
     * @inheritdoc
     */
//    public function behaviors()
//    {
//        return [
//            'acl' => [
//                'class' => \humhub\components\behaviors\AccessControl::className(),
//            ]
//        ];
//    }

    /**
     * JSON Search for Users
     *
     * Returns an array of users with fields:
     *  - guid
     *  - displayName
     *  - image
     *  - profile link
     */
    public function actionJson()
    {
        Yii::$app->response->format = 'json';
        
        return \humhub\modules\task\widgets\TaskPicker::filter([
            'keyword' => Yii::$app->request->get('keyword'),
//            'fillUser' => true,
//            'disableFillUser' => false
        ]);
    }

}

?>
