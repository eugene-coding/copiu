<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\FavoriteProduct;

/**
 * FavoriteProductSearch represents the model behind the search form about `app\models\FavoriteProduct`.
 */
class FavoriteProductSearch extends FavoriteProduct
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'buyer_id', 'obtn_id', 'status'], 'integer'],
            [['count'], 'number'],
            [['note'], 'safe'],
            [['blank_id'], 'safe'],
            [['product_name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = FavoriteProduct::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

//        $dataProvider->sort->attributes['blank_id'] = [
//            'asc' => [Users::tableName() . '.login' => SORT_ASC],
//            'desc' => [Users::tableName() . '.login' => SORT_DESC],
//            'label' => 'Логин'
//        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'buyer_id' => $this->buyer_id,
            'obtn_id' => $this->obtn_id,
            'count' => $this->count,
            'status' => $this->status,
            'blank_id' => $this->blank_id,
        ]);

        $query->andFilterWhere(['like', 'note', $this->note]);

        return $dataProvider;
    }
}
