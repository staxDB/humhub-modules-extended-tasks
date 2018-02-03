<?php

class m180114_210903_initial extends humhub\components\Migration
{

    public function up()
    {
        $this->createTable('task', array(
            'id' => 'pk',
            'title' => 'varchar(255) NOT NULL',
            'description' => 'TEXT NULL',
            'start_datetime' => 'datetime NOT NULL',
            'end_datetime' => 'datetime DEFAULT NULL',
            'all_day' => 'tinyint(4) NOT NULL',
            'status' => 'tinyint(4) DEFAULT 0',
            'percent' => 'smallint(6) NOT NULL DEFAULT 0',
            'time_zone' => 'varchar(60) DEFAULT NULL',
            'parent_task_id' => 'int(11) DEFAULT NULL',
        ), '');

        $this->createTable('task_assigned', array(
            'id' => 'pk',
            'task_id' => 'int(11) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
        ), '');

        $this->createTable('task_responsible', array(
            'id' => 'pk',
            'task_id' => 'int(11) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
        ), '');

        $this->createTable('task_item', array(
            'id' => 'pk',
            'task_id' => 'int(11) NOT NULL',
            'title' => 'VARCHAR(255) NOT NULL',
            'description' => 'TEXT NULL',
            'completed' => 'tinyint(4) DEFAULT 0',
        ), '');

        $this->createTable('task_reminder', array(
            'id' => 'pk',
            'task_id' => 'int(11) NOT NULL',
            'remind_mode' => 'tinyint(4) DEFAULT 0',
        ), '');
    }

    public function down()
    {
        echo "m180114_210903_initial does not support migration down.\n";
        return false;
    }

    /*
      // Use safeUp/safeDown to do migration with transaction
      public function safeUp()
      {
      }

      public function safeDown()
      {
      }
     */
}
