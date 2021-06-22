<?php

use app\models\OrderToNomenclature;
use kartik\select2\Select2;
use yii\helpers\Html;

/* @var $model app\models\Order */
/* @var $dataProvider array */
/* @var $blank_id integer */

$counter = 1;
$product_sum = OrderToNomenclature::getTotalPrice($model->id);
$product_sum = $product_sum ? $product_sum : 0;
?>
<div class="panel panel-primary">
    <div class="panel-heading" style="display: flex; justify-content: space-between;">
        <h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Список позиций для заказа</h3>
        <div class="total-amount">
            Итого: <span class="total"><?= $product_sum; ?></span><span>&nbsp;р.</span>
        </div>
    </div>
    <div class="panel-body">
        <div class="search-product">
            <div class="row">
                <div class="col-md-12" style="margin-bottom: 10px">
                    <?php
                    try {
                        echo Select2::widget([
                            'model' => $model,
                            'attribute' => 'search_product_id',
                            'data' => $model->getProductList($dataProvider),
                            'options' => [
                                'placeholder' => 'Поиск продуктов',
                                'class' => 'product-search-input',
                                'id' => 'search-input-' . $blank_id
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                            'pluginEvents' => [
                                "change" => "function() {
                                    $(this).parents('.search-product').find('.search-btn').click()
                                }",
                            ]
                        ]);
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    } ?>
                </div>
                <div class="col-md-3 col-sm-12">
                    <?php
                    echo Html::button('Найти в бланке', [
                        'class' => 'btn btn-primary btn-block search-btn',
//                        'id' => 'search-btn',
                        'style' => 'display:none;'
                    ]) ?>
                </div>
            </div>
        </div>
        <table class="table table-bordered table-hover table-nomenclature">
            <thead>
            <tr>
                <th>#</th>
                <th>Наименование</th>
                <th>Описание</th>
                <th>Количество</th>
                <th>Ед. измерения</th>
                <th>Цена</th>
                <th>Итого</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($dataProvider): ?>
                <?php
                /** @var array $product */
                foreach ($dataProvider as $product): ?>
                    <?php
                    Yii::info($product, 'test');
                    if (!$product) {
                        continue;
                    }

                    ?>
                    <tr>
                    <td aria-label="#"><?= $counter ?></td>
                    <td aria-label="Наименование"><?= $product['name'] ?></td>
                    <td aria-label="Описание"><?= $product['description'] ?: 'нет' ?></td>
                    <td aria-label="Кол-во"><?= Html::input('number', "Order[count][{$product['obtn_id']}]",
                            $product['count'],
                            [
                                'data-obtn-id' => $product['obtn_id'],
                                'class' => 'form-control count-product',
                                'min' => 0,
                                'step' => 1,
                                'onkeypress' => 'return event.charCode >= 48'
                            ]) ?></td>
                    <td aria-label="Ед. изм."><?= $product['measure'] ?></td>
                    <td aria-label="Цена" class="product-price"><?= $product['price'] ?></td>
                    <td aria-label="Итого" class="total-cost"><?= $product['count'] * $product['price'] ?></td>
                    <?php $counter++; ?>
                <?php endforeach; ?>
                </tr>
            <?php else: ?>
                <p>Ничего не найдено</p>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
