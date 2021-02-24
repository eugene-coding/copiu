<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "settings".
 *
 * @property int $id
 * @property string|null $key
 * @property string|null $value
 * @property string|null $label
 * @property string|null $description
 * @property int|null $user_id
 * @property int|null $is_system Системная настройка
 *
 * @property Users $user
 */
class Settings extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['user_id', 'is_system'], 'integer'],
            [['key', 'value', 'label'], 'string', 'max' => 255],
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
            'key' => 'Key',
            'value' => 'Value',
            'label' => 'Label',
            'description' => 'Description',
            'user_id' => 'User ID',
            'is_system' => 'Системная настройка',
        ];
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

    /**
     * Получает значение настройки по ключу
     * @param $key
     * @return null|string
     */
    public static function getValueByKey($key)
    {
        /** @var Settings $setting */
        $setting = Settings::find()->andWhere(['key' => $key])->one();
        return $setting->value;
    }
}
