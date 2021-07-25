<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\OrderBlank */
/* @var $productsDataProvider \yii\data\ArrayDataProvider */

$counter = 1;
?>
<div class="order-blank-view">

    <?php
    try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'date:date',
                'time_limit:time',
                'day_limit',
                'synced_at:datetime',
            ],
        ]);
    } catch (Exception $e) {
        echo $e->getMessage();
    } ?>

    <table class="table table hovered">
        <thead>
        <tr>
            <td>#</td>
            <td>Наименование</td>
            <td>Ед. измерения</td>
            <td>Кол-во</td>
        </tr>
        </thead>
        <tbody>
        <?php /** @var \app\models\Nomenclature $product */
        foreach ($productsDataProvider as $product): ?>
            <tr>
                <td><?= $counter; ?></td>
                <td><?= $product['name']; ?></td>
                <td><?= $product['measure']; ?></td>
                <td><?= $product['quantity']; ?></td>
            </tr>
            <?php $counter++; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>
