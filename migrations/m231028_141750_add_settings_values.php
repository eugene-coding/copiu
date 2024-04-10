<?php

use yii\db\Migration;

/**
 * Class m231028_141750_add_settings_values
 */
class m231028_141750_add_settings_values extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%settings}}', [
            'key' => 'delivery_min_sum',
            'value' => 3000,
            'label' => 'Минимальная сумма заказа',
            'description' => 'Минимальная сумма заказа',
            'user_id' => 1,
            'is_system' => 0
        ]);

        $this->insert('{{%settings}}', [
            'key' => 'delivery_disabled_days',
            'value' => '',
            'label' => 'Блокировка доставки',
            'description' => 'Блокировка доставки по дням недели',
            'user_id' => 1,
            'is_system' => 0
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231028_141750_add_settings_values cannot be reverted.\n";

        return false;
    }
}
