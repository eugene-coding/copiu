<?php

use app\models\OrderToNomenclature;
use yii\helpers\Html;

/* @var $model app\models\Order */
/* @var \app\models\Nomenclature[] $dataProvider \yii\data\ActiveDataProvider */

$counter = 1;
$product_sum = OrderToNomenclature::getTotalPrice($model->id);
$product_sum = $product_sum ? $product_sum . 'р.': '';

?>
<div class="panel panel-primary">
    <div class="panel-heading" style="display: flex; justify-content: space-between;">
        <h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Список позиций для заказа</h3>
        <div class="total-amount">
            Итого: <span class="total"><?= $product_sum; ?></span>
        </div>
    </div>
    <div class="panel-body">
        <table class="table table-bordered table-hover table-nomenclature">
            <thead>
            <tr>
                <th>#</th>
                <th>Наименование</th>
                <th>Количество</th>
                <th>Ед. измерения</th>
                <th>Цена</th>
                <th>Итого</th>
            </tr>
            </thead>
            <tbody>
            <?php Yii::info(\yii\helpers\ArrayHelper::map($dataProvider->getModels(), 'id', 'name'), 'test') ?>
            <?php /** @var \app\models\Nomenclature $product */
            foreach ($dataProvider->getModels() as $product): ?>
            <?php
            $count_product = $product->getCount($model->id);
            $priceForBuyer = $product->priceForBuyer;
            Yii::info($product->attributes, 'test')
            ?>
            <tr>
                <td><?= $counter ?></td>
                <td><?= $product->name ?></td>
                <td><?= Html::input('number', 'Order[count][' . $product->id . ']', $count_product, [
                        'class' => 'form-control count-product',
                    ]) ?></td>
                <td><?= $product->measure->name ?></td>
                <td class="product-price"><?= $priceForBuyer ?></td>
                <td class="total-cost"><?= $count_product * $priceForBuyer ?></td>
                <?php $counter++; ?>
                <?php endforeach; ?>
            </tr>
            </tbody>
        </table>
    </div>
</div>