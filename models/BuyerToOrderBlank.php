<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "buyer_to_order_blank".
 *
 * @property int $id
 * @property int|null $buyer_id
 * @property int|null $order_blank_id
 *
 * @property Buyer $buyer
 * @property OrderBlank $orderBlank
 */
class BuyerToOrderBlank extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'buyer_to_order_blank';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['buyer_id', 'order_blank_id'], 'integer'],
            [['buyer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Buyer::className(), 'targetAttribute' => ['buyer_id' => 'id']],
            [['order_blank_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrderBlank::className(), 'targetAttribute' => ['order_blank_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'buyer_id' => 'Buyer ID',
            'order_blank_id' => 'Order Blank ID',
        ];
    }

    /**
     * Gets query for [[Buyer]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\BuyerQuery
     */
    public function getBuyer()
    {
        return $this->hasOne(Buyer::className(), ['id' => 'buyer_id']);
    }

    /**
     * Gets query for [[OrderBlank]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\OrderBlankQuery
     */
    public function getOrderBlank()
    {
        return $this->hasOne(OrderBlank::className(), ['id' => 'order_blank_id']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\BuyerToOrderBlankQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\BuyerToOrderBlankQuery(get_called_class());
    }
}
