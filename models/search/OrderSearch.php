<?php

namespace app\models\search;

use app\models\Buyer;
use app\models\Users;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Order;

/**
 * OrderSearch represents the model behind the search form about `app\models\Order`.
 */
class OrderSearch extends Order
{
    /**
     * @inheritdoc
     */
    public function rules() :array
    {
        return [
            [['id', 'buyer_id', 'status'], 'integer'],
            [['created_at', 'target_date', 'delivery_time_from', 'delivery_time_to', 'comment', 'buyer_name'], 'safe'],
            [['total_price'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios(): array
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
    public function search(array $params): ActiveDataProvider
    {
        $query = Order::find();

        $query->andWhere(['<>', 'status', [Order::STATUS_ORDER_DRAFT, Order::STATUS_ORDER_WAITING]]);
        $query->orderBy(['status' => SORT_ASC, 'target_date' => SORT_DESC]);

        if (!Users::isAdmin()){
            /** @var Users $user */
            $user = Users::findOne(Yii::$app->user->id);
            $query->andWhere(['buyer_id' => $user->buyer->id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->joinWith(['buyer']);
        $dataProvider->sort->attributes['buyer_name'] = [
            'asc' => [Buyer::tableName() . '.name' => SORT_ASC],
            'desc' => [Buyer::tableName() . '.name' => SORT_DESC],
            'label' => 'Покупатель'
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->id){
            $this->id = (int)str_replace('N', '', $this->id);
        }

        $query->andFilterWhere([
            'order.id' => $this->id,
            'buyer_id' => $this->buyer_id,
            'created_at' => $this->created_at,
            'target_date' => $this->target_date,
            'delivery_time_from' => $this->delivery_time_from,
            'delivery_time_to' => $this->delivery_time_to,
            'total_price' => $this->total_price,
            'status' => $this->status,
        ]);
        $query->andFilterWhere(['like', 'buyer.name', $this->buyer_name]);


        $query->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
