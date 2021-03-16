<?php

namespace app\models;

use app\components\IkkoApiHelper;
use app\models\query\OrderBlankQuery;
use Yii;
use yii\db\ActiveRecord;
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
 *
 * @property Nomenclature[] $products Продукты в из накладной
 */
class OrderBlank extends ActiveRecord
{
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
            [['day_limit'], 'integer'],
            [['number'], 'string', 'max' => 255],
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
     * Синхронизация всех накладных
     */
    public static function sync()
    {
        $helper = new IkkoApiHelper();
        $result = [];
        foreach (static::find()->all() as $blank) {
            $params = [
                'number' => $blank->number,
                'from' => $blank->date,
                'to' => $blank->date,
            ];
            $response = $helper->getOrderBlank($params);
            Yii::info($response, 'test');

//            $path = 'uploads/order_blank.xml';
//            $str1 = simplexml_load_file($path);
//            $arr = json_decode(json_encode($str1), true);
//
//            \Yii::info($arr, 'test');


            $result[$blank->id] = $response;
        }
        Yii::info($result, 'test');

        foreach ($result as $blank_id => $data) {
            $blank_model = OrderBlank::findOne($blank_id);
            $number = $blank_model->number;
            $date = Yii::$app->formatter->asDate($blank_model->date);

            if (!$data) {
                return [
                    'success' => false,
                    'error' => "Ошибка синхронизации. Для накладной № {$number} от {$date} не получена информация",
                ];
            }
            foreach ($data['document']['items'] as $item) {
                $n_id = Nomenclature::find()->andWhere(['outer_id' => $item['productId']])->one()->id;

                if ($n_id) {
                    $model = new OrderBlankToNomenclature([
                        'n_id' => $n_id,
                        'ob_id' => $blank_id,
                    ]);

                    if (!$model->save()) {
                        Yii::error($model->errors, '_error');
                    }
                } else {
                    Yii::warning('Продукт ' . $item['productId'] . ' не найден в номенклатуре, пропускаем', 'test');
                }
                //Пишем время синхронизации
                $blank_model->synced_at = date('Y-m-d H:i:s', time());
                if ($blank_model->save()) {
                    Yii::error($blank_model->errors, '_error');
                }
            }
        }

        return [
            'success' => true,
            'data' => 'Синхронизация завершена'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getProducts()
    {
        return $this->hasMany(Nomenclature::class, ['id' => 'ob_id'])
            ->viaTable(OrderBlankToNomenclature::tableName(), ['n_id' => 'id']);
    }

    /**
     * Получает бланки заказов на дату
     * @param string $date Дата на которую производится заказ (Y-m-d)
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getBlanksByDate($date)
    {
        $date = date('Y-m-d 00:00:00', strtotime($date));
        $target_date = date('Y-m-d 23:59:59');

        $diff_time = strtotime($date) - strtotime($target_date);
        $diff_days = floor($diff_time / (60 * 60 * 24) + 1);

        Yii::info('Diff days: ' . $diff_days, 'test');

        $blanks = self::find()
            ->andWhere(['<=', 'day_limit', $diff_days])
            ->all();

        return $this->blanksToTable($blanks, $date);
    }

    /**
     * @param OrderBlank[] $blanks
     * @param string $target_date
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    private function blanksToTable($blanks, $target_date)
    {
        $result = '';
        $blank_ids = [];
        /** @var OrderBlank $blank */
        foreach ($blanks as $blank) {
            $products = $blank->products;
            $count_products = count($products) ? count($products) : 0;
            /** @var int $max_order_time Максимальная дата доставки для продукта */
            $max_order_time = strtotime($blank->time_limit); //Максимальное дата и время, до которого можно совершить заказ
            if ($max_order_time < time()) {
                $max_order_time = $max_order_time + (60 * 60 * 24);
            }
            Yii::info('Максимальная дата заказа ' . date('d.m.Y H:i', $max_order_time), 'test');

            $delivery_date = date('Y-m-d', $max_order_time + ($blank->day_limit * 24 *60 * 60));
            $delivery_time = strtotime($delivery_date);

            Yii::info('Дата заказа ' . date('d.m.Y', strtotime($target_date)), 'test');
            Yii::info('Дата доставки ' . date('d.m.Y', $delivery_time), 'test');
            Yii::info('Расчетная дата доставки больше даты заказа: '
                . (int)(strtotime($delivery_date) >  strtotime($target_date)), 'test');

            if (strtotime($delivery_date) >  strtotime($target_date)){
                //Есил расчетная дата доставки больше даты, на которую заказывается продукты
                continue;
            }

            if ($delivery_time < strtotime($target_date)){
                //Если продукты заказаны на более позднюю дату, чем расчетная
                $max_order_time = time();
            }

            $result .= '<tr>';
            $result .= '<td>';
            $result .= '<span class="fa fa-check text-success"></span> Бланк ' . $blank->number . ' продуктов ' . $count_products
                . ", можно оформить <b>" . date('d.m.Y', $max_order_time) . "</b> до <b>" . Yii::$app->formatter->asTime($blank->time_limit)
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
}
