<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%department}}`.
 */
class m210320_064835_create_department_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%department}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'outer_id' => $this->string()->comment('Внешний идентификатор'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%department}}');
    }
}
