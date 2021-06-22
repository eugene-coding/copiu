<?php

namespace app\models;

use app\components\IikoApiHelper;
use app\components\PostmanApiHelper;
use app\models\query\OrderQuery;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

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
 * @property array $count Кол-во продуктов
 * @property int $step Текущий шаг заказа
 * @property string|null $invoice_number Номер накладной
 * @property string|null $delivery_act_number Номер Акта оказанных услуг
 * @property string $buyer_name Наименование покупателя
 *
 * @property Buyer $buyer
 * @property OrderToNomenclature[] $orderToNomenclature;
 * @property double $deliveryCost;
 * @property OrderBlankToNomenclature[] $orderBlankToNomenclature;
 */
class Order extends ActiveRecord
{
    const STATUS_DRAFT = 1;
    const STATUS_WORK = 2;
    const STATUS_DONE = 3;

    public $count;
    public $step = 1;
    public $error_delivery_time;
    public $buyer_name;
    public $search_product_id;

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
            ['search_product_id', 'integer'],
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
            'buyer_name' => 'Покупатель',
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
     * Получает все товары из бланков заказа
     * @return \yii\db\ActiveQuery
     */
    public function getOrderBlankToNomenclature()
    {
        $blanks = explode(',', $this->blanks);
        return OrderBlankToNomenclature::find()->andWhere(['IN', 'ob_id', $blanks]);
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
        $items = [];
        $otn = OrderToNomenclature::find()
            ->andWhere(['order_id' => $this->id])
            ->all();

        /** @var OrderToNomenclature $order_to_nomenclature */
        foreach ($otn as $order_to_nomenclature) {
            /** @var OrderBlankToNomenclature $obtn */
            $obtn = OrderBlankToNomenclature::findOne($order_to_nomenclature->obtn_id);

            if ($obtn->container_id) {
                $container_id = $obtn->container_id;
                $count = (int)($obtn->container->count * $order_to_nomenclature->count);
            } else {
                $container_id = '';
                $count = $order_to_nomenclature->count;
            }

            $product = Nomenclature::find()
                ->andWhere(['id' => $obtn->n_id])
                ->one();

            $price = $product->getPriceForBuyer($container_id);

            $items[] = [
                'outer_id' => $product->outer_id,
                'num' => $product->num,
                'price' => $price,
                'amount' => $count,
                'sum' => round($order_to_nomenclature->count * $price, 2),
                'container_id' => $container_id,
            ];
        }

        Yii::info($items, 'test');

        $blank = $obtn->ob;

        if ($blank->show_number_in_comment) {
            $comment = 'ТОРГ12 ' . $blank->number . ". Доставка с {$this->delivery_time_from} по {$this->delivery_time_to} + «{$this->comment}»";
        } else {
            $comment = "ТОРГ12 Доставка с {$this->delivery_time_from} по {$this->delivery_time_to} + «{$this->comment}»";
        }

        $params = [
            'documentNumber' => $this->getInvoiceNumber(),
            'dateIncoming' => date('Y-m-d\TH:i:s', strtotime($this->target_date)),
            'counteragentId' => $this->buyer->outer_id,
            'from' => $this->delivery_time_from,
            'to' => $this->delivery_time_to,
            'comment' => $comment,
            'items' => $items,
            'defaultStoreId' => Settings::getValueByKey('store_outer_id'),
        ];
        Yii::info($params, 'test');

        $helper = new IikoApiHelper();
        $result = $helper->makeExpenseInvoice($params);
        Yii::info($result, 'test');

        $xml = null;
        try {
            $xml = simplexml_load_string($result);
        } catch (\Exception $e) {
            Yii::error($result, '_error');
            Yii::error($e->getMessage(), '_error');
            return false;
        }

        if ($xml) {
            //Разбираем ответ
            if ($xml->valid == 'true') {
                $this->invoice_number = (string)$xml->documentNumber;
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
            Yii::warning($result, 'test');
            return false;
        }

    }

    /**
     * Акт оказания услуг (доставка)
     */
    public function makeDeliveryAct()
    {
        $delivery_eid = Settings::getValueByKey('delivery_eid');
        $delivery_main_unit = Settings::getValueByKey('delivery_main_unit');

        $params = [
            'entities_version' => Settings::getValueByKey('entities_version'),
            'revenueDebitAccount' => Settings::getValueByKey('revenue_debit_account'),
            'department' => Settings::getValueByKey('department_outer_id'),
            'revenueAccount' => Settings::getValueByKey('invoice_outer_id'),
            'buyer_outer_id' => $this->buyer->outer_id,
            'code' => Settings::getValueByKey('delivery_article'),
            'sum' => $this->deliveryCost,
            'amountUnit' => $delivery_main_unit,
            'product' => $delivery_eid,
            'amount' => $this->deliveryCost,
            'documentNumber' => 'xc' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'status' => 'PROCESSED',
            'incomingDate' => date('Y-m-d\TH:i:s.000+03:00', strtotime($this->target_date)),
        ];
        Yii::info($params, 'test');
        //Проверяем наличие параметров
        foreach ($params as $item) {
            if (!$item) {
                Yii::error('Некоторые параметры не заданы.', '_error');
                return false;
            }
        }

        $helper = new PostmanApiHelper();
        $result = $helper->makeActOfServices($params);
        Yii::info($result, 'test');
        $xml = simplexml_load_string($result);

        if ((string)$xml->returnValue->additionalInfo) {
            Yii::warning((string)$xml->returnValue->additionalInfo, 'test');
        }

        if ($xml->success == 'false') {
            Yii::error($xml->errorString, '_error');
            return false;
        } else {
            $this->delivery_act_number = (string)$xml->returnValue->documentNumber;

            if (!$this->save()) {
                Yii::error($this->errors, '_error');
                return false;
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

    /**
     * Очищает до конца не заполненные заказы
     * @return void
     */
    public static function clean()
    {
        $user = Users::findOne(Yii::$app->user->identity->id);
        $buyer = $user->buyer;

        $fail_orders = Order::find()
            ->andWhere(['buyer_id' => $buyer->id])
            ->andWhere(['IS', 'invoice_number', null])
            ->all();

        foreach ($fail_orders as $order) {
            try {
                $order->delete();
            } catch (StaleObjectException $e) {
                Yii::error($e->getMessage(), 'error');
            } catch (\Exception $e) {
                Yii::error($e->getMessage(), 'error');
            } catch (\Throwable $e) {
                Yii::error($e->getMessage(), 'error');
            }
        }
    }


    /**
     * Обработка заказа
     * @throws StaleObjectException
     * @throws \Exception
     * @throws \Throwable
     */
    public function orderProcessing()
    {
        if (isset($this->count) && is_array($this->count)) {
            foreach ($this->count as $obtn_id => $count) {
                Yii::info($this->count, 'test');
                Yii::info($obtn_id . ' => ' . $count, 'test');
                if (!$count) {
                    //В случае если заказ скопирован, нужно удалить позицию из базы, т.к. кол-во продукта равно нулю
                    $otn_model = OrderToNomenclature::find()
                        ->andWhere(['obtn_id' => $obtn_id, 'order_id' => $this->id])
                        ->one();
                    if ($otn_model) {
                        $otn_model->delete();
                    }
                    continue;
                }
                $otn = OrderToNomenclature::find()
                    ->andWhere([
                        'order_id' => $this->id,
                        'obtn_id' => $obtn_id,
                    ])->one();
                if (!$otn) {
                    $otn = new OrderToNomenclature();
                    $otn->order_id = $this->id;
                    $otn->obtn_id = $obtn_id;
                }
                $obtn = OrderBlankToNomenclature::findOne($obtn_id);
                $otn->price = $obtn->n->getPriceForBuyer($obtn->container_id);
                $otn->count = $count;

                if (!$otn->save()) {
                    Yii::error($otn->errors, '_error');
                    $this->step = 1;
                }
            }
        }
    }

    /**
     * Получает общее кол-во продуктов в заказе
     * @return int
     */
    public function getTotalCountProducts()
    {
        $total_count = 0;
        if (!isset($this->count) || !is_array($this->count)) {
            return 0;
        }
        foreach ($this->count as $count) {
            if (!$count) {
                continue;
            }
            $total_count++;
        }

        return $total_count;
    }

    /**
     * Создает провайдер для таблицы с продуктами
     * @param string $search_string Строка поиска продукта
     * @param array $blanks Массив с номерами бланков
     * @return ArrayDataProvider
     */
    public function getProductDataProvider($product_id = null, $blanks = [])
    {
        if (!$blanks) {
            $blanks = explode(',', $this->blanks);
        }
        $data = [];

        $order_blanks_to_nomenclatures = OrderBlankToNomenclature::find()
            ->andWhere(['IN', 'ob_id', $blanks])
            ->all();

        /** @var OrderBlankToNomenclature $obtn */
        foreach ($order_blanks_to_nomenclatures as $obtn) {

            /** @var Nomenclature $product */
            $product = $obtn->n;

            if ($product_id && $product->id != $product_id){
                continue;
            }

            /** @var OrderToNomenclature $order_to_nomenclature */
            $order_to_nomenclature = OrderToNomenclature::findOne([
                'order_id' => $this->id,
                'obtn_id' => $obtn->id,
            ]);

            $data[$obtn->ob->number][] = [
                'id' => $product->id,
                'name' => $product->name,
                'count' => $order_to_nomenclature->count,
                'price' => $product->getPriceForBuyer($obtn->container_id),
                'measure' => $product->findMeasure($obtn),
                'obtn_id' => $obtn->id,
                'description' => $product->description,
            ];
        }

        $productsDataProvider = new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => false,
            'sort' => [
                'attributes' => ['name'],
            ],
        ]);
        Yii::info($data, 'test');
//        Yii::info($productsDataProvider, 'test');

        return $productsDataProvider;
    }

    /**
     * Продукты в бланке из заказа
     * @return array|ActiveRecord[]
     */
    public function getObtns()
    {
        return OrderBlankToNomenclature::find()
            ->joinWith(['orderToNomenclature'])
            ->andWhere(['order_to_nomenclature.order_id' => $this->id])->all();
    }

    /**
     * Получает список продуктов для Select2 (поиск по бланку)
     * @param array $products
     * [
     * [
     * 'id' => 2248,
     * 'name' => 'Товар1',
     * 'count' => null,
     * 'price' => 11.4,
     * 'measure' => 'кг',
     * 'obtn_id' => 3,
     * 'description' => '',
     * ],
     * [
     * 'id' => 2248,
     * 'name' => 'Товар1',
     * 'count' => null,
     * 'price' => 114.0,
     * 'measure' => '10 шт',
     * 'obtn_id' => 4,
     * 'description' => '',
     * ],
     * ]
     * @return array
     */
    public function getProductList($products = null)
    {
        $list = [];

        if (!$products) {
            return [];
        }

        foreach ($products as $product) {
            $list[$product['id']] = $product['name'];
        }

        return $list;
    }
}
