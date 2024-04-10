<?php

use yii\db\Migration;

/**
 * Class m231126_165605_add_setting_key
 */
class m231126_165605_add_setting_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%settings}}', [
            'key' => 'delivery_message',
            'value' => '',
            'label' => 'Текст уведомления для доставки',
            'description' => 'Текст уведомления для доставки',
            'user_id' => 1,
            'is_system' => 0
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231126_165605_add_setting_key cannot be reverted.\n";

        return false;
    }
}
