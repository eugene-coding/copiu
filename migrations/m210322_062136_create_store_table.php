<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%store}}`.
 */
class m210322_062136_create_store_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%store}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'outer_id' => $this->string(),
            'department_outer_id' => $this->string(),
            'description' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%store}}');
    }
}
