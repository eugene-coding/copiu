<?php

namespace app\models;

use app\components\IikoApiHelper;
use app\components\PostmanApiHelper;
use app\models\query\OrderQuery;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\ActiveRecord;
use yii\db\Exception;
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
 * @property string $delivery_address_id Адрес доставки
 *
 * @property Buyer $buyer
 * @property OrderToNomenclature[] $orderToNomenclature;
 * @property double $deliveryCost;
 * @property OrderBlankToNomenclature[] $orderBlankToNomenclature;
 * @property BuyerAddress $address;
 */
class Order extends ActiveRecord
{

    const STATUS_DRAFT = 1;
    const STATUS_WORK = 2;
    const STATUS_DONE = 3;
    const STATUS_ORDER_DRAFT = 5;
    const STATUS_ORDER_WAITING = 6;
    const STATUS_ERROR = 7;
    const STATUS_IN_PROGRESS = 8;

    const SCENARIO_DRAFT = 'draft';
    const SCENARIO_TO_QUEUE = 'to_queue';
    const SCENARIO_STEP_2 = 'step_2';

    public $count;
    public $step = 1;
    public $error_delivery_time;
    public $buyer_name;
    public $search_product_id;
    public $order_warning_html;


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
            [['buyer_id', 'status', 'step', 'delivery_address_id'], 'integer'],
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
            [
                ['comment'],
                'string',
                'length' => [0, 255],
                'message' => '«Комментарий» должен содержать максимум 255 символов.'
            ],
            [['comment', 'count'], 'required', 'on' => self::SCENARIO_DRAFT],
            [['target_date', 'delivery_address_id'], 'required', 'on' => self::SCENARIO_TO_QUEUE],
            [
                ['delivery_time_from'],
                'required',
                'on' => self::SCENARIO_TO_QUEUE,
                'message' => 'Укажите начало периода доставки'
            ],
            [
                ['delivery_time_to'],
                'required',
                'on' => self::SCENARIO_TO_QUEUE,
                'message' => 'Укажите конец периода доставки'
            ],
            [
                ['comment', 'delivery_time_from', 'delivery_time_to'],
                'required',
                'on' => self::SCENARIO_STEP_2
            ],
            ['order_warning_html', 'string'],
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
            'delivery_address_id' => 'Адрес доставки',
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
            self::STATUS_ERROR => 'Ошибка',
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

        Yii::debug($items, 'test');

        $blank = $obtn->ob;

        if ($blank->show_number_in_comment) {
            $comment = 'ТОРГ12 '
                . $blank->number
                . ". Доставка с {$this->delivery_time_from} по {$this->delivery_time_to} + «{$this->comment}»";
        } else {
            $comment = "ТОРГ12 Доставка с {$this->delivery_time_from} по {$this->delivery_time_to} + «{$this->comment}»";
        }

        //Проверяем адрес
        if ($this->delivery_address_id) {
            $addr = $this->address->address ?? '';
            if ($addr) {
                $comment .= " Адрес: {$addr}";
            }
        }

        //проверяем длину коммента
        Yii::debug('Comment length: ' . mb_strlen($this->comment), 'test');
        if (mb_strlen($comment) > 255) {
            $comment = mb_substr($comment, 0, 254);
        }
        Yii::debug('Comment length after: ' . mb_strlen($comment), 'test');
        Yii::debug('Comment after: ' . $comment, 'test');

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
        Yii::debug($params, 'test');

