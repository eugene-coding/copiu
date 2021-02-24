<?php

use yii\db\Migration;

/**
 * Class m210224_103748_add_open_pass_column_to_users_tale
 */
class m210224_103748_add_open_pass_column_to_users_tale extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%users}}', 'open_pass', $this->string()->comment('Пароль'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%users}}', 'open_pass');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210224_103748_add_open_pass_column_to_user_tale cannot be reverted.\n";

        return false;
    }
    */
}
