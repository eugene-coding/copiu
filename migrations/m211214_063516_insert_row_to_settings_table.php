<?php

use yii\db\Migration;

/**
 * Class m211214_063516_insert_row_to_settings_table
 */
class m211214_063516_insert_row_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%settings}}', [
            'key' => 'delivery_nds',
            'value' => 20,
            'label' => 'НДС (%)',
            'description' => 'Налог на добавленную стоимость',
            'user_id' => 1,
            'is_system' => 0
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211214_063516_insert_row_to_settings_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211214_063516_insert_row_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
