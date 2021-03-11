<?php

namespace app\models;

use app\components\IkkoApiHelper;
use app\models\query\OrderBlankQuery;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_blank".
 *
 * @property int $id
 * @property string|null $number Номер накладной
 * @property string|null $date Дата накладной
 * @property int|null $time_limit Ограничение по времени
 * @property int|null $day_limit Ограничение по дням
 * @property string|null $synced_at Дата и время синхронизации
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

            if (!$data){
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

                    if (!$model->save()){
                        Yii::error($model->errors, '_error');
                    }
                } else {
                    Yii::warning('Продукт ' . $item['productId'] . ' не найден в номенклатуре, пропускаем', 'test');
                }
            }
        }

        return [
            'success' => true,
            'data' => 'Синхронизация завершена'
        ];
    }
}
