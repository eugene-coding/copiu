<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%buyer}}`.
 */
class m210217_115125_create_buyer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%buyer}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'pc_id' => $this->integer()->comment('Ценовая категория'),
            'user_id' => $this->integer()->comment('Пользователь системы')
        ]);
        $this->addCommentOnTable('{{%buyer}}', 'Покупатели');

        $this->addForeignKey(
            'fk-buyer-pc_id',
            '{{%buyer}}',
            'pc_id',
            '{{%price_category}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-buyer-user_id',
            '{{%buyer}}',
            'user_id',
            '{{%users}}',
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
        $this->dropTable('{{%buyer}}');
    }
}
