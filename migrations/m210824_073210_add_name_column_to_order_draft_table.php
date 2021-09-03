<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order_draft}}`.
 */
class m210824_073210_add_name_column_to_order_draft_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_draft}}', 'name', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_draft}}', 'name');
    }
}
