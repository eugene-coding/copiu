<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%buyer}}`.
 */
class m210224_090905_add_min_balance_column_to_buyer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%buyer}}', 'min_balance', $this->double()->defaultValue(0)->comment('Минимальный Баланс'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%buyer}}', 'min_balance');
    }
}
