<?php
/* @var $model app\models\Order */

/* @var $dataProvider \yii\data\ActiveDataProvider */

use yii\helpers\Html;

$counter = 1;
?>
<div class="panel panel-primary">
    <div class="panel-heading" style="display: flex; justify-content: space-between;">
        <h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Позиции в заказе</h3>
    </div>
    <div class="panel-body">
        <?php if ($dataProvider): ?>
            <table class="table table-bordered table-hover table-order-nomenclature">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Наименование</th>
                    <th>Кол-во</th>
                    <th>Ед. измерения</th>
                    <th>Цена</th>
                    <th>Итого</th>
                    <th>...</th>
                </tr>
                </thead>
                <tbody>
                <?php /** @var \app\models\Nomenclature $product */
                foreach ($dataProvider->getModels() as $product): ?>
                <tr>
                    <td><?= $counter ?></td>
                    <td><?= $product->name ?></td>
                    <td><?= Html::input('number', 'Order[count][' . $product->id . ']', $product->getCount($model->id), [
                            'class' => 'form-control count-product',
                        ]) ?></td>
                    <td><?= $product->measure->name ?></td>
                    <td class="product-price"><?= $product->priceForBuyer ?></td>
                    <td class="total-cost"><?= $product->count * $product->priceForBuyer ?></td>
                    <td><?php
                        echo Html::a('<i class="fa fa-close text-warning"></i>', [
                            '/order/exclude-product',
                            'order_id' => $model->id,
                            'nomenclature_id' => $product->id,
                        ], [
                            'title' => 'Исключить из заказа',
                            'role' => 'modal-remote',
                        ]) ?></td>

                    <?php $counter++; ?>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td colspan="5" class="text-right"><b>Итого: </b></td>
                    <td colspan="2" ><b><span class="total"></span></b></td>
                </tr>
                </tbody>
            </table>
        <?php else: ?>
            <h5>Позиции не выбраны</h5>
        <?php endif; ?>
    </div>
</div>