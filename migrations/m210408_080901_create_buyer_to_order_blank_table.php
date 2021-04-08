<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%buyer_to_order_blank}}`.
 */
class m210408_080901_create_buyer_to_order_blank_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%buyer_to_order_blank}}', [
            'id' => $this->primaryKey(),
            'buyer_id' => $this->integer(),
            'order_blank_id' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-buyer_to_order_blank-buyer_id',
            '{{%buyer_to_order_blank}}',
            'buyer_id',
            '{{%buyer}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-buyer_to_order_blank-order_blank_id',
            '{{%buyer_to_order_blank}}',
            'order_blank_id',
            '{{%order_blank}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addCommentOnTable('{{%buyer_to_order_blank}}', 'Видимость бланков для заказчиков');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%buyer_to_order_blank}}');
    }
}
