<?php

namespace app\models\query;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\Container]].
 *
 * @see \app\models\Container
 */
class ContainerQuery extends ActiveQuery
{
    public function forProduct($id)
    {
        return $this->andWhere(['nomenclature_id' => $id]);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Container[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Container|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
