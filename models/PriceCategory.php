<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "price_category".
 *
 * @property int $id
 * @property string|null $code
 * @property string|null $name
 *
 * @property Buyer[] $buyers
 */
class PriceCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'price_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['code'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Код',
            'name' => 'Наименование',
        ];
    }

    /**
     * Gets query for [[Buyers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBuyers()
    {
        return $this->hasMany(Buyer::className(), ['pc_id' => 'id']);
    }
}