        $helper = new IikoApiHelper();
        $result = $helper->makeExpenseInvoice($params);
        Yii::debug($result, 'test');

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
            'documentNumber' => 'D' . str_pad($this->id, 5, '0', STR_PAD_LEFT),
            'status' => 'PROCESSED',
            'incomingDate' => date('Y-m-d\TH:i:s.000+03:00', strtotime($this->target_date)),
        ];
        Yii::debug($params, 'test');
        //Проверяем наличие параметров
        foreach ($params as $item) {
            if (!$item) {
                Yii::error('Некоторые параметры не заданы.', '_error');
                return false;
            }
        }

        $helper = new PostmanApiHelper();
        $result = $helper->makeActOfServices($params);
        Yii::info('Ответ на запрос создания акта доставки:', 'test');
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
     * Генерирует номер накладной. N<id заказа>
     */
    public function getInvoiceNumber()
    {
        return 'N' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Очищает до конца не заполненные заказы
     * @return void
     */
    public static function clean()
    {
        Yii::debug('This is Clean() start', 'test');
        $user = Users::findOne(Yii::$app->user->identity->id);
        $buyer = $user->buyer;

        $not_finished_orders = Order::find()
            ->andWhere(['buyer_id' => $buyer->id])
            ->andWhere(['status' => self::STATUS_IN_PROGRESS])
            ->all();
        Yii::debug($not_finished_orders, 'test');

        /** @var Order $order */
        foreach ($not_finished_orders as $order) {
            Yii::debug($order->attributes, 'test');
            try {
                $order->delete();
            } catch (\Exception $e) {
                Yii::error($e->getMessage(), 'error');
            } catch (\Throwable $e) {
                Yii::error($e->getMessage(), 'error');
            }
        }
        Yii::debug('This is Clean() end', 'test');
    }

    /**
     * Обработка заказа
     * @throws StaleObjectException
     * @throws \Exception
     * @throws \Throwable
     */
    public function orderProcessing()
    {
        /** @var string $check_quantity_enabled зависимость кол-ва заказываемых товаров от кол-ва товаров в бланке заказа */
        $check_quantity_enabled = Settings::getValueByKey('check_quantity_enabled');

        if (isset($this->count) && is_array($this->count)) {
            foreach ($this->count as $obtn_id => $count) {
                $obtn = OrderBlankToNomenclature::findOne($obtn_id);
//                Yii::debug($this->count, 'test');
//                Yii::debug($obtn_id . ' => ' . $count, 'test');
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
                $otn->price = $obtn->n->getPriceForBuyer($obtn->container_id);
                $otn->count = $count;

                if ((int)$check_quantity_enabled) {
                    //Если включена зависимость кол-ва заказываемых товаров от кол-ва товаров в бланке заказа
                    //проверяем минимальное кол-во заказанных товаров
                    if ($count > 0 && $obtn->quantity > $count) {
                        Yii::$app->session->setFlash('warning',
                            'Не соблюдены условия минимального количества для заказа продукта ' . $obtn->n->name);
                        $this->addError('count',
                            'Не соблюдены условия минимального количества для заказапродукта' . $obtn->n->name);
                    }
                }

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
     * @param integer|null $product_id
     * @param array $blanks Массив с номерами бланков
     * @param ArrayDataProvider|null $favoriteDataProvider Избранные продукты
     * @return ArrayDataProvider
     */
    public function getProductDataProvider($product_id = null, $blanks = [], $favoriteDataProvider = null)
    {
        $favorite_obtn_ids = [];
        $user = User::getUser();
        $buyer = $user->buyer;

        if ($favoriteDataProvider) {
            //Если есть избранное
            $favorite_obtn_ids = FavoriteProduct::find()
                ->select(['obtn_id'])
                ->andWhere(['buyer_id' => $buyer->id])
                ->column();
        }
//        Yii::debug($favorite_obtn_ids, 'test');

        if (!$blanks) {
            $blanks = explode(',', $this->blanks);
        }
        $data = [];

        $order_blanks_to_nomenclatures = OrderBlankToNomenclature::find()
            ->andWhere(['IN', 'ob_id', $blanks])
            ->all();

        /** @var OrderBlankToNomenclature $obtn */
        foreach ($order_blanks_to_nomenclatures as $obtn) {
//            Yii::debug($obtn->attributes, 'test');
            //Исключаем избранные продукты
            if ($favorite_obtn_ids) {
                if (in_array($obtn->id, $favorite_obtn_ids)) {
                    //если продукт в избранном - пропускаем
//                    Yii::debug('Продукт в избранном. Пропускаем', 'test');
                    continue;
                }
            }

            /** @var Nomenclature $product */
            $product = $obtn->n;
//            Yii::debug('$product_id: ' . $product_id, 'test');
//            Yii::debug('$product->id: ' . $product->id, 'test');

            if ($product_id && $product->id != $product_id) {
//                Yii::debug('Пропускаем 2', 'test');
                continue;
            }

            /** @var OrderToNomenclature $order_to_nomenclature */
            $order_to_nomenclature = OrderToNomenclature::findOne([
                'order_id' => $this->id,
                'obtn_id' => $obtn->id,
            ]);

            //Избранное
            $is_favorite = FavoriteProduct::find()
                ->andWhere(['buyer_id' => $buyer->id])
                ->andWhere(['obtn_id' => $obtn->id])
                ->exists();
//            Yii::debug('$is_favorite: ' . $is_favorite, 'test');

            $data[$obtn->ob->number][] = [
                'id' => $product->id,
                'name' => $product->name,
                'count' => $order_to_nomenclature->count,
                'price' => $product->getPriceForBuyer($obtn->container_id),
                'measure' => $product->findMeasure($obtn),
                'obtn_id' => $obtn->id,
                'description' => $product->description,
                'min_quantity' => $obtn->quantity,
                'is_favorite' => (int)$is_favorite,
            ];
        }

        $productsDataProvider = new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => false,
            'sort' => [
                'attributes' => ['name'],
            ],
        ]);
        Yii::debug('НЕ избранное:', 'test');
        Yii::debug($data, 'test');
//        Yii::debug($productsDataProvider, 'test');

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

    /**
     * Адрес доставки. Связь с BuyerAddress
     * @return \yii\db\ActiveQuery
     */
    public function getAddress()
    {
        return $this->hasOne(BuyerAddress::class, ['id' => 'delivery_address_id']); //->inverseOf('order');
    }

    /**
     * Копирует заказ
     * @param int $id Идентификатор копируемого заказа
     * @return Order|bool
     */
    public static function copy($id)
    {
        $order_basis = Order::findOne($id);
        $order = new Order();
        $order->buyer_id = $order_basis->buyer_id;

        //Бланки заказов
        $order_blanks = explode(',', $order_basis->blanks);
        $blank_ids = null;

        if ($order_blanks) {
            //Заново получаем id бланков, т.к. их может уже не быть
            $blank_ids = OrderBlank::find()->select(['id'])->andWhere(['IN', 'id', $order_blanks])->column();
        }

        if (!$blank_ids) {
            //Бланки заказов уже удалены из системы
            Yii::$app->session->addFlash('error',
                'Ошибка при копировании заказа. Бланки заказов, указанные в заказе-источнике, не найдены');
            return false;
        }

        $order->status = 1;
        $order->blanks = implode(',', $blank_ids);
        $order->comment = $order_basis->comment;
        $order->delivery_time_from = $order_basis->delivery_time_from;
        $order->delivery_time_to = $order_basis->delivery_time_to;
        $order->total_price = $order_basis->total_price;

        if (!$order->save()) {
            Yii::error($order->errors, '_error');
            Yii::$app->session->addFlash('error', 'Ошибка при копировании заказа. ' . json_encode($order->errors));
            return false;
        }

        //Добавляем продукты в новый заказ
        $rows = [];

        $query = OrderToNomenclature::find()
            ->andWhere(['order_id' => $order_basis->id]);

        /** @var OrderToNomenclature $item */
        foreach ($query->each() as $item) {

            /** @var OrderBlankToNomenclature $obtn */
            $obtn = $item->obtn;
            $product = $obtn->n;
            $rows[] = [
                $order->id,
                $product->getPriceForBuyer($obtn->container_id),
                //Цену продукта рассчитываем заново, т.к. цена может измениться
                $item->count,
                $obtn->id,
            ];
        }

        try {
            Yii::$app->db->createCommand()->batchInsert(OrderToNomenclature::tableName(), [
                'order_id',
                'price',
                'count',
                'obtn_id',
            ], $rows)->execute();
        } catch (Exception $e) {
            Yii::error($e->getMessage(), '_error');
            Yii::$app->session->addFlash('error',
                'Ошибка при сохранении нового заказа. ' . $e->getMessage());
        }
        return $order;
    }

    public function getFavoriteDataProvider()
    {
        $data = [];
        $user = User::getUser();
        $buyer = $user->buyer;

        //Получаем продукты из выбранных бланков
        $obtn_ids = FavoriteProduct::find()
            ->select(['obtn_id'])
            ->andWhere(['buyer_id' => $buyer->id])
            ->column();

        $order_blanks_to_nomenclatures = OrderBlankToNomenclature::find()
            ->andWhere(['IN', 'id', $obtn_ids])
            ->all();

        /** @var OrderBlankToNomenclature $obtn */
        foreach ($order_blanks_to_nomenclatures as $obtn) {

            /** @var Nomenclature $product */
            $product = $obtn->n;

            //Избранное
            $user = User::getUser();
            $buyer = $user->buyer;


            $favorite = FavoriteProduct::find()
                ->andWhere(['buyer_id' => $buyer->id])
                ->andWhere(['obtn_id' => $obtn->id])
                ->one();

//            if ($favorite) {
//                $count = $favorite->count;
//            } else {
//                $count = 0;
//            }

            $data[$obtn->ob->number][] = [
                'id' => $product->id,
                'name' => $product->name,
//                'count' => $count,
                'price' => $product->getPriceForBuyer($obtn->container_id),
                'measure' => $product->findMeasure($obtn),
                'obtn_id' => $obtn->id,
                'is_favorite' => $favorite ? 1 : 0,
            ];

        }
        Yii::debug('Избранное.', 'test');
        Yii::debug($data, 'test');

        $favoriteDataProvider = new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => false,
            'sort' => [
                'attributes' => ['name'],
            ],
        ]);

        return $favoriteDataProvider;
    }

    /**
     * Проверяет корректность периода доставки
     * @return Order
     */
    public function checkDeliveryPeriod(): Order
    {
        if (!$this->delivery_time_to || !$this->delivery_time_from) {
            $this->addError('delivery_time_to', 'Не выбран период доставки');
            Yii::$app->session->setFlash('warning', 'Не выбран период доставки');
        }

        if ($this->delivery_time_from) {
            $delivery_period = Settings::getValueByKey('delivery_period');
            $from = date('H', strtotime($this->delivery_time_from));
            $to = date('H', strtotime($this->delivery_time_to));
            if (!$to) {
                $this->delivery_time_to = date('H:i', strtotime($from) + (60 * 60 * $delivery_period));
                $to = date('H', strtotime($this->delivery_time_to));
            }
            if ($from > $to) {
                Yii::$app->session->setFlash('warning', 'Конечное время доставки должно быть больше начального');
                $this->addError('error_delivery_time', 'Конечное время должно быть больше начального');
            } elseif (($to - $from) < $delivery_period) {
                Yii::$app->session->setFlash('warning', 'Увеличьте период доставки');
                $this->addError('error_delivery_time', 'Увеличьте период доставки');
            }
        }

        return $this;
    }
}
