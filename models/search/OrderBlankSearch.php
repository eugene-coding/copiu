<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\OrderBlank;

/**
 * OrderBlankSearch represents the model behind the search form about `app\models\OrderBlank`.
 */
class OrderBlankSearch extends OrderBlank
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'time_limit', 'day_limit', 'show_to_all'], 'integer'],
            [['number', 'date', 'synced_at'], 'safe'],
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
        $query = OrderBlank::find();

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
            'date' => $this->date,
            'time_limit' => $this->time_limit,
            'day_limit' => $this->day_limit,
            'synced_at' => $this->synced_at,
            'show_to_all' => $this->show_to_all,
        ]);

        $query->andFilterWhere(['like', 'number', $this->number]);

        return $dataProvider;
    }
}
