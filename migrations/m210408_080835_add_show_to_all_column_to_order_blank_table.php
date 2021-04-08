<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order_blank}}`.
 */
class m210408_080835_add_show_to_all_column_to_order_blank_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_blank}}', 'show_to_all', $this->smallInteger()->defaultValue(1)
            ->comment('Показывать всем'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_blank}}', 'show_to_all');
    }
}
