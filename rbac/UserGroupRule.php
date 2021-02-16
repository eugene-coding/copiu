<?php

namespace app\rbac;


use app\models\Users;
use Yii;
use yii\rbac\Rule;

class UserGroupRule extends Rule
{
    public $name = 'userGroup';

    public function execute($user, $item, $params)
    {
        /** @var Users $identity */
        $identity =  Yii::$app->user->identity;
        if (!Yii::$app->user->isGuest) {
            $group = $identity->role;
            if ($item->name === 'admin') {
                return $group == 'admin';
            } elseif ($item->name === 'user') {
                return $group == 'admin' || $group == 'user';
            } elseif ($item->name === 'specialist') {
                return $group == 'admin' || $group == 'specialist';
            }
        }
        return true;
    }
}