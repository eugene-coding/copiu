<?php

use app\models\Settings;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

$this->title = 'Режим обслуживания';

?>

<div class="login-box">
    <div class="login-logo">
        <a href="#"><?= Settings::getValueByKey('app_name')?></a>
    </div>
    <div class="login-box-body">
        <h4>Сервис переехал на новый адрес:</h4>
        <h5> <?= Html::a('http://noviko0v.beget.tech', 'http://noviko0v.beget.tech') ?></h5>
        <p>
        <?= Html::a('Перейти на новый адрес', 'http://noviko0v.beget.tech', [
                'class' => 'btn btn-success btn-block'
        ]) ?>
        </p>
    </div>
</div>
