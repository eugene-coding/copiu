<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_blank".
 *
 * @property int $id
 * @property string|null $number Номер накладной
 * @property string|null $date Дата накладной
 * @property int|null $time_limit Ограничение по времени
 * @property int|null $day_limit Ограничение по дням
 * @property string|null $synced_at Дата и время синхронизации
 */
class OrderBlank extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_blank';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'synced_at', 'time_limit'], 'safe'],
            [[ 'day_limit'], 'integer'],
            [['number'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'number' => 'Номер накладной',
            'date' => 'Дата накладной',
            'time_limit' => 'Ограничение по времени',
            'day_limit' => 'Ограничение по дням',
            'synced_at' => 'Дата и время синхронизации',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\OrderBlankQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\OrderBlankQuery(get_called_class());
    }
}
