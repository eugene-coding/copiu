<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order_to_nomenclature}}`.
 */
class m210402_130726_add_order_blank_id_column_to_order_to_nomenclature_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_to_nomenclature}}', 'order_blank_id', $this->integer()
            ->comment('Бланк заказа из которого получили продукт'));

        $this->addForeignKey(
            'fk-order_to_nomenclature-order_blank_id',
            '{{%order_to_nomenclature}}',
            'order_blank_id',
            '{{%order_blank}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-order_to_nomenclature-order_blank_id',
            '{{%order_to_nomenclature}}');
        $this->dropColumn('{{%order_to_nomenclature}}', 'order_blank_id');
    }
}
