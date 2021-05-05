<?php

namespace app\models;

use app\components\IikoApiHelper;
use app\models\query\NomenclatureQuery;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "nomenclature".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $outer_id
 * @property string|null $num Артикул
 * @property int|null $n_group_id Номенклатурная группа
 * @property int|null $measure_id Единица измерения
 * @property float|null $default_price Цена по умолчанию
 * @property string|null $unit_weight Вес одной единицы
 * @property string|null $unit_capacity Объём одной единицы
 * @property string|null $type Тип
 * @property array|null $price Цена товара
 * @property string $main_unit Параметр, для акта услуг
 *
 * @property Measure $measure
 * @property NGroup $nGroup
 * @property PriceCategoryToNomenclature[] $priceCategoryToNomenclatures
 * @property OrderBlank[] $orderBlanks
 * @property OrderToNomenclature[] $orderToNomenclature
 * @property double $priceForBuyer
 * @property Order[] $orders
 * @property Container[] $containers
 * @property OrderBlankToNomenclature[] $orderBlanksToNomenclatures
 */
class Nomenclature extends ActiveRecord
{

    public $count;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nomenclature';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'main_unit'], 'string'],
            [['n_group_id', 'measure_id'], 'integer'],
            [['default_price', 'unit_weight', 'unit_capacity'], 'number'],
            [['name', 'outer_id', 'num', 'type'], 'string', 'max' => 255],
            [
                ['measure_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Measure::class,
                'targetAttribute' => ['measure_id' => 'id']
            ],
            [
                ['n_group_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => NGroup::class,
                'targetAttribute' => ['n_group_id' => 'id']
            ],
            [['outer_id'], 'unique'],
            ['count', 'safe'],
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
            'description' => 'Описание',
            'outer_id' => 'Внещний идентификатор',
            'num' => 'Артикул',
            'n_group_id' => 'Номенклатурная группа',
            'measure_id' => 'Единица измерения',
            'default_price' => 'Цена по умолчанию',
            'unit_weight' => 'Вес одной единицы',
            'unit_capacity' => 'Объём одной единицы',
            'type' => 'Тип',
            'count' => 'Кол-во',
        ];
    }

