<?php

/* @var $model app\models\Order */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="pre-order-form">
    <div class="container container-pre-order">
        <div class="panel panel-primary">
            <div class="panel-heading text-center">
                <b style="font-size: 2rem;">Подтверждение данных</b>
            </div>
            <div class="panel-body">
                <div class="col-md-6 col-xs-12 text-center">
                    Заказ <b>на <?= Yii::$app->formatter->asDate($model->target_date); ?></b>
                    c <?= Yii::$app->formatter->asTime($model->delivery_time_from); ?>
                    по <?= Yii::$app->formatter->asTime($model->delivery_time_to); ?>
                    <br>
                    <br>
                    Наименований: <?= count($model->orderToNomenclature) ?>.
                    На сумму: <?= Yii::$app->formatter->asCurrency($model->total_price); ?>
                    <br>
                    <br>
                    Доставка: <?= Yii::$app->formatter->asCurrency($model->deliveryCost); ?>
                    <br>
                    <br>
                    Адрес доставки: <?= \yii\helpers\Html::encode($model->address->address ?? '') ?>
                    <br>
                    <br>

                    ИТОГО: <?= Yii::$app->formatter->asCurrency($model->deliveryCost + $model->total_price); ?>
                </div>
                <div class="col-md-6 col-xs-12 text-center">
                    Комментарий к доставке:
                    <br>
                    <?= $model->getComment(); ?>
                    <?= $form->field($model, 'status')->hiddenInput(['value' => $model::STATUS_WORK])->label(false) ?>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                Список заказанных позиций
            </div>
            <div class="panel-body">
                <div class="order-view">
                    <table class="table table-hover table-responsive">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Наименование</th>
                            <th class="order-view-measure">Ед. изм.</th>
                            <th>Цена</th>
                            <th>Кол-во</th>
                            <th>Сумма</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php /** @var OrderBlankToNomenclature $obtn */
                        $counter = 1;
                        foreach ($model->getObtns() as $obtn): ?>
                            <?php
                            //Yii::debug($obtn->attributes, 'test');
                            $product = $obtn->n;
                            //Yii::debug($product->attributes, 'test');
                            $count = $obtn->getCount($model->id);
                            $price = $obtn->getPriceForOrder($model->id);
//                        Yii::debug('Продукт: ' . $product->name, 'test');
//                        Yii::debug('Цена: ' . $price, 'test');
                            ?>
                            <tr>
                                <td><?= $counter; ?></td>
                                <td><?= $product->name ?></td>
                                <td class="order-view-measure"><?= $product->findMeasure($obtn) ?></td>
                                <td><?= $price ?></td>
                                <td><?= $count ?></td>
                                <td><?= $count * $price; ?></td>
                            </tr>
                            <?php
                            $counter++;
                            ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 text-center">
        </div>
    </div>

</div>
