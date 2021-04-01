<?php

use app\models\OrderToNomenclature;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $model app\models\Order */
/* @var \app\models\Nomenclature[] $dataProvider \yii\data\ActiveDataProvider */

$counter = 1;
$product_sum = OrderToNomenclature::getTotalPrice($model->id);
$product_sum = $product_sum ? $product_sum . 'р.' : '';

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
            <?php
            $arr = ArrayHelper::map($dataProvider->getModels(), 'id', 'name');
            Yii::info($arr, 'test') ?>
            <?php /** @var \app\models\Nomenclature $product */
            foreach ($dataProvider->getModels() as $product): ?>
            <?php
            if ($product->containers){
                $container_id = '063ecc2c-b260-4e4d-a42a-c6007005eafe';
                $measure = $product->getContainerById($container_id)->name;
            } else {
                $measure = $product->measure->name;
            }
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
                <td><?= $measure ?></td>
                <td class="product-price"><?= $priceForBuyer ?></td>
                <td class="total-cost"><?= $count_product * $priceForBuyer ?></td>
                <?php $counter++; ?>
                <?php endforeach; ?>
            </tr>
            </tbody>
        </table>
    </div>
</div>