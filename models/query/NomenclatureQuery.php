<?php

namespace app\models\query;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\Nomenclature]].
 *
 * @see \app\models\Nomenclature
 */
class NomenclatureQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\Nomenclature[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Nomenclature|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
