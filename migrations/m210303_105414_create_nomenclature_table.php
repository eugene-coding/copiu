<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%nomenclature}}`.
 */
class m210303_105414_create_nomenclature_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%nomenclature}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'description' => $this->text(),
            'outer_id' => $this->string(),
            'num' => $this->string()->comment('Артикул'),
            'n_group_id' => $this->integer()->comment('Номенклатурная группа'),
            'measure_id' => $this->integer()->comment('Единица измерения'),
            'default_price' => $this->double()->comment('Цена по умолчанию'),
            'unit_weight' => $this->string()->comment('Вес одной единицы'),
            'unit_capacity' => $this->string()->comment('Объём одной единицы'),
        ]);

        $this->addForeignKey(
            'fk-nomenclature-n_group_id',
            '{{%nomenclature}}',
            'n_group_id',
            '{{%n_group}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-nomenclature-measure_id',
            '{{%nomenclature}}',
            'measure_id',
            '{{%measure}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%nomenclature}}');
    }
}
