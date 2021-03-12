<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%buyer}}`.
 */
class m210224_091702_add_delivery_cost_column_to_buyer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%buyer}}', 'delivery_cost', $this->double()
            ->defaultValue(0)->comment('Сумма услуги доставки если сумма заказа меньше минимальной'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%buyer}}', 'delivery_cost');
    }
}
