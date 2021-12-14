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
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Users::class,
                'targetAttribute' => ['user_id' => 'id']
            ],
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

    public function beforeSave($insert)
    {
        if (!$this->user_id) {
            $this->user_id = Yii::$app->user->identity->id;
        }

        return parent::beforeSave($insert);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
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
        return $setting->value ?? null;
    }

    /**
     * @param $key
     * @param $new_value
     * @return bool
     */
    public static function setValueByKey($key, $new_value)
    {
        /** @var Settings $setting */
        $setting = static::find()->andWhere(['key' => $key])->one();

        if (!$setting) {
            Yii::warning('Настройка ' . $key . ' не найдена', 'test');
            return false;
        }

        $setting->value = $new_value;

        if (!$setting->save()) {
            Yii::error($setting->errors, '_error');
            return false;
        }

        return true;
    }

    /**
     * Проверяет настройки системы
     * @return array
     */
    public static function checkSettings()
    {
        $target_keys = [
            'ikko_server_url',
            'ikko_server_login',
            'ikko_server_password',
            'delivery_article',
            'department_outer_id',
            'invoice_outer_id',
            'get_nomenclature_date',
            'delivery_min_time',
            'delivery_max_time',
            'store_outer_id',
            'entities_version',
            'delivery_eid',
            'delivery_main_unit',
            'revenue_debit_account',
        ];

        /** @var array $key_to_sync Ключи, которые нужно получить через синхронизацию (номенклатуры, групп номенклатур и прочее) */
        $key_to_sync = [
            'get_nomenclature_date' => '3. Синхронизация номенклатуры',
            'entities_version' => '1. Синхронизация покупателей, ценовых категорий, отделов и пр.',
            'delivery_eid' => '1. Синхронизация покупателей, ценовых категорий, отделов и пр.',
            'delivery_main_unit' => '1. Синхронизация покупателей, ценовых категорий, отделов и пр.',
        ];

        $settings = Settings::find()->all();
        $errors = [];

        /** @var Settings $setting */
        foreach ($settings as $setting) {
            if (in_array($setting->key, $target_keys)) {
                if (!$setting->value) {
                    //Yii::debug($setting->key . ' In array: ' . (int)in_array($setting->key, $key_to_sync), 'test');
                    if (in_array($setting->key, array_keys($key_to_sync))) {
                        $errors[$setting->key] = 'Не выполнена синхронизация (Раздел "Синхронизация"): <b>'
                            . $key_to_sync[$setting->key] . '</b>';
                    } else {
                        $errors[$setting->key] = "Отсутсвует настройка: <b>'{$setting->label}'</b>";
                    }
                }
            }
        }

//        Yii::debug($errors, 'test');

        if ($errors) {
            return [
                'success' => false,
                'errors' => array_unique($errors),
            ];
        }
        return [
            'success' => true,
        ];
    }
}
