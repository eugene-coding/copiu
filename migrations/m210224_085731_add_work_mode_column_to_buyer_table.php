<?php

use app\models\Buyer;
use yii\db\Migration;

/**
 * Handles adding columns to table `{{%buyer}}`.
 */
class m210224_085731_add_work_mode_column_to_buyer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%buyer}}', 'work_mode', $this->smallInteger()
            ->defaultValue(Buyer::WORK_MODE_BALANCE_LIMIT)->comment('Режим работы'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%buyer}}', 'work_mode');
    }
}
