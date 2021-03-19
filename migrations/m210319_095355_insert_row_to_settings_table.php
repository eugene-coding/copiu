<?php

use yii\db\Migration;

/**
 * Class m210319_095355_insert_row_to_settings_table
 */
class m210319_095355_insert_row_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%settings}}',
            [
                'key' => 'revenue_debit_account',
                'value' => '56729828-f09b-d58e-04be-ed0f2e4e10e1',
                'label' => 'Дебетовый счет выручки',
                'user_id' => 1,
                'is_system' => 0]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210319_095355_insert_row_to_settings_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210319_095355_insert_row_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
