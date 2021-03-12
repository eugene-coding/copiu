<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property int|null $buyer_id Покупатель
 * @property string|null $created_at Дата создания
 * @property string|null $target_date Дата на которую формируется заказ
 * @property string|null $delivery_time_from Время доставки "от"
 * @property string|null $delivery_time_to Время доставки "до"
 * @property float|null $total_price Общая сумма заказа (включая доставку)
 * @property string|null $comment Комментарий
 *
 * @property Buyer $buyer
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['buyer_id'], 'integer'],
            [['created_at', 'target_date', 'delivery_time_from', 'delivery_time_to'], 'safe'],
            [['total_price'], 'number'],
            [['comment'], 'string'],
            [['buyer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Buyer::className(), 'targetAttribute' => ['buyer_id' => 'id']],
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
            'created_at' => 'Дата создания',
            'target_date' => 'Дата на которую формируется заказ',
            'delivery_time_from' => 'Время доставки \"от\"',
            'delivery_time_to' => 'Время доставки \"до\"',
            'total_price' => 'Общая сумма заказа (включая доставку)',
            'comment' => 'Комментарий',
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
     * {@inheritdoc}
     * @return \app\models\query\OrderQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\OrderQuery(get_called_class());
    }
}
