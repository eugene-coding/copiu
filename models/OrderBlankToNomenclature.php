<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_blank_to_nomenclature".
 *
 * @property int $id
 * @property int|null $ob_id Бланк заказа
 * @property int|null $n_id Продукт
 *
 * @property Nomenclature $n
 * @property OrderBlank $ob
 */
class OrderBlankToNomenclature extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_blank_to_nomenclature';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ob_id', 'n_id'], 'integer'],
            [
                ['n_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Nomenclature::class,
                'targetAttribute' => ['n_id' => 'id']
            ],
            [
                ['ob_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => OrderBlank::class,
                'targetAttribute' => ['ob_id' => 'id']
            ],
            [['n_id', 'ob_id'], 'unique', 'targetAttribute' => ['n_id', 'ob_id'], 'message' => 'Продукт уже присутствует в бланке'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ob_id' => 'Бланк заказа',
            'n_id' => 'Продукт',
        ];
    }

    /**
     * Gets query for [[N]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getN()
    {
        return $this->hasOne(Nomenclature::className(), ['id' => 'n_id']);
    }

    /**
     * Gets query for [[Ob]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOb()
    {
        return $this->hasOne(OrderBlank::className(), ['id' => 'ob_id']);
    }
}
