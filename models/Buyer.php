<?php

namespace app\models;

use Ramsey\Uuid\Uuid;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "buyer".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $pc_id Ценовая категория
 * @property int|null $user_id Пользователь системы
 * @property string|null $outer_id Внешний идентификатор
 * @property string $balance Баланс
 * @property string $min_balance Минимальный Баланс
 * @property string $min_order_cost Минимальная сумма заказа
 * @property string $delivery_cost Сумма доставки
 * @property string $work_mode Режим работы
 * @property string $discount Скидка от ЦК
 * @property string $user_login Логин покупателя как пользователя
 * @property string $user_password Пароль покупателя как пользователя системы
 * @property array $addresses_list Адреса покупателя
 *
 * @property PriceCategory $pc
 * @property Users $user
 * @property string $workModeLabel
 * @property BuyerToOrderBlank[] $buyerToOrderBlanks Видимиость бланков для покупателя
 * @property BuyerAddress[] $addresses Адреса покупателя
 */
class Buyer extends ActiveRecord
{
    const MIN_ORDER_COST = 5000;
    const DELIVERY_COST = 500;

    const WORK_MODE_ACTIVE = 1;
    const WORK_MODE_DEACTIVATED = 2;
    const WORK_MODE_BALANCE_LIMIT = 3;

