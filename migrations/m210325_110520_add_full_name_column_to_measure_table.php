<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%measure}}`.
 */
class m210325_110520_add_full_name_column_to_measure_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%measure}}', 'full_name', $this->string()->comment('Полное наименование'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%measure}}', 'full_name');
    }
}
