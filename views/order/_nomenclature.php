<?php

use app\models\Settings;
use yii\helpers\Html;

/* @var $model app\models\Order */
/* @var $dataProvider array */

$counter = 1;

$view_min_col = (bool)Settings::getValueByKey('check_quantity_enabled');
?>

<div class="table-products">
    <table class="table table-bordered table-hover table-nomenclature">
        <thead>
        <tr>
            <th>#</th>
            <th>Избр.</th>
            <th>Наименование</th>
            <th>Описание</th>
            <th>Количество</th>
            <?php if ($view_min_col): ?>
                <th>Мин. кол-во</th>
            <?php endif; ?>
            <th>Ед. измерения</th>
            <th>Цена</th>
            <th>Итого</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($dataProvider): ?>
            <?php
           //Yii::debug($dataProvider, 'test');
            /** @var array $product */
            foreach ($dataProvider as $product): ?>
                <?php
               //Yii::debug('$counter1: ' . $counter, 'test');
               //Yii::debug( $product['name'] , 'test');
                Yii::debug($product, 'test');
                $prod = $product['id'] ?? null;
                if (!$prod && $product){
                    $product = $product[0];
                }
                if (!$product) {
                    Yii::debug('Пропускаем', 'test');
                    continue;
                }
                ?>
                <tr>
                <td aria-label="#"><?= $counter ?></td>
                <td aria-label="Избр." class="text-center">
                    <?= Html::a($product['is_favorite'] ? '<i class="fa fa-fw fa-star fa-2x"></i>': '<i class="fa fa-fw fa-star-o fa-2x"></i>',
                            ['/order/order-update', 'id' => $model->id],[
                                    'id' => 'change-favorite-btn',
                                    'data-href' => '/favorite-product/change?id=' . $product['obtn_id'],
                                    'class' => 'text-warning',
                                    'title' => $product['is_favorite'] ? 'Нажмите для исключения из избранного': 'Нажмите для включения в избранное',
                            ]); ?>
                </td>
                <td aria-label="Наименование"><?= $product['name'] ?></td>
                <td aria-label="Описание">
                    <div class="description" style="max-width: 200px">
                        <?php
                        if (isset($product['description'])) {
                          echo $product['description'];
                        } else {
                            echo 'нет';
                        } ?>
                    </div>
                </td>
                    <td aria-label="Кол-во"><?= Html::input('number', "Order[count][{$product['obtn_id']}]",
                        $product['count'] ?? 0,
                        [
                            'data-order-id' => $model->id,
                            'data-obtn-id' => $product['obtn_id'],
                            'class' => 'form-control count-product',
                            'min' => 0,
                            'step' => 1,
                            'onkeypress' => 'return event.charCode >= 48'
                        ]) ?></td>
                <?php if ($view_min_col): ?>
                    <td aria-label="Мин. кол-во"><?=$product['min_quantity'];?></td>
                <?php endif; ?>
                <td aria-label="Ед. изм."><?= $product['measure'] ?></td>
                <td aria-label="Цена" class="product-price"><?= $product['price'] ?? 0 ?></td>
                <?php
                    $count = $product['count'] ?? 0;
                    $price = $product['price'] ?? 0;
                ?>
                <td aria-label="Итого" class="total-cost"><?= $count * $price ?></td>
                <?php
                $counter++;
                Yii::debug('$counter2: ' . $counter, 'test');
                ?>
            <?php endforeach; ?>
            </tr>
        <?php else: ?>
            <p>Ничего не найдено</p>
        <?php endif; ?>
        </tbody>
    </table>
</div>

