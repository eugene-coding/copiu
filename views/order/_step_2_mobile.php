<?php

use app\models\OrderBlank;
use app\models\User;
use kartik\select2\Select2;
use yii\bootstrap\Html;

/* @var $productsDataProvider \yii\data\ArrayDataProvider */
/* @var $favoriteDataProvider \yii\data\ArrayDataProvider */
/* @var $model app\models\Order */

?>
<ul class="nav nav-tabs">
    <!--Избранное-->
    <?php if (User::favoriteExists()): ?>
        <li role="presentation">
            <a href="#tab-favorite" aria-controls="favorite"
               role="tab" data-toggle="tab">Избранное</a>
        </li>
    <?php endif; ?>
    <!--Остальные-->
    <?php foreach ($productsDataProvider->getModels() as $tab_name => $products): ?>
        <?php $tab_model = OrderBlank::findOne(['number' => $tab_name]); ?>
        <li role="presentation">
            <a href="#tab-<?= $tab_model->id ?>" aria-controls="<?= $tab_model->id; ?>"
               role="tab" data-toggle="tab">
                <?= $tab_model->number; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
<!--Контент вкладок -->
<div class="tab-content">
    <!-- Избранное -->
    <?php foreach ($favoriteDataProvider->getModels() as $tab_id => $products): ?>
        <div role="tabpanel" class="tab-pane" id="tab-favorite">
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_nomenclature_mobile', [
                        'model' => $model,
                        'products' => $products,
                    ]) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <!--Остальные-->
    <?php foreach ($productsDataProvider->getModels() as $tab_name => $products): ?>
        <?php $tab_model = OrderBlank::findOne(['number' => $tab_name]); ?>
        <div role="tabpanel" class="tab-pane" id="tab-<?= $tab_model->id ?>">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="glyphicon glyphicon-th-list"></i> Список позиций для заказа
                    </h3>
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
                                        'data' => $model->getProductList($products),
                                        'options' => [
                                            'placeholder' => 'Поиск продуктов',
                                            'class' => 'product-search-input',
                                            'id' => 'search-input-' . $tab_model->id
                                        ],
                                        'pluginOptions' => [
                                            'allowClear' => true
                                        ],
                                        'pluginEvents' => [
                                            "change" => "function() {
                                                    $(this).parents('.search-product').find('.search-mobile-btn').click()
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
                                    'class' => 'btn btn-primary btn-block search-mobile-btn',
                                    'style' => 'display:none;'
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                            <div class="product-cards">
                                <?= $this->render('_nomenclature_mobile', [
                                    'model' => $model,
                                    'blank_id' => $tab_model->id,
                                    'dataProvider' => $products,
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
