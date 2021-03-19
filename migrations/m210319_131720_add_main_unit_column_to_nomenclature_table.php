<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%nomenclature}}`.
 */
class m210319_131720_add_main_unit_column_to_nomenclature_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%nomenclature}}','main_unit', $this->string()->comment('mainUnit для акта услуг'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%nomenclature}}','main_unit');
    }
}
