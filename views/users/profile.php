<?php

use app\models\Users;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Users */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="users-form">

    <?php $form = ActiveForm::begin(['options' => ['method' => 'post', 'enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'fio')->textInput(['maxlength' => true]) ?>
        </div>


    </div>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'login')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $model->isNewRecord ? $form->field($model,
                'password')->textInput(['maxlength' => true]) : $form->field($model,
                'new_password')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php if (Users::isAdmin()): ?>
                <?= $form->field($model, 'role')->dropDownList($model->getRoles(),
                    ['prompt' => 'Выберите должность']) ?>
            <?php endif; ?>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>

