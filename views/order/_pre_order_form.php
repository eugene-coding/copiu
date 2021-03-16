<?php

/* @var $model app\models\Order */

?>

<div class="pre-order-form">
    <div class="row">
        <div class="col-xs-12 text-center">
            <h3>Подтверждение данных</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-6 text-center">
            Заказ <b>на <?= Yii::$app->formatter->asDate($model->target_date); ?></b>
            c <?= Yii::$app->formatter->asTime($model->delivery_time_from); ?>
            по <?= Yii::$app->formatter->asTime($model->delivery_time_to); ?>
            <br>
            <br>
            Наименований: <?= count($model->orderToNomenclature) ?>.
            На сумму:  <?= Yii::$app->formatter->asCurrency($model->total_price); ?>
            <br>
            <br>
            Доставка: <?= Yii::$app->formatter->asCurrency($model->deliveryCost); ?>
            <br>
            <br>
            ИТОГО: <?= Yii::$app->formatter->asCurrency($model->deliveryCost + $model->total_price);?>
        </div>
        <div class="col-xs-6">
            Комментарий к доставке:
            <br>
            <?= $model->comment; ?>
            <?= $form->field($model, 'status')->hiddenInput(['value' => $model::STATUS_WORK])->label(false) ?>
        </div>
    </div>

</div>
