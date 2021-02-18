<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%supplier}}`.
 */
class m210217_110330_create_supplier_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%supplier}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'artikul_service' => $this->string()->comment('Артикул услуги доставки'),
            'departament_id' => $this->string()->comment('Идентификатор департамента'),
            'revenue_account' => $this->string()->comment('ID счета для оказания услуг'),
            'pc_id' => $this->integer()->comment('Ценовая категория'),
            'user_id' => $this->integer()->comment('Пользователь'),
        ]);

        $this->addCommentOnTable('{{%supplier}}', 'Поставщики (Призводители)');

        $this->addForeignKey(
            'fk-supplier-pc_id',
            '{{%supplier}}',
            'pc_id',
            '{{%price_category}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-supplier-user_id',
            '{{%supplier}}',
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
        $this->dropTable('{{%supplier}}');
    }
}
