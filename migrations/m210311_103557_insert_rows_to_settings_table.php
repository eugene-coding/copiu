<?php

use yii\db\Migration;

/**
 * Class m210311_103557_insert_rows_to_settings_table
 */
class m210311_103557_insert_rows_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%settings}}', ['key', 'label', 'description', 'user_id', 'is_system'], [
            ['delivery_article', 'Артикул услуги доставки', null, 1, 0],
            ['department_outer_id', 'Идентификатор отдела', 'Для запросов', 1, 0],
            ['invoice_outer_id', 'Идентификатор счета', 'Для акта услуг', 1, 0],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210311_103557_insert_rows_to_settings_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210311_103557_insert_rows_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
