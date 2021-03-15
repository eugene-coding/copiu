<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "order_to_nomenclature".
 *
 * @property int $id
 * @property int|null $order_id Заказ
 * @property int|null $nomenclature_id Продукт
 * @property float|null $price Цена за единицу
 * @property float|null $count Количество
 *
 * @property Nomenclature $nomenclature
 * @property Order $order
 */
class OrderToNomenclature extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_to_nomenclature';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'nomenclature_id'], 'integer'],
            [['price', 'count'], 'number'],
            [['nomenclature_id'], 'exist', 'skipOnError' => true, 'targetClass' => Nomenclature::className(), 'targetAttribute' => ['nomenclature_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Заказ',
            'nomenclature_id' => 'Продукт',
            'price' => 'Цена за единицу',
            'count' => 'Количество',
        ];
    }

    /**
     * Gets query for [[Nomenclature]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNomenclature()
    {
        return $this->hasOne(Nomenclature::className(), ['id' => 'nomenclature_id']);
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }
}
