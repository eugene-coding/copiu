<?php

namespace app\models;

use app\models\query\AccountQuery;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "account".
 *
 * @property int $id
 * @property string|null $outer_id
 * @property string|null $name
 * @property string|null $type
 * @property string|null $description
 */
class Account extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['outer_id', 'name', 'type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'outer_id' => 'Outer ID',
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
        ];
    }

    /**
     * {@inheritdoc}
     * @return AccountQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AccountQuery(get_called_class());
    }

    public function sync($data)
    {
        $exists_account = Account::find()->select(['outer_id'])->column();

        foreach ($data as $account){
            if (in_array($account['outer_id'], $exists_account)){
                $model = Account::findOne(['outer_id' => $account['outer_id']]);
            } else {
                $model = new Account();
            }

            $model->name = (string)$account['name'];
            $model->outer_id = (string)$account['outer_id'];
            $model->type = (string)$account['type'];
            $model->description = (string)$account['description'];

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
        return ArrayHelper::map(static::find()->orderBy(['name' => SORT_ASC])->all(), 'outer_id', 'name');
    }
}
