<?php

use app\models\Settings;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

if (Settings::checkSettings()['success']) {
    $create_order_btn = Html::a('<i class="ri-add-circle-line"></i> Добавить заказ', ['order-create'],
        [
            'title' => 'Добавить заказ',
            'data-pjax' => 0,
            'class' => \app\models\User::isAdmin() ? 'hidden' : 'btn btn-primary btn-lg',
        ]);
} else {
    $create_order_btn = $create_order_btn = Html::a('<i class="glyphicon glyphicon-warning-sign text-danger"></i> Добавить заказ',
        ['/order/show-order-error-settings'],
        [
            'title' => 'Добавить заказ',
            'role' => 'modal-remote',
            'class' => \app\models\User::isAdmin() ? 'hidden' : 'btn btn-primary',
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
        <div class="col-sm-12 col-lg-12">
            <div class="card">

                <div class="card-header d-flex justify-content-between">
                    <div class="d-flex flex-wrap align-items-center justify-content-between breadcrumb-content">
                        <h4>Заказы</h4>
                    </div>
                    <div class="d-flex flex-wrap align-items-lg-end">
                        <?=$create_order_btn?>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        echo GridView::widget([
                            'id' => 'crud-datatable',
                            'dataProvider' => $dataProvider,
//                            'filterModel' => $searchModel,
                            'pjax' => true,
                            'columns' => require(__DIR__ . '/_columns.php'),
                            'toolbar' => false,
                            'striped' => true,
                            'panel' => false
                        ]);
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>

