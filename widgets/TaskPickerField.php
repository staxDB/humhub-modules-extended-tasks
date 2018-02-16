<?php

namespace humhub\modules\task\widgets;

use Yii;
use \yii\helpers\Url;
use humhub\widgets\BasePickerField;

/**
 *
 * @author davidborn
 */
class TaskPickerField extends BasePickerField
{

    /**
     * @inheritdoc 
     */
    public $defaultRoute = '/task/search/json';
    
    /**
     * @inheritdoc 
     */
//    public $jsWidget = 'task.picker.TaskModule';

    /**
     * @inheritdoc 
     */
    public function init() {
        $this->itemClass = \humhub\modules\task\models\Task::className();
        $this->itemKey = 'id';
    }
    
    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        if (!$this->url) {
            // provide the space id if the widget is calling from a space
            if (Yii::$app->controller->id == 'space') {
                return Url::to([$this->defaultRoute, 'space_id' => Yii::$app->controller->getSpace()->id]);
            } else {
                return Url::to([$this->defaultRoute]);
            }
        }

        return parent::getUrl();
    }

    /**
     * @inheritdoc 
     */
    protected function getData()
    {
        $result = parent::getData();
        $allowMultiple = $this->maxSelection !== 1;
        $result['placeholder'] = ($this->placeholder != null) ? $this->placeholder : Yii::t('TaskModule.widgets_TaskPickerField', 'Select {n,plural,=1{task} other{tasks}}', ['n' => ($allowMultiple) ? 2 : 1]);
        
        if($this->placeholder && !$this->placeholderMore) {
            $result['placeholder-more'] = $this->placeholder;
        } else {
            $result['placeholder-more'] = ($this->placeholderMore) ? $this->placeholderMore : Yii::t('TaskModule.widgets_TaskPickerField', 'Add more...');
        }
        
        $result['no-result'] = Yii::t('TaskModule.widgets_TaskPickerField', 'No tasks found for the given query.');

        if ($this->maxSelection) {
            $result['maximum-selected'] = Yii::t('TaskModule.widgets_TaskPickerField', 'This field only allows a maximum of {n,plural,=1{# task} other{# tasks}}.', ['n' => $this->maxSelection]);
        }
        return $result;
    }

    /**
     * @inheritdoc 
     */
    protected function getItemText($item)
    {
        return \yii\helpers\Html::encode($item->title);
    }

    /**
     * @inheritdoc
     */
    protected function getItemImage($item)
    {
        return null;
//        return $item->getProfileImage()->getUrl();
    }
}
