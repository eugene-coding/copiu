<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order_to_nomenclature}}`.
 */
class m211202_060156_add_columns_to_order_to_nomenclature_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_to_nomenclature}}', 'created_at', $this->timestamp()
            ->defaultExpression('NOW()'));
        $this->addColumn('{{%order_to_nomenclature}}', 'updated_at', $this->timestamp()
            ->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_to_nomenclature}}', 'created_at');
        $this->dropColumn('{{%order_to_nomenclature}}', 'updated_at');
    }
}
