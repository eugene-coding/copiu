<?php

use yii\db\Migration;

/**
 * Class m210326_083935_insert_row_to_settings_table
 */
class m210326_083935_insert_row_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%settings}}', ['key', 'label', 'user_id', 'is_system'], [
           ['get_prices_date', 'Дата получения цен для ценовых категорий', 1, 1],
           ['sync_price_date', 'Дата последней синхронизации цен для ценовых категорий', 1, 1],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210326_083935_insert_row_to_settings_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210326_083935_insert_row_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
