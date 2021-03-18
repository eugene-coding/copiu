<?php

use yii\db\Migration;

/**
 * Class m210318_065941_add_rows_to_settings_table
 */
class m210318_065941_add_rows_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%settings}}', ['key', 'value', 'label', 'description', 'user_id', 'is_system'],[
            ['delivery_min_time', '03:00', 'Доставка с', 'Минимальное время, с которого может осуществлятся доставка', 1, 0],
            ['delivery_max_time', '17:00', 'Доставка до', 'Максимальное время, до которого может осуществлятся доставка', 1, 0],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210318_065941_add_rows_to_settings_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210318_065941_add_rows_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
