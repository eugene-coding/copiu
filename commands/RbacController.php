<?php

namespace app\commands;


use Yii;
use yii\console\Controller;
use \app\rbac\UserGroupRule;
use \app\rbac\UserProfileOwnerRule;

class RbacController extends Controller
{
    /**
     * @throws \Exception
     */
    public function actionInit()
    {
        $authManager = Yii::$app->authManager;
        $authManager->removeAll();

        //Создаем роли

        $guest = $authManager->createRole('guest');
        $guest->description = 'Гость';
        $user = $authManager->createRole('user');
        $user->description = 'Пользователь';
        $specialist = $authManager->createRole('specialist');
        $specialist->description = 'Специалист';
        $admin = $authManager->createRole('admin');
        $admin->description = 'Администратор';

        //Создаем разрешения, основанные на имени экшена
        $login = $authManager->createPermission('login');
        $logout = $authManager->createPermission('logout');
        $error = $authManager->createPermission('error');
        $sign_up = $authManager->createPermission('sign-up');
        $index = $authManager->createPermission('index');
        $view = $authManager->createPermission('view');
        $create = $authManager->createPermission('create');
        $update = $authManager->createPermission('update');
        $delete = $authManager->createPermission('delete');
        $profile = $authManager->createPermission('profile');


        //Добавляем разрешения в AuthManager

        $authManager->add($login);
        $authManager->add($logout);
        $authManager->add($error);
        $authManager->add($sign_up);
        $authManager->add($index);
        $authManager->add($view);
        $authManager->add($create);
        $authManager->add($update);
        $authManager->add($delete);
        $authManager->add($profile);

        //Добавляем правила, основанные на UserExt->group === $user->group
        $userGroupRule = new UserGroupRule();
        $authManager->add($userGroupRule);

        //Добавляем правила UserGroupRule в роли
        $guest->ruleName = $userGroupRule->name;
        $user->ruleName = $userGroupRule->name;
        $specialist->ruleName = $userGroupRule->name;
        $admin->ruleName = $userGroupRule->name;

        //Добавляем роли в Yii::$app->authManager
        $authManager->add($guest);
        $authManager->add($user);
        $authManager->add($specialist);
        $authManager->add($admin);

        //Добавляем разрешения для роли в Yii::$app->authManager

        //Guest
        $authManager->addChild($guest, $login);
        $authManager->addChild($guest, $error);
        $authManager->addChild($guest, $sign_up);
        $authManager->addChild($guest, $index);
        $authManager->addChild($guest, $view);

        //User
        $authManager->addChild($user, $update);
        $authManager->addChild($user, $create);
        $authManager->addChild($user, $logout);
        $authManager->addChild($user, $profile);
        $authManager->addChild($user, $guest);

        //Specialist
        $authManager->addChild($specialist, $update);
        $authManager->addChild($specialist, $create);
        $authManager->addChild($specialist, $logout);
        $authManager->addChild($specialist, $profile);
        $authManager->addChild($specialist, $guest);

        //Admin
        $authManager->addChild($admin, $delete);
        $authManager->addChild($admin, $user);
        $authManager->addChild($admin, $specialist);

        //Добавляем правило, запрещающее редактировать чужой профиль
        $userProfileOwnerRule = new UserProfileOwnerRule();
        $authManager->add($userProfileOwnerRule);

        $updateOwnProfile = $authManager->createPermission('updateOwnProfile');
        $updateOwnProfile->ruleName = $userProfileOwnerRule->name;
        $authManager->add($updateOwnProfile);

        $authManager->addChild($user, $updateOwnProfile);
        $authManager->addChild($specialist, $updateOwnProfile);


    }
}