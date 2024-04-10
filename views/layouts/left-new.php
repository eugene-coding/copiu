<?php
use app\models\Users;
use dmstr\widgets\Menu;
use yii\bootstrap\Html;
use app\models\Settings;

$fio = Yii::$app->user->identity->fio;
$space_pos = mb_strpos($fio, ' ', 10);
$cut_fio = mb_substr($fio, 0, $space_pos);

$file = Settings::getValueByKey(Settings::KEY_PRICE_LIST);
?>


<div class="iq-sidebar  sidebar-default ">
    <div class="iq-sidebar-logo d-flex align-items-center">
        <a href="/" class="header-logo">
            <h3 class="logo-title light-logo">КОФЕПЬЮ</h3>
        </a>
        <div class="iq-menu-bt-sidebar ml-0">
            <i class="las la-bars wrapper-menu"></i>
        </div>
    </div>
    <div class="data-scrollbar" data-scroll="1">
        <nav class="iq-sidebar-menu">
            <?php try {
                echo Menu::widget(
                    [
                        'options' => ['class' => 'iq-menu', 'data-widget' => 'tree'],
                        'items' => [
                            [
                                'label' => 'Заказы',
                                'icon' => 'shopping-cart',
                                'url' => ['/order'],
                            ],
                            [
                                'label' => 'Прайс-лист',
                                'icon' => 'shopping-cart',
                                'url' => ['/uploads/'.$file],
                                'visible' => !empty($file)
                            ],
                            [
                                'label' => '',
                                'icon' => 'shopping-cart',
                                'url' => ['/'],
                            ],
                            [
                                'label' => 'Выход',
                                'icon' => 'shopping-cart',
                                'url' => ['/site/logout'],
                            ],
                        ],
                    ]
                );
            } catch (Exception $e) {
                echo $e->getMessage();
            } ?>
        </nav>
        <div class="pt-5 pb-2"></div>
    </div>
</div>
<div class="iq-top-navbar">
    <div class="iq-navbar-custom">
        <nav class="navbar navbar-expand-lg navbar-light p-0">
            <div class="iq-navbar-logo d-flex align-items-center justify-content-between">
                <i class="ri-menu-line wrapper-menu"></i>
                <a href="/" class="header-logo">
                    <h4 class="logo-title text-uppercase">КофеПью</h4>
                </a>
            </div>
            <div class="navbar-breadcrumb">
            </div>
            <div class="d-flex align-items-center">
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                        data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-label="Toggle navigation">
                    <i class="ri-menu-3-line"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto navbar-list align-items-center">
                        <li class="nav-item nav-icon dropdown caption-content">
                                <div class="caption ml-3">
                                    <h6 class="mb-0 line-height"><?= $fio ?></h6>
                                </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</div>