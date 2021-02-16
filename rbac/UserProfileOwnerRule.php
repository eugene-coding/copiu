<?php

namespace app\rbac;

use Yii;
use yii\rbac\Item;
use yii\rbac\Rule;

class UserProfileOwnerRule extends Rule
{
    public $name = 'isProfileOwner';

    /**
     * @param string|integer $user   the user ID.
     * @param Item           $item   the role or permission that this rule is associated with
     * @param array          $params parameters passed to ManagerInterface::checkAccess().
     *
     * @return boolean a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        Yii::warning($user, 'test');
        Yii::warning($item, 'test');
        Yii::warning($params, 'test');

        if (Yii::$app->user->identity->role == 'admin') {
            return true;
        }
        return isset($params['profileId']) ? Yii::$app->user->id == $params['profileId'] : false;
    }
}