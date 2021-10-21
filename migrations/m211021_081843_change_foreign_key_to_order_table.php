<?php

use yii\db\Migration;

/**
 * Class m211021_081843_change_foreign_key_to_order_table
 */
class m211021_081843_change_foreign_key_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-order-delivery_address_id', '{{%order}}');

        // add foreign key for table `{{%buyer}}`
        $this->addForeignKey(
            '{{%fk-order-delivery_address_id}}',
            '{{%order}}',
            'delivery_address_id',
            '{{%buyer_address}}',
            'id',
            'RESTRICT',
            'RESTRICT'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211021_081843_change_foreign_key_to_order_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211021_081843_change_foreign_key_to_order_table cannot be reverted.\n";

        return false;
    }
    */
}
