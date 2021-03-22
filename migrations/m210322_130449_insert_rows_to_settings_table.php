<?php

use yii\db\Migration;

/**
 * Class m210322_130449_insert_rows_to_settings_table
 */
class m210322_130449_insert_rows_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%settings}}', ['key', 'label', 'user_id', 'is_system'], [
           ['delivery_eid', 'GUID доставки', 1, 1],
           ['delivery_main_unit', 'mainUnit доставки', 1, 1],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210322_130449_insert_rows_to_settings_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210322_130449_insert_rows_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
