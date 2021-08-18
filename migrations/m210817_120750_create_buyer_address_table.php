<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%buyer_address}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%buyer}}`
 */
class m210817_120750_create_buyer_address_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%buyer_address}}', [
            'id' => $this->primaryKey(),
            'buyer_id' => $this->integer(),
            'address' => $this->text(),
        ]);

        // creates index for column `buyer_id`
        $this->createIndex(
            '{{%idx-buyer_address-buyer_id}}',
            '{{%buyer_address}}',
            'buyer_id'
        );

        // add foreign key for table `{{%buyer}}`
        $this->addForeignKey(
            '{{%fk-buyer_address-buyer_id}}',
            '{{%buyer_address}}',
            'buyer_id',
            '{{%buyer}}',
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
        // drops foreign key for table `{{%buyer}}`
        $this->dropForeignKey(
            '{{%fk-buyer_address-buyer_id}}',
            '{{%buyer_address}}'
        );

        // drops index for column `buyer_id`
        $this->dropIndex(
            '{{%idx-buyer_address-buyer_id}}',
            '{{%buyer_address}}'
        );

        $this->dropTable('{{%buyer_address}}');
    }
}
