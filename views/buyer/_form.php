<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Buyer */
/* @var $user_model app\models\Buyer */
/* @var $addresses app\models\BuyerAddress[] */
/* @var $form yii\widgets\ActiveForm */

Yii::debug($addresses, 'test');
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

        <div class="panel panel-default">
            <div class="panel-heading" style="display: flex; justify-content: space-between;">
                <h4>Адреса:</h4>
                <?= Html::button('Добавить адрес',
                    ['class' => 'btn btn-info', 'id' => 'add-address-btn']) ?>
            </div>
            <div class="panel-body addresses-list">
                <div class="col-md-12 address-element" style="display: none;">
                    <div class="row">
                        <div class="col-md-10">
                            <?= $form->field($model, 'addresses_list[]')
                                ->textarea(['rows' => 2, 'id' => 'address-element-' . rand(15, 999)])
                                ->label(false) ?>
                        </div>
                        <div class="col-md-2">
                            <?= Html::button('Удалить',
                                ['class' => 'btn btn-danger remove-address-btn', 'style' => 'height: 50px;']) ?>

                        </div>
                    </div>
                </div>
                <?php if ($addresses): ?>
                    <?php foreach ($addresses as $address): ?>
                        <?php Yii::debug($address->toArray(), 'test') ?>
                        <div class="col-md-12 address-element">
                            <div class="row">
                                <div class="col-md-10">
                                    <?= $form->field($model, 'addresses_list[]')
                                        ->textarea([
                                            'rows' => 2,
                                            'value' => $address->address,
                                            'id' => 'address-element-' . rand(15, 999)
                                        ])
                                        ->label(false) ?>
                                </div>
                                <div class="col-md-2">
                                    <?= Html::button('Удалить',
                                        ['class' => 'btn btn-danger remove-address-btn', 'style' => 'height: 50px;']) ?>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?= $form->field($model, 'outer_id')->hiddenInput()->label(false) ?>

        <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>

        <?php ActiveForm::end(); ?>

    </div>

<?php
$js = <<<JS
$(document).on('click', '#add-address-btn', function () {
    let list = $('.addresses-list');
    let div = list.find('.address-element:first');
    div.clone().appendTo(list).slideDown();
});
$(document).on('click', '.remove-address-btn', function () {
    let element = $(this).parents('.address-element');
    element.slideUp();
    element.remove();
});

JS;
$this->registerJs($js);