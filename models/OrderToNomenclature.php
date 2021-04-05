<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_to_nomenclature".
 *
 * @property int $id
 * @property int|null $order_id Заказ
 * @property int|null $nomenclature_id Продукт
 * @property float|null $price Цена за единицу
 * @property float|null $count Количество
 * @property float|null $order_blank_id Идентификатор бланка заказа
 *
 * @property Nomenclature $nomenclature
 * @property Order $order
 * @property double $totalPrice
 */
class OrderToNomenclature extends ActiveRecord
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
            [
                ['nomenclature_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Nomenclature::className(),
                'targetAttribute' => ['nomenclature_id' => 'id']
            ],
            [
                ['order_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Order::class,
                'targetAttribute' => ['order_id' => 'id']
            ],
            [['order_id', 'nomenclature_id', 'order_blank_id'], 'unique', 'targetAttribute' => ['order_id', 'nomenclature_id','order_blank_id']],

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

    /**
     * Общая сумма заказа
     * @param int $order_id Заказ
     * @return bool|false|null|string
     */
    public static function getTotalPrice($order_id)
    {
        return self::find()
            ->select(['SUM(price*count)'])
            ->andWhere(['order_id' => $order_id])
            ->scalar();
    }
}
