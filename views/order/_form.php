<?php

use app\components\MyBulkButtonWidget;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\Order */
/* @var $form yii\widgets\ActiveForm */
/* @var int $step Шаг заказа */
/* @var $productsDataProvider \yii\data\ActiveDataProvider */

if ($step == 1) {
    $title = 'Создание заказа. Шаг 1.';
} elseif ($step == 2) {
    $title = 'Создание заказа. Шаг 2. (Формируем заказ на ' . Yii::$app->formatter->asDate($model->target_date) . ')';

}

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile('/js/order_form.js', [
    'depends' => [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ]
]);
?>


<hr>
<div class="order-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php if ($step == 1): ?>
        <div class="row">
            <div class="col-xs-3 text-center" style="display: flex; flex-direction: column; align-items: center;">
                <?= $form->field($model, 'target_date')->widget(DatePicker::class, [
                    'type' => DatePicker::TYPE_INLINE,
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                        'multidate' => false
                    ],
                    'options' => [
                        // you can hide the input by setting the following
                        'style' => 'display:none'
                    ],
                ])->label('Выберите дату доставки') ?>
                <?= Html::button('Подтвердить дату ', [
                    'class' => 'btn btn-success btn-block',
                    'id' => 'confirm-order-date',
                ]) ?>
                <input type="text" name="selected_date" id="selected-date" class="hidden">
            </div>
            <div class="col-xs-9" style="min-height: 300px;">
                <div class="nomenclature-loader" style="display: none;">
                    <div class="preloader" style="color: #3c8dbc;">
                        <i class="fa fa-spinner fa-spin fa-fw fa-5x" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="nomenclature" style="display: none;">

                </div>
            </div>
        </div>
    <?php elseif ($step == 2): ?>
        <div class="row">
            <div class="col-xs-6">
                <?php Pjax::begin(['id' => 'selected-product-pjax']) ?>
                <?php
                $counter = 1;
                ?>
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Позиции в заказе</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-bordered table-hover">
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
                            foreach ($productsDataProvider->getModels() as $product): ?>
                            <tr>
                                <td><?= $counter ?></td>
                                <td><?= $product->name ?></td>
                                <td><?= $product->count ?></td>
                                <td><?= $product->measure->name ?></td>
                                <td><?= $product->default_price ?></td>
                                <td><?= $product->count * $product->default_price ?></td>
                                <td><?= Html::a('<i class="fa fa-close">', ['/order/exclude-product'],[
                                        'title' => 'Исключить продукт из списка'
                                    ]) ?></td>

                                <?php $counter++; ?>
                                <?php endforeach; ?>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
                <?php Pjax::end(); ?>
            </div>
            <div class="col-xs-6">
                <?php
                try {
                    echo GridView::widget([
                        'id' => 'crud-datatable',
                        'dataProvider' => $productsDataProvider,
//                        'filterModel' => $searchModel,
                        'pjax' => true,
                        'columns' => require(__DIR__ . '/_nomenclature_columns.php'),
                        'striped' => true,
                        'condensed' => true,
                        'responsive' => true,
                        'toolbar' => false,
                        'panel' => [
                            'type' => 'primary',
                            'heading' => '<i class="glyphicon glyphicon-list"></i> Доступные позиции',
//                    'before' => '<em>* Resize table columns just like a spreadsheet by dragging the column edges.</em>',
                            'after' => MyBulkButtonWidget::widget([
                                    'buttons' => Html::a('<i class="glyphicon glyphicon-ok"></i>&nbsp; Добавить в заказ выделенные позиции',
                                        ["bulk-add"],
                                        [
                                            "class" => "btn btn-success btn-xs",
                                            'role' => 'modal-remote-bulk',
                                        ]),
                                ]) .
                                '<div class="clearfix"></div>',
                        ]
                    ]);
                } catch (Exception $e) {
                    echo $e->getMessage();
                } ?>
            </div>
            <div class="col-xs-4">
                <?= $form->field($model, 'delivery_time_from')->input('time') ?>

                <?= $form->field($model, 'delivery_time_to')->input('time') ?>

                <?= $form->field($model, 'total_price')->input('number') ?>

                <?= $form->field($model, 'comment')->textarea(['rows' => 3]) ?>

                <?= $form->field($model, 'buyer_id')->hiddenInput()->label(false) ?>
            </div>
        </div>

        <?php if (!Yii::$app->request->isAjax) { ?>
            <div class="form-group">
                <?= Html::submitButton('Далее',
                    ['class' => 'btn btn-success btn-block']) ?>
            </div>
        <?php } ?>
    <?php endif; ?>




    <?php ActiveForm::end(); ?>

</div>

