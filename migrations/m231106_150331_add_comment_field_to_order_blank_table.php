<?php

use yii\db\Migration;

/**
 * Class m231106_150331_add_comment_field_to_order_blank_table
 */
class m231106_150331_add_comment_field_to_order_blank_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order_blank', 'comment', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231106_150331_add_comment_field_to_order_blank_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231106_150331_add_comment_field_to_order_blank_table cannot be reverted.\n";

        return false;
    }
    */
}
