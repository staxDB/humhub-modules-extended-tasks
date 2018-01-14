<?php

class uninstall extends humhub\components\Migration
{

    public function up()
    {

        $this->dropTable('task');
        $this->dropTable('task_item');
        $this->dropTable('task_item_moderator');
        $this->dropTable('task_participant');
    }

    public function down()
    {
        echo "m150629_144032_uninstall does not support migration down.\n";
        return false;
    }

}
