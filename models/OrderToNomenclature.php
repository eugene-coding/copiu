<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_to_nomenclature".
 *
 * @property int $id
 * @property int|null $order_id Заказ
 * @property float|null $price Цена за единицу
 * @property float|null $count Количество
 * @property int|null $obtn_id Позиция в бланке заказа
 *
 * @property OrderBlankToNomenclature $obtn
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
            [['order_id', 'obtn_id'], 'integer'],
            [['price', 'count'], 'number'],
            [
                ['obtn_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => OrderBlankToNomenclature::class,
                'targetAttribute' => ['obtn_id' => 'id']
            ],
            [
                ['order_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Order::class,
                'targetAttribute' => ['order_id' => 'id']
            ],
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
            'obtn_id' => 'Продукт в бланке',
            'price' => 'Цена за единицу',
            'count' => 'Количество',
        ];
    }

    /**
     * Gets query for [[OrderBlankToNomenclature]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getObtn()
    {
        return $this->hasOne(OrderBlankToNomenclature::class, ['id' => 'obtn_id']);
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    /**
     * Общая сумма заказа
     * @param int $order_id Заказ
     * @return bool|false|null|string
     */
    public static function getTotalPrice($order_id)
    {
        $total = self::find()
            ->select(['SUM(REPLACE(price,",",".") * count)'])
            ->andWhere(['order_id' => $order_id])
            ->scalar();
        $total = (double)$total;

        return round($total, 2);
    }
}
