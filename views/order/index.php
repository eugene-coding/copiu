<?php

use app\models\Settings;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

if (Settings::checkSettings()['success']) {
    $create_order_btn = Html::a('<i class="glyphicon glyphicon-plus"></i> Добавить заказ', ['order-create'],
        [
            'title' => 'Добавить заказ',
            'data-pjax' => 0,
            'class' => \app\models\User::isAdmin() ? 'hidden' : 'btn btn-default',
        ]);
} else {
    $create_order_btn = $create_order_btn = Html::a('<i class="glyphicon glyphicon-warning-sign text-danger"></i> Добавить заказ',
        ['/order/show-order-error-settings'],
        [
            'title' => 'Добавить заказ',
            'role' => 'modal-remote',
            'class' => \app\models\User::isAdmin() ? 'hidden' : 'btn btn-default',
        ]);;
}

$this->title = 'Заказы';
$this->params['breadcrumbs'][] = $this->title;

try {
    $this->registerJsFile('/js/order_form.js', [
        'depends' => [
            'yii\web\YiiAsset',
            'yii\bootstrap\BootstrapAsset',
        ]
    ]);
} catch (InvalidConfigException $e) {
    echo $e->getMessage();
}

?>
<div class="order-index">
    <div id="ajaxCrudDatatable">
        <?php
        try {
            echo GridView::widget([
                'id' => 'crud-datatable',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax' => true,
                'columns' => require(__DIR__ . '/_columns.php'),
                'toolbar' => [
                    [
                        'content' => $create_order_btn .
                            Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
                                ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => 'Reset Grid']) .
                            '{toggleData}' .
                            '{export}'
                    ],
                ],
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'panel' => [
                    'type' => 'primary',
                    'heading' => '<i class="glyphicon glyphicon-list"></i> Список заказов',
//                    'before' => '<em>* Resize table columns just like a spreadsheet by dragging the column edges.</em>',
                    'after' => '<div class="clearfix"></div>',
                ]
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        } ?>
    </div>
</div>

