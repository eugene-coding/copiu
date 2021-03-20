<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "department".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $outer_id Внешний идентификатор
 */
class Department extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'department';
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
            'name' => 'Name',
            'outer_id' => 'Внешний идентификатор',
        ];
    }

    /**
     * Синхронизация отделов
     * @param array $data ['name'=><Наименование отдела>, 'outer_id' => <Внешний идентификатор>]
     * @return array
     */
    public function sync($data)
    {
        $exists_department = Department::find()->select(['outer_id'])->column();

        foreach ($data as $department){
            if (in_array($department['outer_id'], $exists_department)){
                $model = Department::findOne(['outer_id' => $department['outer_id']]);
            } else {
                $model = new Department();
            }

            $model->name = (string)$department['name'];
            $model->outer_id = (string)$department['outer_id'];

            if (!$model->save()){
                Yii::error($model->errors, '_error');
            }
        }

        return [
            'success' => true,
        ];

    }

    /**
     * Список отделов
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(static::find()->all(), 'outer_id', 'name');
    }
}
