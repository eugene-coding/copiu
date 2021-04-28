<?php

namespace app\models;

use app\models\query\ContainerQuery;
use Yii;
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
     * @inheritdoc $primaryKey
     */
    public static function primaryKey()
    {
        return ["id"];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nomenclature_id'], 'integer'],
            [['id'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 255],
            [['id'], 'unique'],
            [
                ['nomenclature_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Nomenclature::class,
                'targetAttribute' => ['nomenclature_id' => 'id']
            ],
            [['count', 'weight', 'full_weight'], 'number'],
            ['deleted', 'boolean']
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

    /**
     * Синхронизация
     * @param array $containers Массив контейнеров для позиции номенклатуры
     * @param int $nomenclature_id Позиция номенклатуры (продукт)
     * @return bool
     */
    public static function sync($containers, $nomenclature_id)
    {
        $exists_containers = Container::find()->select(['id'])->column();
        foreach ($containers as $container) {
            Yii::info($container, 'test');
            $container_id = $container['id'];

            if (in_array($container_id, $exists_containers)) {
                $container_model = Container::findOne($container_id);
            } else {
                $container_model = new Container([
                    'id' => $container_id,
                    'nomenclature_id' => $nomenclature_id,
                ]);
            }
            Yii::info($container_model->attributes, 'test');
            $container_model->name = $container['name'];
            $container_model->count = $container['count'];
            $container_model->weight = $container['containerWeight'];
            $container_model->full_weight = $container['fullContainerWeight'];
            $container_model->deleted = $container['deleted'];
            Yii::info($container_model->attributes, 'test');

            if (!$container_model->save()) {
                Yii::error($container_model->errors, '_error');
            }
        }
        return true;
    }
}
