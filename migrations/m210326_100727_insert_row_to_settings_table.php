<?php

use yii\db\Migration;

/**
 * Class m210326_100727_insert_row_to_settings_table
 */
class m210326_100727_insert_row_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%settings}}', ['key', 'label', 'user_id', 'is_system'], [
            ['sync_price_next_chunk', 'Следующая партия для синхронизации цен для ценовых категорий', 1, 1],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210326_100727_insert_row_to_settings_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210326_100727_insert_row_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
