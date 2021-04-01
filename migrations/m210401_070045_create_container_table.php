<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%container}}`.
 */
class m210401_070045_create_container_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%container}}', [
            'id' => $this->string(50)->unique()->comment('UIID контейнера'),
            'nomenclature_id' => $this->integer()->comment('Позиция номенклатуры'),
            'name' => $this->string()->comment('Наименование'),
            'count' => $this->string()->comment('Количество'),
            'weight' => $this->string()->comment('Вес'),
            'full_weight' => $this->string()->comment('Обший вес'),
            'deleted' => $this->string()->comment('Удалён'),
        ]);

        $this->addCommentOnTable('{{%container}}', 'Контенер для продукта');

        $this->addForeignKey(
            'fk-container-nomenclature_id',
            '{{%container}}',
            'nomenclature_id',
            '{{%nomenclature}}',
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
        $this->dropTable('{{%container}}');
    }
}
