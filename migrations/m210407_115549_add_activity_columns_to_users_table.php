<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%users}}`.
 */
class m210407_115549_add_activity_columns_to_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%users}}', 'last_activity', $this->timestamp()->defaultValue(null)
            ->comment('Последняя активность'));
        $this->addColumn('{{%users}}', 'is_active', $this->smallInteger()->defaultValue(0)
            ->comment('Активен или нет пользователь'));
        $this->addColumn('{{%users}}', 'activity_ip', $this->string()->defaultValue(null)
            ->comment('IP активного пользователя'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%users}}', 'last_activity');
        $this->dropColumn('{{%users}}', 'is_active');
        $this->dropColumn('{{%users}}', 'activity_ip');
    }
}
