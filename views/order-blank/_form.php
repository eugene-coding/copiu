<?php

use app\models\Buyer;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\OrderBlank */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="order-blank-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'number')->textInput(['maxlength' => true])->label('Название бланка (Номер накладной)') ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'date')->input('date') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'time_limit')->input('time', [
                'value' => Yii::$app->formatter->asTime($model->time_limit),
            ]) ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'day_limit')->input('number') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?=  $form->field($model, 'show_number_in_comment')->checkbox(); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <?= $form->field($model, 'buyers')->widget(Select2::class, [
                'data' => Buyer::getList(),
                'showToggleAll' => false,
                'theme' => Select2::THEME_CLASSIC,
                'options' => [
                    'placeholder' => 'Выберите заказчиков',
                    'multiple' => true,
                    'autocomplete' => 'off'
                ]
            ])->hint('Выбранные заказчики будут видеть бланк , для остальных бланк будет скрыт. Пустое поле означает, что бланк видят все') ?>
        </div>
    </div>


    <?php if (!Yii::$app->request->isAjax) { ?>
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Редактировать',
                ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    <?php } ?>

    <?php ActiveForm::end(); ?>

</div>
