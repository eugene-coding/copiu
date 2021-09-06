<?php

namespace app\models;

use app\models\query\FavoriteProductQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "favorite_product".
 *
 * @property int $id
 * @property int|null $buyer_id Покупатель
 * @property int|null $obtn_id Связь бланка с продуктом
 * @property float|null $count Кол-во
 * @property int|null $status Статус. Активна/Не активна
 * @property string|null $note
 * @property int $blank_id Идентификатор бланка
 *
 * @property Buyer $buyer
 * @property OrderBlankToNomenclature $obtn
 * @property OrderBlank $blank
 */
class FavoriteProduct extends ActiveRecord
{

    public $blank_id;
    public $product_name;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'favorite_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['buyer_id', 'obtn_id', 'status'], 'integer'],
            [['count'], 'number'],
            [['note'], 'string'],
            [
                ['buyer_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Buyer::class,
                'targetAttribute' => ['buyer_id' => 'id']
            ],
            [
                ['obtn_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => OrderBlankToNomenclature::class,
                'targetAttribute' => ['obtn_id' => 'id']
            ],
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
            'obtn_id' => 'Связь бланка с продуктом',
            'count' => 'Кол-во',
            'status' => 'Статус. Активна/Не активна',
            'note' => 'Примечание',
            'blank_id' => 'Бланк',
            'product_name' => 'Наименование',
        ];
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
     * Gets query for [[Obtn]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getObtn()
    {
        return $this->hasOne(OrderBlankToNomenclature::class, ['id' => 'obtn_id']);
    }

    /**
     * {@inheritdoc}
     * @return FavoriteProductQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FavoriteProductQuery(get_called_class());
    }

    /**
     * Получает записи для покупателя
     * @param int $id Идентификатор покупателя
     * @return FavoriteProduct[]|array|null
     */
    public static function getListForBayer($id)
    {
        return self::find()
            ->andWhere(['buyer_id' => $id])
            ->all();
    }

    /**
     * Бланк для избранного продукта
     * @return \yii\db\ActiveQuery
     */
    public function getBlank()
    {
        return $this->hasOne(OrderBlank::class, ['id' => 'ob_id'])
            ->via('obtn');
    }

    public static function getBlanks()
    {
        $user = Users::getUser();

        $obtns = FavoriteProduct::find()
            ->select(['obtn_id'])
            ->andWhere(['buyer_id' => $user->buyer->id])
            ->active()
            ->column();

        $blanks = OrderBlank::find()
            ->joinWith('orderBlankToNomenclature')
            ->andWhere(['IN', 'order_blank_to_nomenclature.id', $obtns]);

        return ArrayHelper::map($blanks->all(), 'id', 'name');

    }
}
