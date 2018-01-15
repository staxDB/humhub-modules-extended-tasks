<?php

class m180114_210903_initial extends humhub\components\Migration
{

    public function up()
    {
        $this->createTable('task', array(
            'id' => 'pk',
            'title' => 'varchar(255) NOT NULL',
            'deadline' => 'datetime DEFAULT NULL',
            'percent' => 'smallint(6) NOT NULL DEFAULT 0',
            'status' => 'tinyint(4) DEFAULT 0',
        ), '');

        $this->createTable('task_user', array(
            'id' => 'pk',
            'task_id' => 'int(11) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
            'name' => 'varchar(255) NULL',
        ), '');

        $this->createTable('task_item', array(
            'id' => 'pk',
            'task_id' => 'int(11) NOT NULL',
            'title' => 'VARCHAR(255) NOT NULL',
            'description' => 'TEXT NULL',
            'completed' => 'tinyint(4) DEFAULT 0',
            'notes' => 'TEXT NULL',
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
