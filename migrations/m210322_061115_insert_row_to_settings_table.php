<?php

use yii\db\Migration;

/**
 * Class m210322_061115_insert_row_to_settings_table
 */
class m210322_061115_insert_row_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%settings}}', [
            'key' => 'store_outer_id',
            'label' => 'Склад',
            'description' => 'Обязательное значение, т.к. накладная создается с проведением',
            'user_id' => 1,
            'is_system' => 0,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210322_061115_insert_row_to_settings_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210322_061115_insert_row_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