    /**
     * Gets query for [[Measure]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeasure()
    {
        return $this->hasOne(Measure::class, ['id' => 'measure_id']);
    }

    /**
     * Gets query for [[Container]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContainers()
    {
        return $this->hasMany(Container::class, ['nomenclature_id' => 'id']);
    }

    /**
     * Gets query for [[NGroup]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\NGroupQuery
     */
    public function getNGroup()
    {
        return $this->hasOne(NGroup::className(), ['id' => 'n_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getOrderBlanks()
    {
        return $this->hasMany(OrderBlank::class, ['id' => 'ob_id'])
            ->viaTable(OrderBlankToNomenclature::tableName(), ['n_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderToNomenclature()
    {
        return $this->hasMany(OrderToNomenclature::class, ['nomenclature_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderBlanksToNomenclatures()
    {
        return $this->hasMany(OrderBlankToNomenclature::class, ['n_id' => 'id']);
    }

    /**
     * Gets query for [[PriceCategoryToNomenclatures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPriceCategoryToNomenclatures()
    {
        return $this->hasMany(PriceCategoryToNomenclature::className(), ['n_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return NomenclatureQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new NomenclatureQuery(get_called_class());
    }

    /**
     * Импорт номенклатуры
     * @param $data
     * @return array
     */
    public static function import($data)
    {
        $item_exist = Nomenclature::find()->select(['outer_id'])->column();
        $n_groups = [];
        $allowed_types = [
            'GOODS',
            'DISH',
            'PREPARED',
            'MODIFIER',
        ];
        $added_items = 0;
        $updated_items = 0;
        $skipped = 0;
        $message = '';
        /** @var array $containers [<UIID> => [[<Контайнер1>], [<Контайнер 2>]] */
        $containers = [];

        /** @var NGroup $group */
        foreach (NGroup::find()->each() as $group) {
            $n_groups[$group->outer_id] = $group->id;
        }

        $measures = ArrayHelper::map(Measure::find()->all(), 'outer_id', 'id');

        foreach ($data as $item) {
            if ($item['type'] && !in_array($item['type'], $allowed_types)) {
                $skipped++;
                continue;
            }

            if (in_array($item['id'], $item_exist)) {
                $model = Nomenclature::findOne(['outer_id' => $item['id']]);
                $updated_items++;
            } else {
                $model = new Nomenclature();
                $added_items++;
            }

            $n_group_id = $n_groups[$item['parent']];
            if (!$n_group_id && $item['parent']) {
                //Если группы нет в базе
                //(например если синхронизация номенклатуры запущена до синхронизации групп номенклатуры)
                //Добавляем группу
                $n_group_model = new NGroup([
                    'outer_id' => $item['parent'],
                ]);
                if (!$n_group_model->save()) {
                    Yii::error($n_group_model->errors, '_error');
                }
                $message = '<b>Синхронизируйте группы номенклатуры!</b>';
            }
            //Единица измерения
            $measure_outer_id = $item['mainUnit'];
            $model->measure_id = $measures[$measure_outer_id];
            $model->name = $item['name'];
            $model->description = $item['description'] ?: '';
            $model->outer_id = $item['id'];
            $model->num = $item['num'] ?: 0;
            $model->n_group_id = $n_group_id;
            $model->default_price = $item['defaultSalePrice'] ?: 0;
            $model->unit_weight = $item['unitWeight'] ?: 0;
            $model->unit_capacity = $item['unitCapacity'] ?: 0;
            $model->type = $item['type'];
            $model->main_unit = $item['mainUnit'] ?: null;
//            Yii::info($model->attributes, 'test');

            if (!$model->save()) {
                Yii::error($model->errors, '_error');
            } else {
                if ($item['containers']) {
                    $containers[$model->id] = $item['containers'];
                }
            }
        }
        Yii::info($containers, 'test');
        $actual_container_ids = [];

        //Обновляем контейнеры
        foreach ($containers as $nomenclature_id => $arr_containers) {
            foreach ($arr_containers as $item) {
                $container_model = Container::findOne($item['id']);

                if (!$container_model){
                    $container_model = new Container([
                        'id' => $item['id'],
                        'nomenclature_id' => $nomenclature_id,
                    ]);
                }
                $container_model->name = $item['name'];
                $container_model->count = $item['count'];
                $container_model->weight = $item['containerWeight'];
                $container_model->full_weight = $item['fullContainerWeight'];
                $container_model->deleted = $item['deleted'];

                if ($container_model->id){
                    $actual_container_ids[] = $container_model->id;
                }

                if (!$container_model->save()){
                    Yii::error($container_model->errors, '_error');
                }
            }
        }

        //Удаляем контейнеры, которые не пришли в ответе (например контейнеры, которые удалены)
        $current_container_ids = Container::find()->select(['id'])->column();
        $container_for_delete = array_diff($current_container_ids, $actual_container_ids);

        Yii::warning('Контейнеры для удаления: ' . implode(',', $container_for_delete));

        Container::deleteAll(['IN', 'id', $container_for_delete]);

        return [
            'success' => true,
            'data' => 'Синхронизация завершена<br>'
                . 'Добавлено записей: ' . $added_items . '<br>'
                . 'Обновлено записей: ' . $updated_items . '<br>'
                . 'Пропущено: ' . $skipped . '<br>'
                . $message
        ];
    }

    /**
     * Типы елементов номенклатуры
     * https://ru.iiko.help/articles/#!api-documentations/base-types
     * параграф "Тип элемента номенклатуры"
     * @return array
     */
    public static function getTypeList()
    {
        return [
            'GOODS' => 'Товар',
            'DISH' => 'Блюдо',
            'PREPARED' => 'Заготовка',
            'SERVICE' => 'Услуга',
            'MODIFIER' => 'Модификатор',
            'OUTER' => 'Внешние товары',
            'PETROL' => 'Топливо',
            'RATE' => 'Тариф',
        ];
    }

    /**
     * Получает цену за единицу продукта для покупателя
     * @param string|null $container_id Контейнер
     * @return float
     */
    public function getPriceForBuyer($container_id = null)
    {
        /** @var Users $user */
        $user = Yii::$app->user->identity;
        $buyer = $user->buyer;
        /** @var double $count Кол-во продукта (для контейнера) */
        $count = 1;

        //Проверяем кол-во (если контейнер)
        if ($container_id){
            $container = Container::findOne($container_id);
            $count = $container->count;
        }

        if (!$buyer->pc_id) {
            return $buyer->calcOnDiscount($this->default_price) * $count;
        }

        //Проверяем цену ценовой категории
        /** @var PriceCategoryToNomenclature $pc_t_n */
        $pc_t_n = PriceCategoryToNomenclature::find()
            ->andWhere(['pc_id' => $buyer->pc_id, 'n_id' => $this->id])
            ->one();

        if (!$pc_t_n) {
            return $buyer->calcOnDiscount($this->default_price) * $count;
        }

        return $buyer->calcOnDiscount($pc_t_n->price) * $count;
    }


    /**
     * Цена продукта для заказа
     * @param $order_id
     * @return float|null
     */
    public function getPriceForOrder($order_id)
    {
        /** @var OrderToNomenclature $query */
        $query = OrderToNomenclature::find()->andWhere([
            'order_id' => $order_id,
            'nomenclature_id' => $this->id
        ])->one();

        return $query->price;
    }

    /**
     * Колво продуктов в заказе
     * @param $order_id
     * @return mixed
     */
    public function getCount($order_id)
    {
        /** @var OrderToNomenclature $query */
        $query = OrderToNomenclature::find()
            ->andWhere(['nomenclature_id' => $this->id, 'order_id' => $order_id])
            ->one();

        return $query->count;
    }

    /**
     * Заказы для позиции номенклатуры
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['id' => 'order_id'])
            ->via('orderToNomenclature');
    }

    /**
     * @param array $ids массив UIID продуктов
     * @return bool
     */
    public static function syncByIds($ids)
    {
        $helper = new IikoApiHelper();
        $outer_products = $helper->getItemsById($ids);
//        Yii::info($outer_products, 'test');

        $products = ArrayHelper::map(self::find()
            ->andWhere(['IN', 'outer_id', $ids])->all(), 'outer_id', 'id');

        foreach ($outer_products as $product) {
            $nom_id = $products[$product['id']];

            $n_position = Nomenclature::findOne($nom_id);
            if (!$n_position) {
                continue;
            }

            $n_position->default_price = $product['defaultSalePrice'];
            $n_position->main_unit = $product['mainUnit'];

            if (!$n_position->save()) {
                Yii::error($n_position->errors, '_error');
            }

            $containers = isset($product['containers']) ? $product['containers'] : null;
            if ($containers) {
                //Обновляем контейнер(ы)
                Container::sync($containers, $nom_id);
            } else {
                //Удаляем все контейнеры для продукта
                Container::deleteAll(['nomenclature_id' => $nom_id]);
            }
        }
        Yii::info('Обновление номенклатуры. Ок', 'test');

        return true;
    }

    /**
     * Возвращает модель контейнера по UIID
     * @param string $container_id UIID контейнера
     * @return array|ActiveRecord
     */
    public function getContainerById($container_id)
    {
        return $this->getContainers()->andWhere(['id' => $container_id])->one();
    }

    /**
     * Получает единицу измерения или название контейнера (при наличии) для продукта
     * @param OrderBlankToNomenclature $order_blank_to_nomenclature Связ бланка с продуктом
     * @return null|string
     */
    public function findMeasure(OrderBlankToNomenclature $order_blank_to_nomenclature)
    {
        $container = $order_blank_to_nomenclature->container;

        if ($container){
            return $container->name;
        } else {
            return $this->measure->name;
        }
    }

}
