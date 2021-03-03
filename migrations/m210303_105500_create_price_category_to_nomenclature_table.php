<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%price_category_to_nomenclature}}`.
 */
class m210303_105500_create_price_category_to_nomenclature_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%price_category_to_nomenclature}}', [
            'id' => $this->primaryKey(),
            'pc_id' => $this->integer()->comment('Ценовая категория'),
            'n_id' => $this->integer()->comment('Продукт (позиция номенклатуры)'),
            'price' => $this->double()->comment('Цена продукта для ценовой группы'),
        ]);

        $this->addCommentOnTable('{{%price_category_to_nomenclature}}', 'Связь продукта с ценовой категорией');

        $this->addForeignKey(
            'fk-price_category_to_nomenclature-pc_id',
            '{{%price_category_to_nomenclature}}',
            'pc_id',
            '{{%price_category}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-price_category_to_nomenclature-n_id',
            '{{%price_category_to_nomenclature}}',
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
        $this->dropTable('{{%price_category_to_nomenclature}}');
    }
}
