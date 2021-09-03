<?php

namespace app\models;

use app\models\query\OrderDraftQuery;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_draft".
 *
 * @property int $id
 * @property int|null $order_id
 * @property string $plan_send_date
 * @property string|null $send_at
 * @property string $name Наименование
 *
 * @property Order $order
 */
class OrderDraft extends ActiveRecord
{

    public $target_date; //Для поиска
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_draft';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id'], 'integer'],
            [['plan_send_date', 'send_at'], 'safe'],
            [
                ['order_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Order::class,
                'targetAttribute' => ['order_id' => 'id']
            ],
            [['name'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'plan_send_date' => 'Планируемая дата отправки заказа',
            'send_at' => 'Время отправки заказа',
            'name' => 'Наименование',
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
     * {@inheritdoc}
     * @return OrderDraftQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrderDraftQuery(get_called_class());
    }

    /**
     * Проверка условий и постановка заказа в очередь
     * @param Order $order Заказ
     * @return array|bool
     */
    public function toQueue($order)
    {
        $blanks = explode(',', $order->blanks);
        Yii::debug($blanks, 'test');

        //Получаем максимальный лимит из всех бланков текущего заказа
        $day_limit = 0;
        foreach ($blanks as $blank_id){
            $blank = OrderBlank::findOne($blank_id);
            if (!$blank) continue;
            if ($day_limit < $blank->day_limit) $day_limit = $blank->day_limit;
        }
        Yii::debug('Day limit: ' . $day_limit, 'test');

        //Получаем минимальную дату (относительно целевой даты), раньше ее заказывать нельзя
        $min_date = date('Y-m-d', strtotime($order->target_date . ' -' . $day_limit .' day'));
        Yii::debug('Min date: ' . $min_date, 'test');

        if ($min_date <= date('Y-m-d', time())){
            $order->addError('target_date', 'Заказ на выбранную дату невозможен. Выберите более позднюю дату заказа');
            return [
                'success' => false,
                'error' => 'Заказ на выбранную дату невозможен. Выберите более позднюю дату заказа'
            ];
        }

        //Расчитываем общую сумму заказа
        $new_total_price = OrderToNomenclature::getTotalPrice($order->id);

        if ($new_total_price != $order->total_price){
            return [
                'success' => false,
                'error' => 'Черновик не актуален, т.к. изменились цены на продукты. Создайте новый черновик.'
            ];
        }

        //Устанавливаем дату отправки
        $this->plan_send_date = $min_date;

        return [
            'success' => true,
        ];

    }
}
