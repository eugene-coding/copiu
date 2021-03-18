<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order}}`.
 */
class m210318_113021_add_columns_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'invoice_number', $this->string()->comment('Номер накладной'));
        $this->addColumn('{{%order}}', 'delivery_act_number', $this->string()->comment('Номер акта оказанных услуг (доставка)'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'invoice_number');
        $this->dropColumn('{{%order}}', 'delivery_act_number');
    }
}
