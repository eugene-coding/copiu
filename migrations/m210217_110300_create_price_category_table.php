<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%price_category}}`.
 */
class m210217_110300_create_price_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%price_category}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(),
        ]);
        $this->addCommentOnTable('{{%price_category}}', 'Ценовая категория');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%price_category}}');
    }
}
