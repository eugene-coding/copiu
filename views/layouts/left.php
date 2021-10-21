<?php

use app\models\Users;
use dmstr\widgets\Menu;

?>
<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="/img/user.png" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <?php
                    $fio = Yii::$app->user->identity->fio;
                    $space_pos = mb_strpos($fio, ' ', 10);
                    $cut_fio = mb_substr($fio, 0, $space_pos);
                ?>
                <p title="<?= $fio ?>"><?= $cut_fio ? $cut_fio . '...': $fio ?></p>

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
                                ['label' => 'Лог заказов', 'icon' => 'file-code-o', 'url' => ['/order-logging'],],
                                ['label' => 'Gii', 'icon' => 'file-code-o', 'url' => ['/gii'],],
                                ['label' => 'Debug', 'icon' => 'dashboard', 'url' => ['/debug'],],
                                [
                                    'label' => 'System Info',
                                    'template' => '<a href="{url}" role="modal-remote"><i class="fa fa-check-square-o"></i> {label}</a>',
                                    'url' => ['/settings/system-info'],
                                ],
                            ],
                            'visible' => Users::isAdmin()
                        ],
                        [
                            'label' => 'Заказы',
                            'icon' => 'shopping-cart',
                            'url' => ['/order'],
//                            'visible' => Users::isBuyer()
                        ],
//                        [
//                            'label' => 'Черновики',
//                            'icon' => 'circle',
//                            'url' => ['/order-draft'],
//                            'visible' => Users::isBuyer(),
//                        ],
                        [
                            'label' => 'Избранное',
                            'icon' => 'star',
                            'url' => ['/favorite-product'],
                            'visible' => Users::isBuyer() && Users::favoriteExists(),
                        ],
                    ],
                ]
            );
        } catch (Exception $e) {
            echo $e->getMessage();
        } ?>

    </section>

</aside>
