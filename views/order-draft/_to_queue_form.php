<?php

use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $draft app\models\OrderDraft */
/* @var $form yii\widgets\ActiveForm */
/* @var $order app\models\Order */
/* @var $productsDataProvider \yii\data\ArrayDataProvider */


$this->title = 'Постановка заказа в очередь';
$this->params['breadcrumbs'][] = 'Черновики заказов';
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="queue-draft-form">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($order, 'target_date')->input('date') ?>
            </div>
        </div>

        <?= $form->field($draft, 'id')->hiddenInput()->label(false) ?>
        <?= $form->field($order, 'id')->hiddenInput()->label(false) ?>

        <?php ActiveForm::end(); ?>

    </div>
<?php
