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
        $buyer = $authManager->createRole('buyer');
        $buyer->description = 'Покупатель';
        $supplier = $authManager->createRole('supplier');
        $supplier->description = 'Поставщик';
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
        $test = $authManager->createPermission('test');
        $syncing = $authManager->createPermission('syncing');
        $syncBuyer = $authManager->createPermission('sync-buyer');
        $syncPriceCategory = $authManager->createPermission('sync-price-category');
        $syncAll = $authManager->createPermission('sync-all');
        $syncNom = $authManager->createPermission('sync-nomenclature');
        $syncNomGroup = $authManager->createPermission('sync-nomenclature-group');
        $getPriceForPC = $authManager->createPermission('get-price-for-price-category');
        $syncPriceForPC = $authManager->createPermission('sync-price-for-price-category');
        $syncBuyerBalances = $authManager->createPermission('sync-buyer-balances');
        $getOrdersByDate = $authManager->createPermission('get-orders-by-date');
        $getNomenclature = $authManager->createPermission('get-nomenclature');
        $sysInfo = $authManager->createPermission('system-info');
        $showErrors = $authManager->createPermission('show-errors');
        $showOrderErrorSettings = $authManager->createPermission('show-order-error-settings');


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
        $authManager->add($test);
        $authManager->add($syncing);
        $authManager->add($syncBuyer);
        $authManager->add($syncPriceCategory);
        $authManager->add($syncAll);
        $authManager->add($syncNom);
        $authManager->add($syncNomGroup);
        $authManager->add($getPriceForPC);
        $authManager->add($syncPriceForPC);
        $authManager->add($syncBuyerBalances);
        $authManager->add($getOrdersByDate);
        $authManager->add($getNomenclature);
        $authManager->add($sysInfo);
        $authManager->add($showErrors);
        $authManager->add($showOrderErrorSettings);

        //Добавляем правила, основанные на UserExt->group === $user->group
        $userGroupRule = new UserGroupRule();
        $authManager->add($userGroupRule);

        //Добавляем правила UserGroupRule в роли
        $guest->ruleName = $userGroupRule->name;
        $buyer->ruleName = $userGroupRule->name;
        $supplier->ruleName = $userGroupRule->name;
        $admin->ruleName = $userGroupRule->name;

        //Добавляем роли в Yii::$app->authManager
        $authManager->add($guest);
        $authManager->add($buyer);
        $authManager->add($supplier);
        $authManager->add($admin);

        //Добавляем разрешения для роли в Yii::$app->authManager

        //Guest
        $authManager->addChild($guest, $login);
        $authManager->addChild($guest, $error);
        $authManager->addChild($guest, $sign_up);
        $authManager->addChild($guest, $index);
        $authManager->addChild($guest, $view);
        $authManager->addChild($guest, $getNomenclature);
        $authManager->addChild($guest, $syncNom);
        $authManager->addChild($guest, $syncPriceForPC);

        //Покупатель
        $authManager->addChild($buyer, $update);
        $authManager->addChild($buyer, $create);
        $authManager->addChild($buyer, $logout);
        $authManager->addChild($buyer, $profile);
        $authManager->addChild($buyer, $guest);
        $authManager->addChild($buyer, $getOrdersByDate);
        $authManager->addChild($buyer, $showOrderErrorSettings);

        //Admin
        $authManager->addChild($admin, $delete);
        $authManager->addChild($admin, $test);
        $authManager->addChild($admin, $syncing);
        $authManager->addChild($admin, $syncBuyer);
        $authManager->addChild($admin, $syncPriceCategory);
        $authManager->addChild($admin, $syncAll);
        $authManager->addChild($admin, $syncNomGroup);
        $authManager->addChild($admin, $getPriceForPC);
        $authManager->addChild($admin, $syncPriceForPC);
        $authManager->addChild($admin, $syncBuyerBalances);
        $authManager->addChild($admin, $sysInfo);
        $authManager->addChild($admin, $showErrors);

        $authManager->addChild($admin, $buyer);

        //Добавляем правило, запрещающее редактировать чужой профиль
        $userProfileOwnerRule = new UserProfileOwnerRule();
        $authManager->add($userProfileOwnerRule);

        $updateOwnProfile = $authManager->createPermission('updateOwnProfile');
        $updateOwnProfile->ruleName = $userProfileOwnerRule->name;
        $authManager->add($updateOwnProfile);

        $authManager->addChild($buyer, $updateOwnProfile);
        $authManager->addChild($supplier, $updateOwnProfile);


    }
}