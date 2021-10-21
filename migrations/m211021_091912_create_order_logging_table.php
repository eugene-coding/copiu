<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_logging}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%order}}`
 */
class m211021_091912_create_order_logging_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order_logging}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
            'user_id' => $this->integer(),
            'order_id' => $this->integer(),
            'action_type' => $this->integer()->comment('Тип действия'),
            'model' => $this->text()->comment('Поля модели'),
            'description' => $this->text()->comment('Описание'),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-order_logging-user_id}}',
            '{{%order_logging}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-order_logging-user_id}}',
            '{{%order_logging}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        // creates index for column `order_id`
        $this->createIndex(
            '{{%idx-order_logging-order_id}}',
            '{{%order_logging}}',
            'order_id'
        );

        // add foreign key for table `{{%order}}`
        $this->addForeignKey(
            '{{%fk-order_logging-order_id}}',
            '{{%order_logging}}',
            'order_id',
            '{{%order}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-order_logging-user_id}}',
            '{{%order_logging}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-order_logging-user_id}}',
            '{{%order_logging}}'
        );

        // drops foreign key for table `{{%order}}`
        $this->dropForeignKey(
            '{{%fk-order_logging-order_id}}',
            '{{%order_logging}}'
        );

        // drops index for column `order_id`
        $this->dropIndex(
            '{{%idx-order_logging-order_id}}',
            '{{%order_logging}}'
        );

        $this->dropTable('{{%order_logging}}');
    }
}
