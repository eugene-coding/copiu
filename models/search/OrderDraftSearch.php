<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\OrderDraft;

/**
 * OrderDraftSearch represents the model behind the search form about `app\models\OrderDraft`.
 */
class OrderDraftSearch extends OrderDraft
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'order_id'], 'integer'],
            [['plan_send_date', 'send_at', 'name'], 'safe'],
            ['target_date', 'safe'],
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
        $query = OrderDraft::find()->joinWith(['order']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'order_id' => $this->order_id,
            'plan_send_date' => $this->plan_send_date,
            'send_at' => $this->send_at,
            'order.target_date' => $this->target_date,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);


        return $dataProvider;
    }
}
