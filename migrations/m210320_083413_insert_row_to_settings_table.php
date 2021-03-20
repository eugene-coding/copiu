<?php

use yii\db\Migration;

/**
 * Class m210320_083413_insert_row_to_settings_table
 */
class m210320_083413_insert_row_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%settings}}', ['key' => 'entities_version', 'label' => 'Entities версия', 'is_system' => 1, 'user_id' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210320_083413_insert_row_to_settings_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210320_083413_insert_row_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
