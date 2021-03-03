<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%n_group}}`.
 */
class m210303_105316_create_n_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%n_group}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->comment('Наименование'),
            'outer_id' => $this->string()->comment('Внешний идентификатор'),
            'description' => $this->text()->comment('Описание'),
            'num' => $this->string()->comment('Артикул'),
            'code' => $this->string()->comment('Код'),
            'parent_id' => $this->integer()->comment('Родительская группа'),
        ]);

        $this->addCommentOnTable('{{%n_group}}', 'Номенклатурные группы');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%n_group}}');
    }
}
