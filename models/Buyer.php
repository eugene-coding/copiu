<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "buyer".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $pc_id Ценовая категория
 * @property int|null $user_id Пользователь системы
 * @property string|null $outer_id Внешний идентификатор
 *
 * @property PriceCategory $pc
 * @property Users $user
 */
class Buyer extends ActiveRecord
{
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
            [['pc_id'], 'exist', 'skipOnError' => true, 'targetClass' => PriceCategory::className(), 'targetAttribute' => ['pc_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'id']],
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
        ];
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

        $price_categories = ArrayHelper::map(PriceCategory::find()->all(), 'outer_id', 'id' );

        foreach ($data as $buyer){
            $outer_id = $buyer['id'];
            if (!in_array($outer_id, $exists_buyer)){
                $name = $buyer['r']['name']['customValue'];
                $outer_price_category = $buyer['r']['priceCategory'][0];
                if ($outer_price_category){
                    $price_category = $price_categories[$outer_price_category];
                } else {
                    $price_category = null;
                }
                $rows[] = [$name, $price_category, $outer_id];
            }
        }

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
}
