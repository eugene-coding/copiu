<?php

namespace app\models\query;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\NGroup]].
 *
 * @see \app\models\NGroup
 */
class NGroupQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * Только родительские категории
     * {@inheritdoc}
     * @return NGroupQuery
     */
    public function parents()
   {
        return $this->andWhere(['IS', 'parent_id', null]);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\NGroup[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\NGroup|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
