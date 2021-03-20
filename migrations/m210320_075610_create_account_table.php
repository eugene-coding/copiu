<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%account}}`.
 */
class m210320_075610_create_account_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%account}}', [
            'id' => $this->primaryKey(),
            'outer_id' => $this->string(),
            'name' => $this->string(),
            'type' => $this->string(),
            'description' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%account}}');
    }
}
