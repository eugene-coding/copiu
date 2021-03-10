<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_blank}}`.
 */
class m210310_133239_create_order_blank_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order_blank}}', [
            'id' => $this->primaryKey(),
            'number' => $this->string()->comment('Номер накладной'),
            'date' => $this->date()->comment('Дата накладной'),
            'time_limit' => $this->integer()->comment('Ограничение по времени'),
            'day_limit' => $this->integer()->comment('Ограничение по дням'),
            'synced_at' => $this->timestamp()->defaultValue(null)->comment('Дата и время синхронизации'),
        ]);

        $this->addCommentOnTable('{{%order_blank}}', 'Бланки заказов');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%order_blank}}');
    }
}
