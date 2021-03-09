<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "price_category_to_nomenclature".
 *
 * @property int $id
 * @property int|null $pc_id Ценовая категория
 * @property int|null $n_id Продукт (позиция номенклатуры)
 * @property float|null $price Цена продукта для ценовой группы
 *
 * @property Nomenclature $nomenclature
 * @property PriceCategory $pc
 */
class PriceCategoryToNomenclature extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'price_category_to_nomenclature';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pc_id', 'n_id'], 'integer'],
            [['price'], 'number'],
            [['n_id'], 'exist', 'skipOnError' => true, 'targetClass' => Nomenclature::class, 'targetAttribute' => ['n_id' => 'id']],
            [['pc_id'], 'exist', 'skipOnError' => true, 'targetClass' => PriceCategory::class, 'targetAttribute' => ['pc_id' => 'id']],
            [['pc_id'], 'unique', 'targetAttribute' => ['pc_id', 'n_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pc_id' => 'Ценовая категория',
            'n_id' => 'Продукт (позиция номенклатуры)',
            'price' => 'Цена продукта для ценовой группы',
        ];
    }

    /**
     * Gets query for [[N]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNomenclature()
    {
        return $this->hasOne(Nomenclature::class, ['id' => 'n_id']);
    }

    /**
     * Gets query for [[Pc]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPc()
    {
        return $this->hasOne(PriceCategory::class, ['id' => 'pc_id']);
    }
}
