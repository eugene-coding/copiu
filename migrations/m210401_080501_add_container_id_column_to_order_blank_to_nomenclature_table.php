<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order_blank_to_nomenclature}}`.
 */
class m210401_080501_add_container_id_column_to_order_blank_to_nomenclature_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_blank_to_nomenclature}}', 'container_id', $this->string(50)
            ->comment('Контейнер'));

        $this->addForeignKey(
            'fk-order_blank_to_nomenclature-container_id',
            '{{%order_blank_to_nomenclature}}',
            'container_id',
            '{{%container}}',
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
        $this->dropForeignKey('fk-order_blank_to_nomenclature-container_id', '{{%order_blank_to_nomenclature}}');
        $this->dropColumn('{{%order_blank_to_nomenclature}}', 'container_id');
    }
}
