<?php

namespace app\models;

use app\models\query\ContainerQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "container".
 *
 * @property string|null $id UIID контейнера
 * @property int|null $nomenclature_id Позиция номенклатуры
 * @property string|null $name Наименование
 * @property string|null $count Количество
 * @property string|null $weight Вес
 * @property string|null $full_weight Обший вес
 * @property string|null $deleted Удалён
 *
 * @property Nomenclature $nomenclature
 */
class Container extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'container';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nomenclature_id'], 'integer'],
            [['id'], 'string', 'max' => 50],
            [['name', 'count', 'weight', 'full_weight', 'deleted'], 'string', 'max' => 255],
            [['id'], 'unique'],
            [
                ['nomenclature_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Nomenclature::class,
                'targetAttribute' => ['nomenclature_id' => 'id']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'UIID контейнера',
            'nomenclature_id' => 'Позиция номенклатуры',
            'name' => 'Наименование',
            'count' => 'Количество',
            'weight' => 'Вес',
            'full_weight' => 'Обший вес',
            'deleted' => 'Удалён',
        ];
    }

    /**
     * Gets query for [[Nomenclature]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\NomenclatureQuery
     */
    public function getNomenclature()
    {
        return $this->hasOne(Nomenclature::className(), ['id' => 'nomenclature_id']);
    }

    /**
     * {@inheritdoc}
     * @return ContainerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ContainerQuery(get_called_class());
    }
}
