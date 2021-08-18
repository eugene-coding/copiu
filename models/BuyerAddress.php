<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "buyer_address".
 *
 * @property int $id
 * @property int|null $buyer_id
 * @property string|null $address
 *
 * @property Buyer $buyer
 * @property Order $order
 */
class BuyerAddress extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'buyer_address';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['buyer_id'], 'integer'],
            [['address'], 'string'],
            [
                ['buyer_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Buyer::class,
                'targetAttribute' => ['buyer_id' => 'id']
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
            'buyer_id' => 'Покупатель',
            'address' => 'Адрес',
        ];
    }

    /**
     * Gets query for [[Buyer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBuyer()
    {
        return $this->hasOne(Buyer::class, ['id' => 'buyer_id']);
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['delivery_address_id' => 'id'])->inverseOf('address');
    }

    /**
     * Список адресов для покупателя
     * @param int $id
     * @return array
     */
    public static function getList($id)
    {
        return ArrayHelper::map(self::findAll(['buyer_id' => $id]), 'id', 'address');
    }
}
