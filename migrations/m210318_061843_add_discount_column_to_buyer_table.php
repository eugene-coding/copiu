<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%buyer}}`.
 */
class m210318_061843_add_discount_column_to_buyer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%buyer}}', 'discount', $this->double()->defaultValue(0)->comment('Скидка от ЦК'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%buyer}}', 'discount');
    }
}
