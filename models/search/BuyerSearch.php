<?php

namespace app\models\search;

use app\models\Users;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Buyer;

/**
 * BuyerSearch represents the model behind the search form about `app\models\Buyer`.
 */
class BuyerSearch extends Buyer
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'pc_id', 'user_id'], 'integer'],
            [['name', 'outer_id'], 'safe'],
            [['work_mode'], 'integer'],
            [['min_order_cost', 'delivery_cost', 'discount', 'min_balance'], 'number'],
            [['user_login', 'user_password'], 'string']
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
        $query = Buyer::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->joinWith('user');

        $dataProvider->sort->attributes['user_login'] = [
            'asc' => [Users::tableName() . '.login' => SORT_ASC],
            'desc' => [Users::tableName() . '.login' => SORT_DESC],
            'label' => 'Логин'
        ];
        $dataProvider->sort->attributes['user_password'] = [
            'asc' => [Users::tableName() . '.open_pass' => SORT_ASC],
            'desc' => [Users::tableName() . '.open_pass' => SORT_DESC],
            'label' => 'Пароль'
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'pc_id' => $this->pc_id,
            'user_id' => $this->user_id,
            'work_mode' => $this->work_mode,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'outer_id', $this->outer_id])
            ->andFilterWhere(['like', 'users.login', $this->user_login])
            ->andFilterWhere(['like', 'users.open_pass', $this->user_password]);

        return $dataProvider;
    }
}
