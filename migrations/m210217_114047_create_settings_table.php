<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%settings}}`.
 */
class m210217_114047_create_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%settings}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string(),
            'value' => $this->string(),
            'label' => $this->string(),
            'description' => $this->text(),
            'user_id' => $this->integer(),
            'is_system' => $this->smallInteger()->defaultValue(0)->comment('Системная настройка')
        ]);

        $this->addForeignKey(
            'fk-settings-user_id',
            '{{%settings}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%settings}}');
    }
}
