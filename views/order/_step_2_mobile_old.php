<?php

use yii\helpers\Html;

/* @var $productsDataProvider \yii\data\ActiveDataProvider */
?>
<style>
    .card {
        border: black solid 1px;
        margin-top: 15px;
        border-radius: 10px;
        padding: 5px;
    }
</style>
<?php foreach ($productsDataProvider->getModels() as $products) : ?>
    <?php foreach ($products as $item): ?>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <div class="card">
                <div class="row">
                    <div class="col-md-12 card-name">
                        <b>Наименование:</b>
                        <?= $item['name'] ?>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-12 card-description">
                        <b>Описание:</b>
                        <?= $item['description'] ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 card-ed">
                        <b>Единица измерения:</b>
                        <?= $item['measure']; ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6 col-sm-12 card-count">
                        <label for="count-product">Количество:</label><br>
                        <?= Html::input('number', "Order[count][{$item['obtn_id']}]",
                            $item->count, [
                                'id' => 'count-product',
                                'class' => 'form-control count-product',
                                'min' => 0,
                                'step' => 1,
                                'onkeypress' => 'return event.charCode >= 48'
                            ]) ?>
                    </div>
                    <div class="col-md-3 col-xs-6 card-cena">
                        <b>Цена:</b><br><span class="product-price"><?= $item['price'] ?></span>
                    </div>
                    <div class="col-md-3 col-xs-6 card-amount">
                        <b>Сумма:</b><br><span class="total-cost"><?= $item['count'] * $item['price'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>

