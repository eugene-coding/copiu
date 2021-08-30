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
        <p class="login-box-msg">Извините, сайт находится на обслуживании. <br><br>Попробуйте зайти позже.</p>
    </div>
</div>
