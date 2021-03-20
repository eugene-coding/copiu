<?php

namespace app\models;

use app\components\IikoApiHelper;
use app\components\PostmanApiHelper;
use app\models\query\OrderQuery;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property int|null $buyer_id Покупатель
 * @property string|null $created_at Дата создания
 * @property string|null $target_date Дата на которую формируется заказ
 * @property string|null $delivery_time_from Время доставки "от"
 * @property string|null $delivery_time_to Время доставки "до"
 * @property float|null $total_price Общая сумма заказа (включая доставку)
 * @property string|null $comment Комментарий
 * @property int $status Статус
 * @property string|null $blanks Бланки заказов
 * @property double $count Кол-во продуктов
 * @property int $step Текущий шаг заказа
 * @property string|null $invoice_number Номер накладной
 * @property string|null $delivery_act_number Номер Акта оказанных услуг
 *
 * @property Buyer $buyer
 * @property Nomenclature[] $products;
 * @property OrderToNomenclature[] $orderToNomenclature;
 * @property double $deliveryCost;
 */
class Order extends ActiveRecord
{
    const STATUS_DRAFT = 1;
    const STATUS_WORK = 2;
    const STATUS_DONE = 3;

    public $count;
    public $step = 1;
    public $error_delivery_time;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['buyer_id', 'status', 'step'], 'integer'],
            [['created_at', 'target_date', 'delivery_time_from', 'delivery_time_to', 'count'], 'safe'],
            [['total_price'], 'number'],
            [['comment', 'blanks', 'invoice_number', 'delivery_act_number'], 'string'],
            [
                ['buyer_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Buyer::class,
                'targetAttribute' => ['buyer_id' => 'id']
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
            'buyer_id' => 'Покупатель',
            'created_at' => 'Дата создания',
            'target_date' => 'Дата на которую формируется заказ',
            'delivery_time_from' => 'Время доставки "от"',
            'delivery_time_to' => 'Время доставки "до"',
            'total_price' => 'Сумма заказа',
            'comment' => 'Комментарий',
            'status' => 'Статус',
            'blanks' => 'Бланки заказов',
            'invoice_number' => 'Накладная',
            'delivery_act_number' => 'Акт оказания услуг (доставка)',
        ];
    }

    public function beforeSave($insert)
    {
        //Общая сумма заказа (без доставки)
        $this->total_price = OrderToNomenclature::getTotalPrice($this->id);

        return parent::beforeSave($insert);
    }

    /**
     * Gets query for [[Buyer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBuyer()
    {
        return $this->hasOne(Buyer::class, ['id' => 'buyer_id']);
    }

    /**
     * {@inheritdoc}
     * @return OrderQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrderQuery(get_called_class());
    }

    /**
     * Список статусов заказа
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_WORK => 'В работе',
            self::STATUS_DONE => 'Завершен',
        ];
    }

    /**
     * Связи заказа с продуктами
     * @return \yii\db\ActiveQuery
     */
    public function getOrderToNomenclature()
    {
        return $this->hasMany(OrderToNomenclature::class, ['order_id' => 'id']);
    }

    /**
     * Продукты в заказе
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Nomenclature::class, ['id' => 'nomenclature_id'])
            ->via('orderToNomenclature');
    }

    /**
     * Возвращает стоимость доставки
     * @return int|double
     */
    public function getDeliveryCost()
    {
        if ($this->total_price > $this->buyer->min_order_cost) {
            return 0;
        } else {
            return $this->buyer->delivery_cost;
        }
    }

    /**
     * Формирует расходную накладную
     * @return bool|string
     */
    public function makeInvoice()
    {
        //Пример
//        $item = [
//              'outer_id',
//              'num',
//              'price',
//              'count',
//              'sum',
//        ];

        $items = Nomenclature::find()
            ->joinWith(['orders'])
            ->select([
                'nomenclature.outer_id',
                'nomenclature.num',
                'order_to_nomenclature.price',
                'order_to_nomenclature.count',
                '(order_to_nomenclature.price * order_to_nomenclature.count) AS sum'
            ])
            ->andWhere(['order.id' => $this->id])
            ->asArray()
            ->all();


        $params = [
            'documentNumber' => $this->getInvoiceNumber(),
            'counteragentId' => $this->buyer->outer_id,
            'from' => $this->delivery_time_from,
            'to' => $this->delivery_time_to,
            'comment' => $this->comment,
            'items' => $items,
        ];

        $helper = new IikoApiHelper();
        $result = $helper->makeExpenseInvoice($params);
        Yii::info($result);

        $xml = simplexml_load_string($result);

        if ($xml) {
            //Разбираем ответ
            if ($xml->valid == 'true') {
                $this->invoice_number = $xml->documentNumber;
                if (!$this->save()) {
                    Yii::error($this->errors, '_error');
                }
            }

            if ($xml->errorMessage) {
                Yii::error($xml->errorMessage, '_error');
            }

            if ($xml->additionalInfo) {
                Yii::warning($xml->additionalInfo, 'test');
            }

            return true;
        } else {
            return $result;
        }

    }

    /**
     * Акт оказания услуг (доставка)
     */
    public function makeDeliveryAct()
    {
        $params = [
            'entities_version' => Settings::getValueByKey('entities_version'),
            'revenueDebitAccount' => Settings::getValueByKey('revenue_debit_account'),
            'department' => Settings::getValueByKey('department_outer_id'),
            'revenueAccount' => Settings::getValueByKey('invoice_outer_id'),
            'buyer_outer_id' => $this->buyer->outer_id,
            'code' => Settings::getValueByKey('delivery_article'),
            'sum' => $this->deliveryCost,
            'amountUnit' => $this->products[0]->main_unit,
            'product' => $this->products[0]->outer_id,
            'amount' => $this->deliveryCost,
            'documentNumber' => 'xc' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'status' => 'PROCESSED',
            'dateIncoming' => date('Y-m-d\TH:i:s.000+03:00', time()),
        ];
        Yii::info($params, 'test');
        $helper = new PostmanApiHelper();
        $result = $helper->makeActOfServices($params);

        $xml = simplexml_load_string($result);

        if ($xml->returnValue->additionalInfo){
            Yii::warning($xml->returnValue->additionalInfo, 'test');
        }

        if ($xml->success == 'false'){
            Yii::error($xml->errorString, '_error');
            return $xml->errorString;
        } else {
            $this->delivery_act_number = $xml->returnValue->documentNumber;

            if (!$this->save()){
                Yii::error($this->errors, '_error');
            }
        }

        return true;
    }

    /**
     * Генерирует номер накладной. xc<id заказа>_<Unix время создания заказа>
     */
    public function getInvoiceNumber()
    {
        return 'xc' . $this->id . '_' . strtotime($this->created_at);
    }

}
