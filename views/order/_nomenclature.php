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

        <div class="table-products">
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

