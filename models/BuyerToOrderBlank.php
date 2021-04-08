<?php

namespace app\models;

use app\models\query\BuyerToOrderBlankQuery;
use http\Exception;
use Yii;
use yii\db\ActiveRecord;

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
class BuyerToOrderBlank extends ActiveRecord
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
     * @return \yii\db\ActiveQuery|
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
     * @return BuyerToOrderBlankQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BuyerToOrderBlankQuery(get_called_class());
    }

    /**
     * Сохраняет видимость бланка для клиентов
     * @param OrderBlank $order_blank
     * @throws \yii\db\Exception
     */
    public static function insertBuyers($order_blank)
    {
        if ($order_blank->buyers) {
            $rows = [];
            foreach ($order_blank->buyers as $buyer_id) {
                $rows[] = [
                    $order_blank->id,
                    $buyer_id
                ];
            }

            try {
                Yii::$app->db->createCommand()
                    ->batchInsert(BuyerToOrderBlank::tableName(), ['order_blank_id', 'buyer_id'], $rows)
                    ->execute();

                $order_blank->show_to_all = 0;
                if (!$order_blank->save()) {
                    Yii::error($order_blank->errors, '_error');
                }
            } catch (Exception $e) {
                Yii::error($e->getMessage(), '_error');
            }
        }
    }
}
