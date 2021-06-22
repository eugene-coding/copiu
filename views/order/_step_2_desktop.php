<?php


/* @var $productsDataProvider \yii\data\ActiveDataProvider */

/* @var $model app\models\Order */

use app\models\OrderBlank;
use kartik\select2\Select2;
use yii\helpers\Html;

?>

<div class="col-md-12">
    <div>
        <!-- Навигационные вкладки -->
        <ul class="nav nav-tabs" role="tablist">
            <?php
            //                            Yii::warning($productsDataProvider->getModels());
            foreach ($productsDataProvider->getModels() as $tab_name => $products): ?>
                <?php $tab_model = OrderBlank::findOne(['number' => $tab_name]); ?>
                <li role="presentation">
                    <a href="#tab-<?= $tab_model->id ?>" aria-controls="<?= $tab_model->id; ?>"
                       role="tab" data-toggle="tab">
                        <?= $tab_model->number; ?>
                    </a>
                </li>
            <?php endforeach; ?>

        </ul>

        <!-- Вкладки панелей -->

        <div class="tab-content">
            <?php foreach ($productsDataProvider->getModels() as $tab_name => $products): ?>
                <?php $tab_model = OrderBlank::findOne(['number' => $tab_name]); ?>
                <div role="tabpanel" class="tab-pane" id="tab-<?= $tab_model->id ?>">
                    <div class="panel panel-primary">
                        <div class="panel-heading" style="display: flex; justify-content: space-between;">
                            <h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Список позиций для заказа
                            </h3>
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
                            <div class="row">
                                <div class="col-sm-12 tab-nomenclature-list">
                                    <?= $this->render('_nomenclature', [
                                        'model' => $model,
                                        'blank_id' => $tab_model->id,
                                        'dataProvider' => $products,
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>  <?php endforeach; ?>
        </div>
    </div>
</div>