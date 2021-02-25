<?php

use yii\db\Migration;

/**
 * Class m210225_101635_insert_ikko_settings_to_settings_table
 */
class m210225_101635_insert_ikko_settings_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%settings}}', ['key', 'label', 'user_id', 'is_system'], [
            ['ikko_server_url', 'Адрес:порт сервера IKKO', 1, 1],
            ['ikko_server_login', 'IKKO логин', 1, 1],
            ['ikko_server_password', 'IKKO пароль', 1, 1],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210225_101635_insert_ikko_settings_to_settings_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210225_101635_insert_ikko_settings_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
