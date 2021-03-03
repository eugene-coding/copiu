<?php

use app\models\Settings;
use yii\db\Migration;

/**
 * Class m210224_075656_insert_values_to_settings_table
 */
class m210224_075656_insert_values_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert(Settings::tableName(), ['key', 'label', 'user_id', 'is_system'], [
                ['token', 'Токен доступа', 1, 1],
                ['token_date', 'Дата и время получения токена', 1, 1],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210224_075656_insert_values_to_settings_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210224_075656_insert_values_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
