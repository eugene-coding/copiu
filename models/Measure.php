<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "measure".
 *
 * @property int $id
 * @property string|null $name Наименование
 * @property string|null $outer_id Внешний идентификатор
 *
 * @property Nomenclature[] $nomenclatures
 */
class Measure extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'measure';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'outer_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'outer_id' => 'Внешний идентификатор',
        ];
    }

    /**
     * Gets query for [[Nomenclatures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNomenclatures()
    {
        return $this->hasMany(Nomenclature::className(), ['measure_id' => 'id']);
    }
}
