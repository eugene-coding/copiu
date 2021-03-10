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

    /**
     * @param $data
     * @return array
     */
    public function sync($data)
    {
        $updated = 0;

        $rows = [];
        $exist_category = PriceCategory::find()->select(['outer_id'])->column();
//        Yii::info($exist_category, 'test');
        $new_categories = [];

        foreach ($data as $pc) {
            $outer_id = $pc['id'];
            $new_categories[] = $outer_id;
            $name = $pc['r']['name']['customValue'];
            if (!in_array($outer_id, $exist_category)) {
                $rows[] = [$name, $outer_id];
            } else {
                /** @var PriceCategory $pc_model */
                $pc_model = PriceCategory::find()->andWhere(['outer_id' => $outer_id])->one();
                $pc_model->name = $name;
                if (!$pc_model->save()) {
                    Yii::error($pc_model->errors, '_error');
                }
                $updated++;
            }
        }

        try {
            Yii::$app->db->createCommand()->batchInsert(PriceCategory::tableName(), ['name', 'outer_id'],
                $rows)->execute();
        } catch (Exception $e) {
            Yii::error($e->getMessage(), '_error');
        }

        //Удаляем лишние
        $cat_for_delete = array_diff($exist_category, $new_categories);

        Yii::warning('Категории для удаления: ' . implode(', ', $cat_for_delete), 'test');

        if ($cat_for_delete){
            PriceCategory::deleteAll(['IN', 'outer_id', $cat_for_delete]);
        }
        $deleted = count($cat_for_delete) > 0 ? 'Удалено: ' . count($cat_for_delete) : '';
        $cat_insert = count($rows) > 0 ? 'Добавлено: ' . count($rows) : '';
        $updated = $updated > 0 ? 'Обнволено: ' . $updated : '';
        return [
            'success' => true,
            'data' => 'Синхронизация ценовых категорий прошла успешно.<br>'
                . $cat_insert
                . $updated
                . $deleted,
        ];
    }

    public static function getList()
    {
        return ArrayHelper::map(static::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
    }
}
