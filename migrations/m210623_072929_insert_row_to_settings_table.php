<?php

use yii\db\Migration;

/**
 * Class m210623_072929_insert_row_to_settings_table
 */
class m210623_072929_insert_row_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%settings}}', [
            'key' => 'delivery_period',
            'value' => 2,
            'label' => 'Период доставки (в&nbsp;часах)',
            'description' => 'Минимальное время между "Доставка с" и "Доставка до", которое клиент может выставить при создании заказа',
            'user_id' => 1,
            'is_system' => 0
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210623_072929_insert_roe_to_settings_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210623_072929_insert_roe_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
