<?php

namespace humhub\modules\task\widgets;

use humhub\components\Widget;

/**
 * TaskItemWidget is used to display a task item.
 *
 * This Widget will used by the Task Model in Method getWallOut().
 *
 * @author davidborn
 */
class AddItemsInput extends Widget
{
    
    public $name;

    public function run()
    {
        return $this->render('addItemsInput', ['name' => $this->name]);
    }

}

?>