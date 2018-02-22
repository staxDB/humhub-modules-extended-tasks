<?php

class m180114_210903_initial extends humhub\components\Migration
{

    public function up()
    {
        $this->createTable('task', array(
            'id' => 'pk',
            'title' => 'varchar(255) NOT NULL',
            'color' => 'varchar(7)',
            'description' => 'TEXT NULL',
            'review' => 'tinyint(4) NOT NULL',
            'scheduling' => 'tinyint(4) NOT NULL',
            'all_day' => 'tinyint(4) NOT NULL',
            'start_datetime' => 'datetime DEFAULT NULL',
            'end_datetime' => 'datetime DEFAULT NULL',
            'status' => 'tinyint(4) NOT NULL DEFAULT 0',
            'cal_mode' => 'tinyint(4) NOT NULL DEFAULT 0',
            'time_zone' => 'varchar(60) DEFAULT NULL',
            'parent_task_id' => 'int(11) DEFAULT NULL',
            'request_sent' => 'tinyint(4) DEFAULT 0',
        ), '');

        $this->createTable('task_user', array(
            'id' => 'pk',
            'task_id' => 'int(11) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
            'user_type' => 'tinyint(4) NOT NULL',
        ), '');

        $this->createTable('task_item', array(
            'id' => 'pk',
            'task_id' => 'int(11) NOT NULL',
            'title' => 'VARCHAR(255) NOT NULL',
            'description' => 'TEXT NULL',
            'completed' => 'tinyint(4) DEFAULT 0',
            'sort_order' => 'int(11) NOT NULL DEFAULT 1',

    ), '');

        $this->createTable('task_reminder', array(
            'id' => 'pk',
            'task_id' => 'int(11) NOT NULL',
            'remind_mode' => 'tinyint(4) DEFAULT 0',
            'start_reminder_sent' => 'tinyint(4) NOT NULL DEFAULT 0',
            'end_reminder_sent' => 'tinyint(4) NOT NULL DEFAULT 0'
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
