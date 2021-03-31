<?php

namespace app\models\query;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\OrderBlank]].
 *
 * @see \app\models\OrderBlank
 */
class OrderBlankQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    public function allowed()
    {
       return $this;
    }

    /**
     * {@inheritdoc}
     * @return \app\models\OrderBlank[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\OrderBlank|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
