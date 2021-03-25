<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "measure".
 *
 * @property int $id
 * @property string|null $name Наименование
 * @property string|null $outer_id Внешний идентификатор
 * @property string|null $full_name Полное наименование
 *
 * @property Nomenclature[] $nomenclatures
 */
class Measure extends ActiveRecord
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
            [['name', 'outer_id', 'full_name'], 'string', 'max' => 255],
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
            'full_name' => 'Полное наименование',
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

    public function sync($data)
    {
        $exists_measure = Measure::find()->select(['outer_id'])->column();

        foreach ($data as $measure){
            if (in_array($measure['outer_id'], $exists_measure)){
                $model = Measure::findOne(['outer_id' => $measure['outer_id']]);
            } else {
                $model = new Measure();
            }

            $model->name = $measure['name'];
            $model->outer_id = $measure['outer_id'];
            $model->full_name = $measure['full_name'];

            if (!$model->save()){
                Yii::error($model->errors, '_error');
            }
        }

        return [
            'success' => true,
        ];
    }
}
