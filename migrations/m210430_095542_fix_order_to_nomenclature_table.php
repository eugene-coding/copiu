<?php

use yii\db\Migration;

/**
 * Class m210430_095542_fix_order_to_nomenclature_table
 */
class m210430_095542_fix_order_to_nomenclature_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-order_to_nomenclature-nomenclature_id', '{{%order_to_nomenclature}}');
        $this->dropColumn('{{%order_to_nomenclature}}', 'nomenclature_id');

        $this->dropForeignKey('fk-order_to_nomenclature-order_blank_id', '{{%order_to_nomenclature}}');
        $this->dropColumn('{{%order_to_nomenclature}}', 'order_blank_id');

        $this->addColumn('{{%order_to_nomenclature}}', 'obtn_id', $this->integer()
            ->comment('Позиция бланка заказа'));
        $this->addForeignKey(
            'fk-order_to_nomenclature-obtn_id',
            '{{%order_to_nomenclature}}',
            'obtn_id',
            '{{%order_blank_to_nomenclature}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%order_to_nomenclature}}', 'nomenclature_id', $this->integer()
            ->comment('Позиция номенклатуры'));

        $this->addForeignKey(
            'fk-order_to_nomenclature-nomenclature_id',
            '{{%order_to_nomenclature}}',
            'nomenclature_id',
            '{{%nomenclature}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addColumn('{{%order_to_nomenclature}}', 'order_blank_id', $this->integer()
            ->comment('Бланк заказа'));

        $this->addForeignKey(
            'fk-order_to_nomenclature-order_blank_id',
            '{{%order_to_nomenclature}}',
            'order_blank_id',
            '{{%order_blank}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->dropForeignKey('fk-order_to_nomenclature-obtn_id', '{{%order_to_nomenclature}}');
        $this->dropColumn('{{%order_to_nomenclature}}', 'obtn_id');

    }
}
