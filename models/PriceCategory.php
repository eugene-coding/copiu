<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "price_category".
 *
 * @property int $id
 * @property string|null $outer_id
 * @property string|null $name
 *
 * @property Buyer[] $buyers
 */
class PriceCategory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'price_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['outer_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'outer_id' => 'Внешний идентификатор',
            'name' => 'Наименование',
        ];
    }

    /**
     * Gets query for [[Buyers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBuyers()
    {
        return $this->hasMany(Buyer::class, ['pc_id' => 'id']);
    }

    public function sync($data)
    {
        $rows = [];
        $exist_category = PriceCategory::find()->select(['outer_id'])->column();
//        Yii::info($exist_category, 'test');

        foreach ($data as $pc){
            $outer_id = $pc['id'];
            if (!in_array($outer_id, $exist_category)){
                $name = $pc['r']['name']['customValue'];
                $rows[] = [$name, $outer_id];
            }
        }

        try {
            Yii::$app->db->createCommand()->batchInsert(PriceCategory::tableName(), ['name', 'outer_id'],
                $rows)->execute();
        } catch (Exception $e) {
            Yii::error($e->getMessage(), '_error');
        }


        return [
            'success' => true,
            'data' => 'Синхронизация ценовых категорий прошла успешно',
        ];
    }

    public static function getList ()
    {
        return ArrayHelper::map(static::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
    }
}
