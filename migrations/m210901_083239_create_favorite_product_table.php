<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%favorite_product}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%buyer}}`
 * - `{{%order_blank_to_nomenclature}}`
 */
class m210901_083239_create_favorite_product_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%favorite_product}}', [
            'id' => $this->primaryKey(),
            'buyer_id' => $this->integer()->comment('Покупатель'),
            'obtn_id' => $this->integer()->comment('Связь бланка с продуктом'),
            'count' => $this->double(3)->comment('Кол-во'),
            'status' => $this->smallInteger()->defaultValue(1)->comment('Статус. Активна/Не активна'),
            'note' => $this->text(),
        ]);

        $this->addCommentOnTable('{{%favorite_product}}', 'Избранные продукты');

        // creates index for column `buyer_id`
        $this->createIndex(
            '{{%idx-favorite_product-buyer_id}}',
            '{{%favorite_product}}',
            'buyer_id'
        );

        // add foreign key for table `{{%buyer}}`
        $this->addForeignKey(
            '{{%fk-favorite_product-buyer_id}}',
            '{{%favorite_product}}',
            'buyer_id',
            '{{%buyer}}',
            'id',
            'CASCADE'
        );

        // creates index for column `obtn_id`
        $this->createIndex(
            '{{%idx-favorite_product-obtn_id}}',
            '{{%favorite_product}}',
            'obtn_id'
        );

        // add foreign key for table `{{%order_blank_to_nomenclature}}`
        $this->addForeignKey(
            '{{%fk-favorite_product-obtn_id}}',
            '{{%favorite_product}}',
            'obtn_id',
            '{{%order_blank_to_nomenclature}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%buyer}}`
        $this->dropForeignKey(
            '{{%fk-favorite_product-buyer_id}}',
            '{{%favorite_product}}'
        );

        // drops index for column `buyer_id`
        $this->dropIndex(
            '{{%idx-favorite_product-buyer_id}}',
            '{{%favorite_product}}'
        );

        // drops foreign key for table `{{%order_blank_to_nomenclature}}`
        $this->dropForeignKey(
            '{{%fk-favorite_product-obtn_id}}',
            '{{%favorite_product}}'
        );

        // drops index for column `obtn_id`
        $this->dropIndex(
            '{{%idx-favorite_product-obtn_id}}',
            '{{%favorite_product}}'
        );

        $this->dropTable('{{%favorite_product}}');
    }
}
