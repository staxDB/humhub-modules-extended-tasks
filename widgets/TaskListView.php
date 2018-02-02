<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 01.07.2017
 * Time: 12:22
 */

namespace humhub\modules\task\widgets;


use humhub\components\Widget;
use humhub\modules\task\models\forms\TaskFilter;
use humhub\modules\task\models\Task;
use Yii;
use yii\data\ActiveDataProvider;
use yii\widgets\ListView;

class TaskListView extends Widget
{
    /**
     * @var TaskFilter
     */
    public $filter;

    public $canEdit;

    public function run()
    {
        $tasksProvider = new ActiveDataProvider([
            'query' => $this->filter->query(),
            'pagination' => [
                'pageSize' => 5,
                'route' => '/task/index/filter-tasks'
            ],
        ]);

        return  ListView::widget([
            'dataProvider' => $tasksProvider,
//            'itemView' => '@task/widgets/views/taskListEntry',
            'itemView' => '@task/widgets/views/_taskItem',
            'viewParams' => [
                'contentContainer' => $this->filter->contentContainer,
                'canEdit' => $this->canEdit
            ],
            'options' => [
                'tag' => 'ul',
                'class' => 'media-list'
            ],
            'itemOptions' => [
                'tag' => 'li'
            ],
            'layout' => "{summary}\n{items}\n<div class=\"pagination-container\">{pager}</div>"
        ]);
    }

}