    public $user_login;
    public $user_password;
    public $addresses_list;

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
            [['name', 'outer_id'], 'string', 'max' => 255],
            [
                ['pc_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => PriceCategory::class,
                'targetAttribute' => ['pc_id' => 'id']
            ],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Users::class,
                'targetAttribute' => ['user_id' => 'id']
            ],
            [['balance', 'min_balance', 'min_order_cost', 'delivery_cost'], 'number'],
            [['work_mode'], 'integer'],
            [['discount'], 'number'],
            [['addresses_list'], 'safe'],
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
            'outer_id' => 'Внешний идентификатор',
            'balance' => 'Баланс',
            'min_balance' => 'Минимальный баланс',
            'min_order_cost' => 'Минимальный заказ',
            'delivery_cost' => 'Сумма доставки',
            'work_mode' => 'Режим работы',
            'discount' => 'Скидка от ЦК (%)',
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->discount > 1) {
            $this->discount = $this->discount / 100;
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->addresses_list){
            $addresses = BuyerAddress::find()
                ->where([
                    'buyer_id' => $this->id,
                ])
                ->indexBy('id')
                ->all()
            ;

            foreach ($this->addresses_list as $id => $address){
                if (!$address) continue;

                $model = $addresses[$id] ?? new BuyerAddress();
                $model->buyer_id = $this->id;
                $model->address = $address;
                if (!$model->save()){
                    Yii::error($model->errors, '_error');
                } else {
                    unset($addresses[$id]);
                };
            }
            foreach ($addresses as $address) {
                $address->delete();
            }
        }
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

    /**
     * Получает список режимов работы пользователя
     * @return array
     */
    public static function getWorkModeList()
    {
        return [
            static::WORK_MODE_ACTIVE => 'Активирован',
            static::WORK_MODE_DEACTIVATED => 'Деактивирован',
            static::WORK_MODE_BALANCE_LIMIT => 'Ограничение по балансу',
        ];
    }

    /**
     * Синхронизирует покупателей с базой
     * @param array $data Массив покупателей из postman
     * @return array
     */
    public function sync($data)
    {
        $rows = [];
        $exists_buyer = Buyer::find()->select(['outer_id'])->column();
        //Yii::debug($exists_buyer, 'test');

        $price_categories = ArrayHelper::map(PriceCategory::find()->all(), 'outer_id', 'id');

        foreach ($data as $buyer) {
            //Yii::debug($buyer, 'test');
            //Yii::debug('Buyer outer_id: ' . $buyer['id'], 'test');
            $outer_id = (string)$buyer['id'];
            $outer_price_category = (string)$buyer['price_category'];

            if (!in_array($outer_id, $exists_buyer)) {
                //Покупатель не найден в базе
                $name = (string)$buyer['name'];
                if ($outer_price_category) {
                    $price_category = $price_categories[$outer_price_category];
                } else {
                    $price_category = null;
                }
                $rows[] = [$name, $price_category, $outer_id, self::MIN_ORDER_COST, self::DELIVERY_COST];
            } else {
                //Обновление покупателя
                /** @var Buyer $buyer */
                $buyer = Buyer::find()->andWhere(['outer_id' => $buyer['id']])->one();
                /** @var PriceCategory $category */
                $category = PriceCategory::find()->andWhere(['outer_id' => $outer_price_category])->one();
                $buyer->pc_id = $category ? $category->id : null;
                $buyer->name = (string)$buyer['name'];
                if ($buyer->min_order_cost == 0) $buyer->min_order_cost = self::MIN_ORDER_COST;
                if ($buyer->delivery_cost == 0) $buyer->delivery_cost = self::DELIVERY_COST;

                if (!$buyer->save()) {
                    Yii::error($buyer->errors, '_error');
                }

            }
        }

        try {
            Yii::$app->db->createCommand()->batchInsert(Buyer::tableName(), ['name', 'pc_id', 'outer_id', 'min_order_cost', 'delivery_cost'],
                $rows)->execute();
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), '_error');
        }

        return [
            'success' => true,
            'data' => 'Синхронизация покупателей прошла успешно',
        ];
    }

    /**
     * Наименование типа работы
     * @return mixed|string
     */
    public function getWorkModeLabel()
    {
        if ($this->work_mode) {
            return $this::getWorkModeList()[$this->work_mode];
        }

        return '';
    }

    /**
     * Интервалы для выбора времени доставки
     * @param $type
     * @return array
     */
    public function getDeliveryTimeIntervals($type): array
    {
        $delivery_period = (double)Settings::getValueByKey('delivery_period');
        $from_setting = Settings::getValueByKey('delivery_min_time');
        $from = (int)explode(':', $from_setting)[0];

        $to_setting = Settings::getValueByKey('delivery_max_time');
        $to = (int)explode(':', $to_setting)[0];

        //Yii::debug('From: ' . $from, 'test');
        //Yii::debug('To: ' . $to, 'test');

        switch ($type) {
            case 'from':
                return $this->getTimeIntervals($from, $to - $delivery_period);
                break;
            case 'to':
                return $this->getTimeIntervals($from + $delivery_period, $to);
                break;
            default:
                return [];
        }
    }

    private function getTimeIntervals($start, $end)
    {
        $result_arr = [];
        for ($i = $start; $i <= $end; $i++) {
            $val = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $result_arr[$val . ':00'] = $val;
            if ($i < $end) {
                $val = str_pad($i, 2, '0', STR_PAD_LEFT) . ':30';
                $result_arr[$val . ':00'] = $val;
            }
        }

        //Yii::debug($result_arr, 'test');

        return $result_arr;
    }

    /**
     * Расчитыват скидку для суммы
     * @param float $sum Сумма для которой будет расчитываться скидка
     * @return float Возвращает сумму с учетом скидки
     */
    public function calcOnDiscount($sum)
    {
        $discount = $this->discount;
        if (!$discount) {
            return $sum;
        }

        $discount_sum = $sum * $discount;

        return round($sum - $discount_sum, 2);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuyerToOrderBlanks()
    {
        return $this->hasMany(BuyerToOrderBlank::class, ['buyer_id' => 'id']);
    }

    /**
     * Список покупателей
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(Buyer::find()->all(), 'id', 'name');
    }

    /**
     * Адреса покупателя
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
        return $this->hasMany(BuyerAddress::class, ['buyer_id' => 'id']);
    }

    public static function getAddressesList($id)
    {
        $addresses = BuyerAddress::findAll(['buyer_id' => $id]);
        return ArrayHelper::map($addresses, 'id', 'address');
    }

    public function generateUuid(): string
    {
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
    }

    /**
     * Check if a given string is a valid UUID
     *
     * @param   mixed  $uuid   The string to check
     * @return  boolean
     */
    public function isValidUuid($uuid): bool
    {
        return is_string($uuid) && preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uuid);
    }
}
