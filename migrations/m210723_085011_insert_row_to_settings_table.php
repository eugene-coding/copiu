<?php

use yii\db\Migration;

/**
 * Class m210723_085011_insert_row_to_settings_table
 */
class m210723_085011_insert_row_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%settings}}', ['key', 'value', 'label', 'description', 'user_id', 'is_system'], [
            [
                'app_name',
                'Наименование',
                'Наименование приложения',
                'Наименование, которое будет отображаться в форме входа и в шапке системы',
                1,
                0
            ],
            ['phone_number', '+70000000000', 'Номер контактного телефона', 'Телефон на странице входа в систему', 1, 0],
            ['check_quantity_enabled', '0', 'Ограничение минимального заказа', 'Кол-во в накладной влияет на минимальный заказ', 1, 0],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210723_085011_insert_row_to_settings_table cannot be reverted.\n";

        return true;
    }
}
