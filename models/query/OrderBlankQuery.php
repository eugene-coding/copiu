<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\OrderBlank]].
 *
 * @see \app\models\OrderBlank
 */
class OrderBlankQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

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
