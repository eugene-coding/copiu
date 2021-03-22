<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "store".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $outer_id
 * @property string|null $department_outer_id
 * @property string|null $description
 */
class Store extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'store';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['name', 'outer_id', 'department_outer_id'], 'string', 'max' => 255],
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
            'outer_id' => 'Outer ID',
            'department_outer_id' => 'Department Outer ID',
            'description' => 'Description',
        ];
    }

    public function sync($data)
    {
        $exists_store = Store::find()->select(['outer_id'])->column();

        foreach ($data as $store){
            if (in_array($store['outer_id'], $exists_store)){
                $model = Store::findOne(['outer_id' => $store['outer_id']]);
            } else {
                $model = new Store();
            }

            $model->name = $store['name'];
            $model->outer_id = $store['outer_id'];
            $model->department_outer_id = $store['department_outer_id'];
            $model->description = $store['description'];

            if (!$model->save()){
                Yii::error($model->errors, '_error');
            }
        }

        return [
            'success' => true,
        ];
    }

    public static function getList()
    {
        return ArrayHelper::map(static::find()->all(), 'outer_id', 'name');
    }
}
