<?php

use app\models\OrderBlankToNomenclature;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Order */

$counter = 1;
?>
<div class="order-view">
    <?php
    try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'created_at:datetime',
                'target_date:date',
                [
                    'attribute' => 'delivery_time_from',
                    'value' => 'с '
                        . Yii::$app->formatter->asTime($model->delivery_time_from)
                        . ' до '
                        . Yii::$app->formatter->asTime($model->delivery_time_to),
                    'label' => 'Время доставки',
                ],
                [
                    'attribute' => 'delivery_address_id',
                    'value' => $model->address->address,
                    'label' => 'Адрес доставки',
                    'visible' => (bool)$model->delivery_address_id,
                ],
                'total_price:currency',
                [
                    'attribute' => 'delivery',
                    'value' => Yii::$app->formatter->asCurrency($model->deliveryCost),
                    'label' => 'Доставка',
                ],
                'comment:ntext',
                'invoice_number',
                'delivery_act_number'
            ],
        ]);
    } catch (Exception $e) {
        echo $e->getMessage();
    } ?>

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
                    foreach ($model->getObtns() as $obtn): ?>
                        <?php
                        Yii::debug($obtn->attributes, 'test');
                        $product = $obtn->n;
                        Yii::debug($product->attributes, 'test');
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

</div>
