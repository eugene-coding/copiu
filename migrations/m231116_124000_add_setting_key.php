<?php

use yii\db\Migration;

/**
 * Class m231116_124000_add_setting_key
 */
class m231116_124000_add_setting_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%settings}}', [
            'key' => 'price_list',
            'value' => '',
            'label' => 'Файл прайс-листа',
            'description' => 'Файл прайс-листа',
            'user_id' => 1,
            'is_system' => 1
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231116_124000_add_setting_key cannot be reverted.\n";

        return false;
    }

}
