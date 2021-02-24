<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%buyer}}`.
 */
class m210224_085740_add_balance_column_to_buyer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%buyer}}', 'balance', $this->double()->defaultValue(0)->comment('Баланс'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%buyer}}', 'balance');
    }
}
