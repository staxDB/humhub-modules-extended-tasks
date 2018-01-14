<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\components\Migration;
use yii\db\Expression;

class m180114_210940_item_streamChannel extends Migration
{
    public function up()
    {
        $this->updateSilent('content', ['stream_channel' => new Expression("NULL")], ['object_model' => \humhub\modules\task\models\TaskItem::class]);
    }

    public function down()
    {
        echo "m180114_210940_item_streamChannel cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
