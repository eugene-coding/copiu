<?php

use yii\db\Migration;

/**
 * Class m240424_051614_update_longtext
 */
class m240424_051614_update_longtext extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%order_logging}}', 'description', $this->getDb()->getSchema()->createColumnSchemaBuilder('longtext'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240424_051614_update_longtext cannot be reverted.\n";
        $this->alterColumn('{{%order_logging}}', 'description', $this->text());
    }
}
