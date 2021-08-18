<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%buyer_address}}`
 */
class m210818_150259_add_delivery_address_id_column_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'delivery_address_id', $this->integer());

        // creates index for column `delivery_address_id`
        $this->createIndex(
            '{{%idx-order-delivery_address_id}}',
            '{{%order}}',
            'delivery_address_id'
        );

        // add foreign key for table `{{%buyer_address}}`
        $this->addForeignKey(
            '{{%fk-order-delivery_address_id}}',
            '{{%order}}',
            'delivery_address_id',
            '{{%buyer_address}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%buyer_address}}`
        $this->dropForeignKey(
            '{{%fk-order-delivery_address_id}}',
            '{{%order}}'
        );

        // drops index for column `delivery_address_id`
        $this->dropIndex(
            '{{%idx-order-delivery_address_id}}',
            '{{%order}}'
        );

        $this->dropColumn('{{%order}}', 'delivery_address_id');
    }
}
