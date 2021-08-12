<?php

use yii\db\Migration;

/**
 * Class m210812_055411_insert_comment_required_row_to_settings_table
 */
class m210812_055411_insert_comment_required_row_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%settings}}', ['key', 'value', 'label', 'description', 'user_id', 'is_system'], [
            [
                'comment_required',
                0,
                'Комментарий к заказу обязателен',
                'Если настройка "Да", то заказ не возможно будет совершить если поле комментария к заказу пустое',
                1,
                0
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210812_055411_insert_comment_required_row_to_settings_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210812_055411_insert_comment_required_row_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
