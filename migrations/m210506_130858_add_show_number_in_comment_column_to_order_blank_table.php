<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order_blank}}`.
 */
class m210506_130858_add_show_number_in_comment_column_to_order_blank_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_blank}}', 'show_number_in_comment', $this->smallInteger()
            ->defaultValue(0)->comment('Показывать номер бланка в комментарии'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_blank}}', 'show_number_in_comment');
    }
}
