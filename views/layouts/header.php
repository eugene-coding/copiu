<?php

use app\models\Settings;
use app\models\Users;
use yii\bootstrap\Modal;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

\johnitvn\ajaxcrud\CrudAsset::register($this)
?>

    <header class="main-header">

        <?= Html::a('<span class="logo-mini">BB</span><span class="logo-lg">' . Yii::$app->name . '</span>',
            Yii::$app->homeUrl, ['class' => 'logo']) ?>

        <nav class="navbar navbar-static-top" role="navigation">

            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>

            <div class="navbar-custom-menu">

                <ul class="nav navbar-nav">
                    <li>
                        <?php
                        if (Users::isAdmin() && !Settings::checkSettings()['success']) {
                            echo Html::a('<i class="fa fa-warning text-danger"></i> Отсутствуют настройки, необходимые для работы системы!',
                                ['/settings/show-errors'],
                                [
                                    'role' => 'modal-remote',
                                    'title' => 'Показать информацию',
                                    'class' => 'btn btn-warning btn-block'
                                ]);
                        }
                        ?>
                    </li>
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="/img/user.png" class="user-image" alt="User Image"/>
                            <span class="hidden-xs"><?= Yii::$app->user->identity->fio ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="/img/user.png" class="img-circle"
                                     alt="User Image"/>

                                <p>
                                    <?= Yii::$app->user->identity->fio ?><br>
                                    <?= Yii::$app->user->identity->roleDescription ?>

                                </p>
                            </li>
                            <!-- Menu Body -->

                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <?= Html::a('Профиль', ['/users/profile'], [
                                        'class' => 'btn btn-default btn-flat',
                                        'role' => 'modal-remote'

                                    ]) ?>
                                </div>
                                <div class="pull-right">
                                    <?= Html::a(
                                        'Выход',
                                        ['/site/logout'],
                                        ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                    ) ?>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
<?php Modal::begin([
    "id" => "ajaxCrudModal",
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>