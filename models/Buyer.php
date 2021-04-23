<?php

namespace app\models;

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
 *
 * @property PriceCategory $pc
 * @property Users $user
 * @property string $workModeLabel
 * @property BuyerToOrderBlank[] $buyerToOrderBlanks Видимиость бланков для покупателя
 */
class Buyer extends ActiveRecord
{
    const WORK_MODE_ACTIVE = 1;
    const WORK_MODE_DEACTIVATED = 2;
    const WORK_MODE_BALANCE_LIMIT = 3;

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
        if ($this->discount > 1){
            $this->discount = $this->discount / 100;
        }
        return parent::beforeSave($insert);
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
     * Синхронизирует покупателей с базой
     * @param array $data Массив покупателей из postman
     * @return array
     */
    public function sync($data)
    {
        $rows = [];
        $exists_buyer = Buyer::find()->select(['outer_id'])->column();
        Yii::info($exists_buyer, 'test');

        $price_categories = ArrayHelper::map(PriceCategory::find()->all(), 'outer_id', 'id');

        foreach ($data as $buyer) {
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
                $rows[] = [$name, $price_category, $outer_id];
            } else {
                //Обновление покупателя
                $buyer = Buyer::findOne(['outer_id' => $buyer]);
                $buyer->pc_id = PriceCategory::findOne(['outer_id' => $outer_price_category])->id;
                $buyer->name = (string)$buyer['name'];

                if (!$buyer->save()){
                    Yii::error($buyer->errors, '_error');
                }

            }
        }
//        Yii::info('Строки для добавления покупателей', 'test');
//        Yii::info($rows, 'test');

        try {
            Yii::$app->db->createCommand()->batchInsert(Buyer::tableName(), ['name', 'pc_id', 'outer_id'],
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
    public function getDeliveryTimeIntervals($type)
    {
        $from_setting = Settings::getValueByKey('delivery_min_time');
        $from = (int)explode(':',$from_setting)[0];

        $to_setting = Settings::getValueByKey('delivery_max_time');
        $to = (int)explode(':',$to_setting)[0];

        Yii::info('From: ' . $from, 'test');
        Yii::info('To: ' . $to, 'test');

        switch ($type) {
            case 'from':
                return $this->getTimeIntervals($from, $to - 2);
                break;
            case 'to':
                return $this->getTimeIntervals($from + 2, $to);
                break;
            default:
                return[];
        }
    }

    private function getTimeIntervals($start, $end)
    {
        $result_arr = [];
        for ($i = $start; $i <= $end; $i++){
            $val = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $result_arr[$val] = $val;
        }

        Yii::info($result_arr, 'test');

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
        if (!$discount){
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
}
