<?php

use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Buyer */
/* @var $user_model app\models\Buyer */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="buyer-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-xs-6">
            <?= $form->field($user_model, 'login')->textInput() ?>
        </div>
        <div class="col-xs-6">
            <?= $form->field($user_model, 'open_pass')->textInput() ?>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-6">
            <b> Ценовая категория:</b><br> <?= $model->pc_id ? $model->pc->name : 'По умолчанию'; ?>
        </div>
        <div class="col-xs-6">
            <?= $form->field($model, 'delivery_cost')->input('number') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-6">
            <?= $form->field($model, 'min_order_cost')->input('number') ?>
        </div>
        <div class="col-xs-6">
            <?= $form->field($model, 'min_balance')->input('number') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-6">
            <?= $form->field($model, 'discount')->input('number', ['value' => $model->discount * 100]) ?>
        </div>
        <div class="col-xs-6">
            <?= $form->field($model, 'work_mode')->dropDownList($model::getWorkModeList()) ?>
        </div>
    </div>

    <?= $form->field($model, 'outer_id')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
