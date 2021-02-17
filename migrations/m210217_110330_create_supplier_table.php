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
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%supplier}}');
    }
}
