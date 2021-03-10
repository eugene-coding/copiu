<?php

use app\models\Users;
use dmstr\widgets\Menu;

?>
<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p><?= Yii::$app->user->identity->fio ?></p>

                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <?php try {
            echo Menu::widget(
                [
                    'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
                    'items' => [
                        ['label' => 'Меню', 'options' => ['class' => 'header']],
                        [
                            'label' => 'Пользователи',
                            'icon' => 'users',
                            'url' => ['/users'],
                            'visible' => Users::isAdmin()
                        ],
                        [
                            'label' => 'Ценовые категории',
                            'icon' => 'inbox',
                            'url' => ['/price-category'],
                            'visible' => Users::isAdmin()
                        ],
                        [
                            'label' => 'Бланки заказа',
                            'icon' => 'file-text-o',
                            'url' => ['/order-blank'],
                            'visible' => Users::isAdmin()
                        ],
                        ['label' => 'Покупатели', 'icon' => 'male', 'url' => ['/buyer'], 'visible' => Users::isAdmin()],
                        [
                            'label' => 'Настройки',
                            'template' => '<a href="{url}" role="modal-remote"><i class="fa fa-wrench"></i> {label}</a>',
                            'url' => ['/settings'],
                            'visible' => Users::isAdmin()
                        ],
                        [
                            'label' => 'Синхронизация',
                            'icon' => 'exchange',
                            'url' => ['/site/syncing'],
                            'template' => '<a href="{url}" role="modal-remote"><i class="fa fa-exchange"></i> {label}</a>',
                            'visible' => Users::isAdmin()
                        ],
                        [
                            'label' => 'Система',
                            'icon' => 'share',
                            'url' => '#',
                            'items' => [
                                ['label' => 'Gii', 'icon' => 'file-code-o', 'url' => ['/gii'],],
                                ['label' => 'Debug', 'icon' => 'dashboard', 'url' => ['/debug'],],
                            ],
                        ],
                    ],
                ]
            );
        } catch (Exception $e) {
            echo $e->getMessage();
        } ?>

    </section>

</aside>
