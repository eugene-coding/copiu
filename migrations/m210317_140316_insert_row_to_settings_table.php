<?php

use yii\db\Migration;

/**
 * Class m210317_140316_insert_row_to_settings_table
 */
class m210317_140316_insert_row_to_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%settings}}',
            ['key', 'label', 'description', 'user_id', 'is_system'],
            [
                [
                    'sync_nomenclature_next_chunk',
                    'Следующий чанк для синхронизации',
                    'Синхронизация делит файл номенклатуры на части.' .
                    'Каждый заход синхронизируется по 500 записей. Чанк указывает какую часть записей синхронизировать следующей',
                    1,
                    1
                ],
                [
                    'sync_nomenclature_sync_date',
                    'Дата и время синхронизации номенклатуры',
                    null,
                    1,
                    1
                ],
                [
                    'get_nomenclature_date',
                    'Дата и время получения номенклатуры',
                    null,
                    1,
                    1
                ],
            ]);
        $this->insert('{{%settings}}', [
            'key' => 'sync_nomenclature_next_chunk',
            'label' => 'Следующий чанк для синхронизации',
            'description' => 'Синхронизация делит файл номенклатуры на части.' .
                'Каждый заход синхронизируется по 500 записей. Чанк указывает какую часть записей синхронизировать следующей',
            'user_id' => 1,
            'is_system' => 1
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210317_140316_insert_row_to_settings_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210317_140316_insert_row_to_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
