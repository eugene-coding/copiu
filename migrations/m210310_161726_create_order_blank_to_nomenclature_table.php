<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_blank_to_nomenclature}}`.
 */
class m210310_161726_create_order_blank_to_nomenclature_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order_blank_to_nomenclature}}', [
            'id' => $this->primaryKey(),
            'ob_id' => $this->integer()->comment('Бланк заказа'),
            'n_id' => $this->integer()->comment('Продукт'),
        ]);

        $this->addForeignKey(
            'fk-order_blank_to_nomenclature-ob_id',
            '{{%order_blank_to_nomenclature}}',
            'ob_id',
            '{{%order_blank}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-order_blank_to_nomenclature-n_id',
            '{{%order_blank_to_nomenclature}}',
            'n_id',
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
        $this->dropTable('{{%order_blank_to_nomenclature}}');
    }
}
