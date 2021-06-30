<?php

use app\models\OrderBlank;
use kartik\select2\Select2;
use yii\bootstrap\Html;

/* @var $productsDataProvider \yii\data\ActiveDataProvider */
/* @var $model app\models\Order */

?>
    <style>
        .card {
            border: black solid 1px;
            margin-top: 15px;
            border-radius: 10px;
            padding: 5px;
        }
    </style>
    <ul class="nav nav-tabs">
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
                            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                <?= $this->render('_nomenclature_mobile', [
                                    'model' => $model,
                                    'blank_id' => $tab_model->id,
                                    'products' => $products,
                                ]) ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php echo Html::input('text', 'is_mobile', 1, [
    'class' => 'hidden',
    'id' => 'is-mobile'
]) ?>

<?php
$script = <<<JS
function setProduct(order_id, obtn_id, count, price){
     $.post('/order/add-product', {
            order_id: order_id,
            obtn_id:obtn_id,
            count:count,
            price:price
        })
        .fail(function (response) {
                console.log(response.responseText);
            });
};
$('.showDescription').on('click',function () {
    console.log ($(this).html());
      $(this).parent().parent().next().toggle();
  });

$('.count-inc').on('click',function () {
    let order_id = $('#order-step').attr('data-id');
    let obtn_id = $(this).attr('data-obtn-id');
    let price = $(this).parents('.card').find('.product-price').html();
    
    let input = $(this).parents('.input-group').find('input');
    let count = input.val();
    if (count.length < 1){
        count = 0;
    }
    let result = parseInt(count) + 1;
    input.val(result);
    input.parents('.card').find('.product-total-price').html(result * price);
    setProduct(order_id, obtn_id, result, price);
    
});
$('.count-dec').on('click',function () {
    let order_id = $('#order-step').attr('data-id');
    let obtn_id = $(this).attr('data-obtn-id');
    let price = $(this).parents('.card').find('.product-price').html();
    
    let input = $(this).parents('.input-group').find('input');
    let count = input.val();
    let result;
    if (count.length < 1){
        result = 0
    } else {
        if (count <= 0){
            result = 0;
        } else {
            result = parseInt(count) - 1;
        }
    }
    input.val(result);
    input.parents('.card').find('.product-total-price').html(result * price);
    setProduct(order_id, obtn_id, result, price);
});
$(document).on('change', '.count-product', function () {
    debugger;
        var count = $(this).val();
        var price = $(this).parents('.card').find('.product-price').html();
        var price_d = $(this).parents('tr').find('.product-price').html();
        // $(this).parents('.card').find('.total-cost').html((count * price).toFixed(2));
        // $(this).parents('tr').find('.total-cost').html((count * price_d).toFixed(2));
        if (typeof(price) === 'undefined'){
            price = price_d;
        }
        var order_id = $('#order-step').attr('data-id');
        var obtn_id = $(this).attr('data-obtn-id');

        // var total = 0;
        // $('.total-cost').each(function (index, value) {
        //     total += Number(value.innerHTML);
        // });
        $.post('/order/add-product', {
            order_id: order_id,
            obtn_id:obtn_id,
            count:count,
            price:price
        })
            .done(function (response) {
                $('.total').html(Number(response.total).toFixed(2));
            });

    });

JS;
$this->registerJs($script);

?>