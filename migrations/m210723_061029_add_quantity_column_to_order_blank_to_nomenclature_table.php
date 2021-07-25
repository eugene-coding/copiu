<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order_blank_to_nomenclature}}`.
 */
class m210723_061029_add_quantity_column_to_order_blank_to_nomenclature_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_blank_to_nomenclature}}', 'quantity', $this->double()->comment('Кол-во'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_blank_to_nomenclature}}', 'quantity');
    }
}
