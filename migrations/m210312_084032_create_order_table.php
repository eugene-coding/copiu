<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order}}`.
 */
class m210312_084032_create_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'buyer_id' => $this->integer()->comment('Покупатель'),
            'created_at' => $this->timestamp()->defaultExpression('NOW()')->comment('Дата создания'),
            'target_date' => $this->date()->comment('Дата на которую формируется заказ'),
            'delivery_time_from' => $this->time()->comment('Время доставки "от"'),
            'delivery_time_to' => $this->time()->comment('Время доставки "до"'),
            'total_price' => $this->double()->comment('Общая сумма заказа (включая доставку)'),
            'comment' => $this->text()->comment('Комментарий'),
            'status' => $this->smallInteger()->comment('Статус заказа'),
        ]);

        $this->addForeignKey(
            'fk-order-buyer_id',
            '{{%order}}',
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
        $this->dropTable('{{%order}}');
    }
}
