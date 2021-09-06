<?php

use app\models\BuyerAddress;
use yii\helpers\Html;
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
                <?= $form->field($order, 'delivery_address_id')
                    ->dropDownList(BuyerAddress::getList($order->buyer_id), [
                            'prompt' => 'Выберите адрес доставки'
                    ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($order, 'target_date')->input('date') ?>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">Период доставки</div>
            <div class="panel-body">
                <div class="col-md-6 col-sm-12 required">
                    <label>C</label><br>
                    <?= Html::dropDownList('Order[delivery_time_from]',
                        $order->delivery_time_from,
                        $order->buyer->getDeliveryTimeIntervals('from'), [
                            'class' => 'form-control',
                            'prompt' => 'Выберите время',
                        ]) ?>
                    <div class="help-block"></div>
                </div>
                <div class="col-md-6 col-sm-12 required">
                    <label>До</label><br>
                    <?= Html::dropDownList('Order[delivery_time_to]',
                        $order->delivery_time_to,
                        $order->buyer->getDeliveryTimeIntervals('to'), [
                            'class' => 'form-control',
                            'prompt' => 'Выберите время',
                        ]) ?>
                    <div class="help-block"></div>
                </div>
            </div>
        </div>

        <?= $form->field($draft, 'id')->hiddenInput()->label(false) ?>
        <?= $form->field($order, 'id')->hiddenInput()->label(false) ?>

        <?php ActiveForm::end(); ?>

    </div>
<?php
