<?php

namespace app\models;

use app\models\query\OrderLoggingQuery;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "order_logging".
 *
 * @property int $id
 * @property string|null $created_at
 * @property int|null $user_id
 * @property int|null $order_id
 * @property int|null $action_type Тип действия
 * @property string|null $description Описание
 * @property string|null $model Описание
 *
 * @property Order $order
 * @property Users $user
 */
class OrderLogging extends ActiveRecord
{
    const ACTION_ORDER_CREATE = 1;
    const ACTION_ORDER_STEP = 2;
    const ACTION_ORDER_FINISH = 3;
    const ACTION_ORDER_CHANGE_STATUS = 4;
    const ACTION_ORDER_CREATE_INVOICE = 5;
    const ACTION_ORDER_CREATE_DELIVERY_ACT = 6;
    const ACTION_ORDER_COPY = 7;
    const ACTION_ORDER_DELETE = 8;
    const ACTION_ORDER_CANCEL = 9;
    const ACTION_ORDER_ERROR = 10;
    const ACTION_ORDER_CREATE_DRAFT = 11;
    const ACTION_ORDER_ADD_PRODUCT = 12;

    public $order_info;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_logging';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['user_id', 'order_id', 'action_type'], 'integer'],
            [['description', 'model'], 'string'],
            [
                ['order_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Order::class,
                'targetAttribute' => ['order_id' => 'id']
            ],
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
            'created_at' => 'Created At',
            'user_id' => 'Пользователь',
            'order_id' => 'ID Заказа',
            'order_info' => 'Информация',
            'action_type' => 'Действие',
            'description' => 'Описание',
            'model' => 'Модель',
        ];
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\OrderQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
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
     * {@inheritdoc}
     * @return OrderLoggingQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrderLoggingQuery(get_called_class());
    }

    /**
     * Логирование событий
     * @param Order $order
     * @param int $action Действие
     * @param string $description Описание
     */
    public static function log(Order $order, $action, $description)
    {

        $model = new OrderLogging([
            'user_id' => Yii::$app->user->identity->id,
            'order_id' => $order->id,
            'action_type' => $action,
            'model' => json_encode($order->attributes),
            'description' => $description,
        ]);

        if (!$model->save()) {
            Yii::error($model->errors, '_error');
        }
    }

    public static function getActionList()
    {
        return [
            self::ACTION_ORDER_CREATE => 'Добавление',
            self::ACTION_ORDER_STEP => 'Шаг',
            self::ACTION_ORDER_FINISH => 'Завершение',
            self::ACTION_ORDER_CHANGE_STATUS => 'Смена статуса',
            self::ACTION_ORDER_CREATE_INVOICE => 'Создание накладной',
            self::ACTION_ORDER_CREATE_DELIVERY_ACT => 'Создание акта',
            self::ACTION_ORDER_COPY => 'Копирование',
            self::ACTION_ORDER_DELETE => 'Удаление',
            self::ACTION_ORDER_CANCEL => 'Отмена',
            self::ACTION_ORDER_ERROR => 'Ошибка',
            self::ACTION_ORDER_CREATE_DRAFT => 'Создание черновика',
            self::ACTION_ORDER_ADD_PRODUCT => 'Изменение кол-ва продукта',
        ];
    }

    public static function getOrerList()
    {
        return ArrayHelper::map(OrderLogging::find()->all(), 'order_id', 'order_id');
    }

    public function isJson($string) {
        return ((is_string($string) &&
            (is_object(json_decode($string)) ||
                is_array(json_decode($string))))) ? true : false;
    }
}
