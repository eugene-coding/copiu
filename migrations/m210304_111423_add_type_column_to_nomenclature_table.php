<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%nomenclature}}`.
 */
class m210304_111423_add_type_column_to_nomenclature_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%nomenclature}}', 'type', $this->string()
            ->comment('Тип элемента номенклатуры.'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%nomenclature}}', 'type');
    }
}
