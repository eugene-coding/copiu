<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "buyer".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $pc_id Ценовая категория
 * @property int|null $user_id Пользователь системы
 *
 * @property PriceCategory $pc
 * @property Users $user
 */
class Buyer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'buyer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pc_id', 'user_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['pc_id'], 'exist', 'skipOnError' => true, 'targetClass' => PriceCategory::className(), 'targetAttribute' => ['pc_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'pc_id' => 'Ценовая категория',
            'user_id' => 'Пользователь системы',
        ];
    }

    /**
     * Gets query for [[Pc]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPc()
    {
        return $this->hasOne(PriceCategory::className(), ['id' => 'pc_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }
}
