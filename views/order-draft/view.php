<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\OrderDraft */
/* @var $order app\models\Order */
?>
<div class="order-draft-view">
    <?php
    try {
        echo DetailView::widget([
            'model' => $order,
            'attributes' => [
                'target_date:date',
                'total_price:currency',
                'comment:ntext',
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
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Наименование</th>
                    <th>Цена</th>
                    <th>Кол-во</th>
                    <th>Сумма</th>
                </tr>
                </thead>
                <tbody>
                <?php /** @var \app\models\OrderBlankToNomenclature $obtn */
                foreach ($order->getObtns() as $obtn):?>
                    <?php
                    Yii::debug($obtn->attributes, 'test');
                    $product = $obtn->n;
                    Yii::debug($product->attributes, 'test');
                    $count = $obtn->getCount($order->id);
                    $price = $obtn->getPriceForOrder($order->id);
                    ?>
                    <tr>
                        <td><?= $counter; ?></td>
                        <td><?= $product->name ?></td>
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
