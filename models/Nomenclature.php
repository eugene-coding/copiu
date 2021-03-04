<?php

namespace app\models;

use app\models\query\NomenclatureQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "nomenclature".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $outer_id
 * @property string|null $num Артикул
 * @property int|null $n_group_id Номенклатурная группа
 * @property int|null $measure_id Единица измерения
 * @property float|null $default_price Цена по умолчанию
 * @property string|null $unit_weight Вес одной единицы
 * @property string|null $unit_capacity Объём одной единицы
 *
 * @property Measure $measure
 * @property NGroup $nGroup
 * @property PriceCategoryToNomenclature[] $priceCategoryToNomenclatures
 */
class Nomenclature extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nomenclature';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['n_group_id', 'measure_id'], 'integer'],
            [['default_price'], 'number'],
            [['name', 'outer_id', 'num', 'unit_weight', 'unit_capacity'], 'string', 'max' => 255],
            [['measure_id'], 'exist', 'skipOnError' => true, 'targetClass' => Measure::className(), 'targetAttribute' => ['measure_id' => 'id']],
            [['n_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => NGroup::className(), 'targetAttribute' => ['n_group_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'outer_id' => 'Outer ID',
            'num' => 'Артикул',
            'n_group_id' => 'Номенклатурная группа',
            'measure_id' => 'Единица измерения',
            'default_price' => 'Цена по умолчанию',
            'unit_weight' => 'Вес одной единицы',
            'unit_capacity' => 'Объём одной единицы',
        ];
    }

    /**
     * Gets query for [[Measure]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\MeasureQuery
     */
    public function getMeasure()
    {
        return $this->hasOne(Measure::className(), ['id' => 'measure_id']);
    }

    /**
     * Gets query for [[NGroup]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\NGroupQuery
     */
    public function getNGroup()
    {
        return $this->hasOne(NGroup::className(), ['id' => 'n_group_id']);
    }

    /**
     * Gets query for [[PriceCategoryToNomenclatures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPriceCategoryToNomenclatures()
    {
        return $this->hasMany(PriceCategoryToNomenclature::className(), ['n_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return NomenclatureQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new NomenclatureQuery(get_called_class());
    }
}
