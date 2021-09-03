<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_draft}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%order}}`
 */
class m210823_102910_create_order_draft_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order_draft}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer(),
            'plan_send_date' => $this->date(),
            'send_at' => $this->timestamp()->defaultValue(null),
        ]);

        // creates index for column `order_id`
        $this->createIndex(
            '{{%idx-order_draft-order_id}}',
            '{{%order_draft}}',
            'order_id'
        );

        // add foreign key for table `{{%order}}`
        $this->addForeignKey(
            '{{%fk-order_draft-order_id}}',
            '{{%order_draft}}',
            'order_id',
            '{{%order}}',
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
        // drops foreign key for table `{{%order}}`
        $this->dropForeignKey(
            '{{%fk-order_draft-order_id}}',
            '{{%order_draft}}'
        );

        // drops index for column `order_id`
        $this->dropIndex(
            '{{%idx-order_draft-order_id}}',
            '{{%order_draft}}'
        );

        $this->dropTable('{{%order_draft}}');
    }
}
