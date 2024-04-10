<?php

/* @var $model app\models\Order */
/* @var $form yii\widgets\ActiveForm */

?>
<div class="order-index row">
    <div class="col-sm-12 col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div class="header-title">
                    <h4 class="card-title">Список заказанных позиций</h4>
                </div>
            </div>
            <div class="card-body">
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
                        $product = $obtn->n;
                        $count = $obtn->getCount($model->id);
                        $price = $obtn->getPriceForOrder($model->id);
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

    <div class="col-sm-12 col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div class="header-title">
                    <h4 class="card-title">Информация о заказе</h4>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Заказ <b>на <?= Yii::$app->formatter->asDate($model->target_date); ?></b>
                        c <?= Yii::$app->formatter->asTime($model->delivery_time_from); ?>
                        по <?= Yii::$app->formatter->asTime($model->delivery_time_to); ?></li>
                    <li class="list-group-item">Наименований: <?= count($model->orderToNomenclature) ?>.</li>
                    <li class="list-group-item">На сумму: <?= Yii::$app->formatter->asCurrency($model->total_price); ?></li>
                    <li class="list-group-item">Доставка: <?= Yii::$app->formatter->asCurrency($model->deliveryCost); ?></li>
                    <li class="list-group-item">ИТОГО: <?= Yii::$app->formatter->asCurrency($model->deliveryCost + $model->total_price); ?></li>
                    <li class="list-group-item">Адрес доставки: <?= \yii\helpers\Html::encode($model->address->address ?? '') ?></li>
                    <li class="list-group-item">Комментарий к доставке:<br>
                        <?= $model->getComment(); ?></li>
                </ul>
                <?= $form->field($model, 'status')->hiddenInput(['value' => $model::STATUS_WORK])->label(false) ?>
            </div>
        </div>
    </div>
</div>