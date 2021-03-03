<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%measure}}`.
 */
class m210303_105228_create_measure_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%measure}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->comment('Наименование'),
            'outer_id' => $this->string()->comment('Внешний идентификатор'),
        ]);

        $this->addCommentOnTable('{{%measure}}', 'Меры продуктов');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%measure}}');
    }
}
