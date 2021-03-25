<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\OrderBlankSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Бланки заказа';
$this->params['breadcrumbs'][] = $this->title;

try {
    $this->registerJsFile('/js/order_blank.js', [
        'depends' => [
            'yii\web\YiiAsset',
            'yii\bootstrap\BootstrapAsset',
        ]
    ]);
} catch (\yii\base\InvalidConfigException $e) {
    echo $e->getMessage();
}
?>
<div class="order-blank-index">
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
                        'content' =>
                            Html::a('<i class="glyphicon glyphicon-plus"></i> Добавить', ['create'],
                                [
                                    'role' => 'modal-remote',
                                    'title' => 'Добавить накладную',
                                    'class' => 'btn btn-default'
                                ]) .
                            Html::button('<i class="glyphicon glyphicon-sort"></i> Синхронизировать',
                                [
                                    'title' => 'Синронизация накладных',
                                    'class' => 'btn btn-default',
                                    'id' => 'sync-order-blank-btn',
                                    'data-url' => '/order-blank/syncing'
                                ]) .
                            '{toggleData}' .
                            '{export}'
                    ],
                ],
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'panel' => [
                    'type' => 'primary',
                    'before' => '<div id="before-panel-message" style="display: none;"></div>',
                    'heading' => '<i class="glyphicon glyphicon-list"></i> Список накладных',
                    'after' => '<div class="clearfix"></div>',
                ]
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        } ?>
    </div>
</div>
