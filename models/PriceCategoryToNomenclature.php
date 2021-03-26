<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "price_category_to_nomenclature".
 *
 * @property int $id
 * @property int|null $pc_id Ценовая категория
 * @property int|null $n_id Продукт (позиция номенклатуры)
 * @property float|null $price Цена продукта для ценовой группы
 *
 * @property Nomenclature $nomenclature
 * @property PriceCategory $pc
 */
class PriceCategoryToNomenclature extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'price_category_to_nomenclature';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pc_id', 'n_id'], 'integer'],
            [['price'], 'number'],
            [
                ['n_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Nomenclature::class,
                'targetAttribute' => ['n_id' => 'id']
            ],
            [
                ['pc_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => PriceCategory::class,
                'targetAttribute' => ['pc_id' => 'id']
            ],
            [['pc_id'], 'unique', 'targetAttribute' => ['pc_id', 'n_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pc_id' => 'Ценовая категория',
            'n_id' => 'Продукт (позиция номенклатуры)',
            'price' => 'Цена продукта для ценовой группы',
        ];
    }

    /**
     * Gets query for [[N]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNomenclature()
    {
        return $this->hasOne(Nomenclature::class, ['id' => 'n_id']);
    }

    /**
     * Gets query for [[Pc]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPc()
    {
        return $this->hasOne(PriceCategory::class, ['id' => 'pc_id']);
    }

    public static function sync($data)
    {
        $skipped = 0;
        $added = 0;
        $errors = 0;
        $changed = 0;

        $xml = simplexml_load_string($data);
        $items = $xml->returnValue;
        $categories_in_base = ArrayHelper::map(PriceCategory::find()->all(), 'outer_id', 'id');
        $products_in_base = ArrayHelper::map(Nomenclature::find()->all(), 'outer_id', 'id');
        $base_cat_outer_ids = array_keys($categories_in_base);
        $base_product_outer_ids = array_keys($products_in_base);

        foreach ($items->v as $item) {
            $product_outer_id = (string)$item->i->product;

            if (!$product_outer_id) {
                Yii::info('Нет ID продукта. Пропускаем', 'test');
                $skipped++;
                continue;
            }

            Yii::info('Product outer ID: ' . $product_outer_id, 'test');
            $categories_and_prices = [];
            if ($item->i->pricesForCategories) {
                $categories_and_prices = json_decode(json_encode($item->i->pricesForCategories), true);
            }
//            Yii::info($categories_and_prices, 'test');

            $categories_prep = isset($categories_and_prices['k']) ? $categories_and_prices['k'] : null;
            if (!is_array($categories_prep)) {
                $categories[] = (string)$categories_prep;
            } else {
                $categories = $categories_prep;
            }

            if (!$categories) {
                Yii::info('Нет категорий. Пропускаем', 'test');
                $skipped++;
                continue;
            }
            $prices_prep = isset($categories_and_prices['v']) ? $categories_and_prices['v'] : null;;
            if (!is_array($prices_prep)) {
                $prices[] = (string)$prices_prep;
            } else {
                $prices = $prices_prep;
            }


            if ($categories) {
//                Yii::info($categories, 'test');
                for ($i = 0; $i < count($categories); $i++) {
//                    $category = PriceCategory::findOne(['outer_id' => $categories[$i]]);
                    $cat_outer_id = $categories[$i];
                    /** @var array $base_cat_outer_ids Внешине ключи категорий в базе */

                    if (!in_array($cat_outer_id, $base_cat_outer_ids)) {
                        Yii::info('Категория не найдена. Пропускаем', 'test');
                        continue;
                    }

                    $price = $prices[$i];
//                    $product = Nomenclature::findOne(['outer_id' => $product_outer_id]);

                    if (!in_array($product_outer_id, $base_product_outer_ids)) {
                        Yii::info('Продукт не найден. Пропускаем', 'test');
                        continue;
                    }

                    $model = new PriceCategoryToNomenclature([
                        'pc_id' => $categories_in_base[$cat_outer_id],
                        'n_id' => $base_product_outer_ids[$product_outer_id],
                    ]);

                    $exists = PriceCategoryToNomenclature::find()
                        ->andWhere([
                            'pc_id' => $categories_in_base[$cat_outer_id],
                            'n_id' => $base_product_outer_ids[$product_outer_id]
                        ])->exists();

                    if ($exists) {
                        Yii::info($model->errors, 'test');
                        $model = PriceCategoryToNomenclature::find()
                            ->andWhere(['pc_id' => $model->pc_id, 'n_id' => $model->n_id])->one();
                    }

                    $model->price = $price;

                    if ($model->isNewRecord) {
                        $added++;
                    } else {
                        $changed++;
                    }

                    if (!$model->save()) {
                        Yii::error($model->errors, '_error');
                        $errors++;
                    }
                }
            }

        }

        $data = 'Синхронизация цен прошла успешно<br>';

        if ($errors) {
            $data .= 'Ошибок: ' . $errors . '<br>';
        }
        if ($skipped) {
            $data .= 'Пропущено: ' . $skipped . '<br>';
        }

        if ($added) {
            $data .= 'Добавлено: ' . $added . '<br>';
        }

        if ($changed) {
            $data .= 'Изменено: ' . $changed . '<br>';
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }
}
