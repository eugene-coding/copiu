<?php

namespace app\models;

use app\components\IikoApiHelper;
use app\components\PostmanApiHelper;
use app\models\query\OrderBlankQuery;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "order_blank".
 *
 * @property int $id
 * @property string|null $number Номер накладной
 * @property string|null $date Дата накладной
 * @property int|null $time_limit Ограничение по времени
 * @property int|null $day_limit Ограничение по дням
 * @property string|null $synced_at Дата и время синхронизации
 * @property string|null $show_to_all Виден всем покупателям
 * @property string|null $buyers Заказчики
 * @property int show_number_in_comment Вставлять номер в комментарий к заказу
 *
 * @property Nomenclature[] $products Продукты в из накладной
 * @property OrderBlankToNomenclature[] $orderBlankToNomenclature
 * @property OrderBlankToNomenclature[] $buyerToOrderBlanks Видимость бланков для покупателей
 */
class OrderBlank extends ActiveRecord
{
    public $buyers;

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
            [['day_limit', 'show_to_all'], 'integer'],
            [['number'], 'string', 'max' => 255],
            [['number'], 'unique', 'message' => 'Накладная уже есть в базе'],
            ['buyers', 'safe'],
            ['show_number_in_comment', 'integer'],
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
            'show_to_all' => 'Видимость',
            'buyers' => 'Заказчики',
            'show_number_in_comment' => 'Добавлять название бланка в комментарий',
        ];
    }

    /**
     * {@inheritdoc}
     * @return OrderBlankQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrderBlankQuery(get_called_class());
    }

    /**
     * Синхронизация всех бланков заказа
     * @return array
     * @throws InvalidConfigException
     * @throws Exception
     */
    public static function sync()
    {
        $helper = new IikoApiHelper();
        $result = [];

        foreach (static::find()->all() as $blank) {
            $params = [
                'number' => $blank->number,
                'from' => $blank->date,
                'to' => $blank->date,
            ];
            $response = $helper->getOrderBlank($params);
            $result[$blank->id] = $response;
        }
        //Очищаем таблицу связей бланка с номенклатурой
        Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=0;')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE `order_blank_to_nomenclature`;')->execute();
        Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=1;')->execute();

        $nomenclature = ArrayHelper::map(Nomenclature::find()->all(), 'outer_id', 'id');
        $containers = Container::find()->select(['id'])->column();
        $product_outer_ids_in_blanks = [];
        $rows = [];

        foreach ($result as $blank_id => $data) {
            $blank_model = OrderBlank::findOne($blank_id);
            $number = $blank_model->number;
            $date = Yii::$app->formatter->asDate($blank_model->date);

            if (!$data) {
                return [
                    'success' => false,
                    'error' => "Ошибка синхронизации. Для накладной № {$number} от {$date}: не получена информация",
                ];
            }

            if (isset($data['document']['items']['item']['productId'])) {
                //Если один продукт в накладной
                $data['document']['items']['item'] = [$data['document']['items']['item']];
            }

            foreach ($data['document']['items']['item'] as $item) {
                $product_id = $item['productId'];
                $n_id = $product_id ? $nomenclature[$product_id] : null;
                $container_id = $item['containerId'] ?? null;
                $quantity = (double)$item['amount'] ?? 0;
                if ($container_id) {
//                    Yii::warning($item, 'test');
                    if (!in_array($container_id, $containers)) {
                        //Синхронизируем позицию номенкалтуры
                        Nomenclature::syncByIds([$product_id]);
                        //Обновляем список контейнеров
                        $containers = Container::find()->select(['id'])->column();
                        if (!in_array($container_id, $containers)) {
                            Yii::error('Ошибка синхронизации, необходимо обновление номенклатуры', '_error');
                            return [
                                'success' => false,
                                'error' => 'Ошибка синхронизации, необходимо обновление номенклатуры',
                                'date' => date('d.m.Y H:i', time()),
                            ];
                        }
                    }
                    $count_per_container = Container::findOne($container_id)->count ?? 0;

                    if ($count_per_container){
                        $quantity = $quantity/$count_per_container;
                    } else {
                        $quantity = 0;
                    }
                }

                if ($n_id) {
                    $product_outer_ids_in_blanks[] = $item['productId'];
                    $rows[] = [$n_id, $blank_id, $container_id, $quantity];
                } else {
                   //Yii::debug('Продукт ' . $item['productId'] . ' не найден в номенклатуре, пропускаем', 'test');
                }

            }
            Yii::debug('Check 1', 'test');

            //Синхронизируем цены для ценовых категорий товаров из бланка
            static::syncPriceForPriceCategory();
            Yii::debug('Check 1.1', 'test');

            //Пишем время синхронизации
            $blank_model->synced_at = date('Y-m-d H:i:s', time());
            if (!$blank_model->save()) {
                Yii::debug($blank_model->errors, '_error');
            }
        }
        Yii::debug('Check 2', 'test');

       //Yii::debug($product_outer_ids_in_blanks, 'test');
       //Yii::debug($rows, 'test');

        //Сохраняем всё
        Yii::$app->db->createCommand()->batchInsert(OrderBlankToNomenclature::tableName(),
            ['n_id', 'ob_id', 'container_id', 'quantity'], $rows)->execute();


        if ($product_outer_ids_in_blanks) {
            //Обновляем продукты указанные в бланках
           //Yii::debug($product_outer_ids_in_blanks, 'test');
            Yii::debug('Check 3', 'test');
            Nomenclature::syncByIds($product_outer_ids_in_blanks);
            Yii::debug('Check 3.1', 'test');

            //Обновляем цены для ценовых категорий в которых находятся продукты бланков
            Yii::debug('Check 4', 'test');
            PriceCategoryToNomenclature::syncForProducts($product_outer_ids_in_blanks);
            Yii::debug('Check 4.1', 'test');
        }

        Yii::warning('Всего памяти ' . (memory_get_usage(true) / 1048576) . 'M', 'test');

        return [
            'success' => true,
            'data' => 'Синхронизация завершена',
            'date' => date('d.m.Y H:i', time()),
        ];
    }

    public function getOrderBlankToNomenclature()
    {
        return $this->hasMany(OrderBlankToNomenclature::class, ['ob_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Nomenclature::class, ['id' => 'ob_id'])
            ->via('orderBlankToNomenclature');
    }

    /**
     * Получает бланки заказов на дату
     * @param string $date Дата на которую производится заказ (Y-m-d)
     * @return OrderBlank[]
     */
    public static function getBlanksByDate(string $date): array
    {
        $date = date('Y-m-d 00:00:00', strtotime($date));
        $target_date = date('Y-m-d 23:59:59');

        $diff_time = strtotime($date) - strtotime($target_date);
        $diff_days = floor($diff_time / (60 * 60 * 24) + 1);

       //Yii::debug('Diff days: ' . $diff_days, 'test');

        $current_time = date('H:i:00.0000', time());
        return self::find()
            ->andWhere(['<=', 'day_limit', $diff_days])
            ->andWhere(['>', 'time_limit', $current_time])
            ->all();
    }

    /**
     * @param OrderBlank[] $blanks
     * @param string $target_date
     * @return string
     * @throws InvalidConfigException
     */
    public static function blanksToTable($blanks, $target_date)
    {
        $result = '';
        $blank_ids = [];
        /** OrderBlank $blank */
        foreach ($blanks as $blank) {
            $buyers = ArrayHelper::map($blank->buyerToOrderBlanks, 'id', 'buyer_id');
            $user = Users::getUser();
           //Yii::debug($buyers, 'test');
           //Yii::debug('Buyer ID: ' . $user->buyer->id, 'test');
            if ($buyers && !in_array($user->buyer->id, $buyers)){
                //Если для бланка видимость только выбранным и покупателя нет в списке видимости
                continue;
            }

            $count_products = OrderBlankToNomenclature::find()->andWhere(['ob_id' => $blank->id])->count();

            /** @var int $max_order_time Максимальная дата доставки для продукта */
            $max_order_time = strtotime($blank->time_limit); //Максимальное дата и время, до которого можно совершить заказ
            if ($max_order_time < time()) {
                $max_order_time = $max_order_time + (60 * 60 * 24);
            }
           //Yii::debug('Максимальная дата заказа ' . date('d.m.Y H:i', $max_order_time), 'test');

            $delivery_date = date('Y-m-d', $max_order_time + ($blank->day_limit * 24 * 60 * 60));
            $delivery_time = strtotime($delivery_date);

           //Yii::debug('Дата заказа ' . date('d.m.Y', strtotime($target_date)), 'test');
           //Yii::debug('Мин. дата доставки ' . date('d.m.Y', $delivery_time), 'test');
           //Yii::debug('Расчетная дата доставки больше даты заказа: '
//                . (int)(strtotime($delivery_date) > strtotime($target_date)), 'test');

            if (strtotime($delivery_date) > strtotime($target_date)) {
                //Есил расчетная дата доставки больше даты, на которую заказывается продукты
                continue;
            }

            if ($delivery_time < strtotime($target_date)) {
                //Если продукты заказаны на более позднюю дату, чем расчетная
                $max_order_time = time();
            }

            $result .= '<tr>';
            $result .= '<td>';
            $result .= '<span class="fa fa-check text-success"></span> Бланк ' . $blank->number
                . ' продуктов <span class="count-products">' . $count_products . '</span>'
                . ", можно оформить <b>" . date('d.m.Y',
                    $max_order_time) . "</b> до <b>" . Yii::$app->formatter->asTime($blank->time_limit)
                . "</b>. Заказ будет доставлен <b>" . date('d.m.Y', strtotime($target_date)) . '</b><br>';
            $result .= '</td>';
            $result .= '</tr>';
            $blank_ids[] = $blank->id;
        }

        if (!$result) {
            return $result;
        }

        $table = '<table class="table table-bordered table-hover">';
        $table .= '<tbody>';
        $table .= $result;
        $table .= '</tbody>';
        $table .= '</table>';

        $hidden_input = Html::input('text', 'Order[blanks]', implode(',', $blank_ids), [
            'style' => 'display: none;'
        ]);


        return $table . $hidden_input;

    }

    /**
     * Проверяет существование бланка в айке
     * @return bool
     */
    public function blankExistsInIiko()
    {
        $helper = new IikoApiHelper();

        $params = [
            'from' => $this->date,
            'to' => $this->date,
            'number' => $this->number,
        ];

        $result = $helper->getOrderBlank($params);
       //Yii::debug($result, 'test');

        if ($result && isset($result['document']['id'])) {
            return true;
        }

        return false;
    }

    /**
     * Информация по всем бланкам заказа, с выводом минимаальной даты заказа для каждого бланка
     */
    public function getAllBlanksInfo()
    {
        $blanks = self::find()->all();
        $result = '';

        foreach ($blanks as $blank) {
            $buyers = ArrayHelper::map($blank->buyerToOrderBlanks, 'id', 'buyer_id');
            $user = Users::getUser();
           //Yii::debug($buyers, 'test');
           //Yii::debug('Buyer ID: ' . $user->buyer->id, 'test');
            if ($buyers && !in_array($user->buyer->id, $buyers)){
                //Если для бланка видимость только выбранным и покупателя нет в списке видимости
                continue;
            }

            $count_products = OrderBlankToNomenclature::find()->andWhere(['ob_id' => $blank->id])->count();

            /** @var int $max_order_time Максимальная дата доставки для продукта */
            $max_order_time = strtotime($blank->time_limit); //Максимальное дата и время, до которого можно совершить заказ
            if ($max_order_time < time()) {
                $max_order_time = $max_order_time + (60 * 60 * 24);
            }
           //Yii::debug('Максимальная дата заказа ' . date('d.m.Y H:i', $max_order_time), 'test');
            $delivery_date = date('Y-m-d', $max_order_time + ($blank->day_limit * 24 * 60 * 60));
            $delivery_time = strtotime($delivery_date);

           //Yii::debug('Мин. дата доставки ' . date('d.m.Y', $delivery_time), 'test');

            $result .= '<tr>';
            $result .= '<td>';
            $result .= '<span class="fa fa-check text-success"></span> Бланк ' . $blank->number
                . ' продуктов <span class="count-products">' . $count_products . '</span>'
                . ", можно оформить <b>" . date('d.m.Y',
                    $max_order_time) . "</b> до <b>" . Yii::$app->formatter->asTime($blank->time_limit)
                . "</b>. Заказ будет доставлен <b>" . date('d.m.Y', strtotime($delivery_date)) . '</b><br>';
            $result .= '</td>';
            $result .= '</tr>';
            $blank_ids[] = $blank->id;
        }

        $table = '<table class="table table-bordered table-hover">';
        $table .= '<tbody>';
        $table .= $result;
        $table .= '</tbody>';
        $table .= '</table>';

        return $table;
    }

    /**
     * Синхронизация цен для ценовых категорий тех продуктов которые есть в бланках заказа
     */
    public static function syncPriceForPriceCategory()
    {
        $target_product_outer_ids = Nomenclature::find()
            ->joinWith(['orderBlanksToNomenclatures'])
            ->select('outer_id')
            ->column();

        $helper = new PostmanApiHelper();
        $response = $helper->getPriceListItems();

        if (!$response['success']) return $response;

        $xml = simplexml_load_string($response['data']);

        foreach ($xml->returnValue->v as $item){
            if (in_array($item->product, $target_product_outer_ids)){
                $json = json_encode($item);
                $data = json_decode($json, true);
                $result_import = PriceCategoryToNomenclature::import($data);
               //Yii::debug($result_import, 'test');
            }
        }
        return true;

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuyerToOrderBlanks()
    {
        return $this->hasMany(BuyerToOrderBlank::class, ['order_blank_id' => 'id']);
    }

    /**
     * @return array
     */
    public function getProductDataProvider()
    {
        $obtns = $this->orderBlankToNomenclature;

        $products = [];

        /** @var OrderBlankToNomenclature $item */
        foreach ($obtns as $item) {
            $products[] = [
                'name' => $item->n->name,
                'measure' => $item->findMeasure(),
                'quantity' => $item->quantity,
            ];
        }

        return $products;
    }

    /**
     * @param string $date Дата
     * @return array
     * @throws InvalidConfigException
     */
    public static function getOrdersByDate(string $date): array
    {
        $model = new OrderBlank();

        if (!$date) {
            return [
                'success' => false,
                'error' => 'Не выбрана дата',
            ];
        }

        if (strtotime($date) < time()) {
            $data = $model->getAllBlanksInfo();
            return [
                'success' => false,
                'error' => 'Дата заказа уже наступила. Заказ невозможен',
                'warning' => $data
            ];
        }

        $target_date = date('Y-m-d', strtotime($date));

        $blanks = $model->getBlanksByDate($target_date);
        $data = $model->blanksToTable($blanks, $target_date);

        if (!$data) {
            $data = 'Бланки заказов отсуствуют';
        }

        return [
            'success' => true,
            'data' => $data,
        ];
    }
}
