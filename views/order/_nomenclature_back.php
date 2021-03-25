<?php

use yii\helpers\Html;

/* @var $model app\models\Order */
/* @var $orderToNomenclatureDataProvider \yii\data\ActiveDataProvider */

$counter = 1;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Список позиций для заказа</h3>
    </div>
    <div class="panel-body">
        <table class="table table-bordered table-hover table-nomenclature">
            <thead>
            <tr>
                <th>#</th>
                <th>Наименование</th>
                <th>Ед. измерения</th>
                <th>Цена</th>
                <th>...</th>
            </tr>
            </thead>
            <tbody>
            <?php /** @var \app\models\Nomenclature $product */
            foreach ($dataProvider->getModels() as $product): ?>
            <?php Yii::info($product->attributes, 'test') ?>
            <tr>
                <td><?= $counter ?></td>
                <td><?= $product->name ?></td>
                <td><?= $product->measure->name ?></td>
                <td><?= $product->default_price ?></td>
                <td>
                    <?php
                    echo Html::a('<i class="fa fa-plus text-success"></i>', [
                        '/order/include-product',
                        'order_id' => $model->id,
                        'nomenclature_id' => $product->id,
                    ], [
                        'title' => 'Добавить в заказ',
                        'role' => 'modal-remote',
                    ])
                    ?>
                </td>

                <?php $counter++; ?>
                <?php endforeach; ?>
            </tr>
            </tbody>
        </table>
    </div>
</div>