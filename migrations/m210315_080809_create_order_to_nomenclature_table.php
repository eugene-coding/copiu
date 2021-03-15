<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_to_nomenclature}}`.
 */
class m210315_080809_create_order_to_nomenclature_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order_to_nomenclature}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->comment('Заказ'),
            'nomenclature_id' => $this->integer()->comment('Продукт'),
            'price' => $this->double()->comment('Цена за единицу'),
            'count' => $this->double()->comment('Количество'),
        ]);

        $this->addForeignKey(
            'fk-order_to_nomenclature-order_id',
            '{{%order_to_nomenclature}}',
            'order_id',
            '{{%order}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-order_to_nomenclature-nomenclature_id',
            '{{%order_to_nomenclature}}',
            'nomenclature_id',
            '{{%nomenclature}}',
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
        $this->dropTable('{{%order_to_nomenclature}}');
    }
}
