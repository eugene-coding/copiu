<?php

use app\models\OrderLogging;
use yii\helpers\Url;

return [
    [
        'class' => 'kartik\grid\CheckboxColumn',
        'width' => '20px',
    ],
//    [
//        'class' => 'kartik\grid\SerialColumn',
//        'width' => '30px',
//    ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'id',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'created_at',
    // ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'user_id',
        'value' => function (OrderLogging $model) {
            return $model->user->fio;
        }
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'order_id',
        'filter' => OrderLogging::getOrerList()
    ],
//    [
//        'class'=>'\kartik\grid\DataColumn',
//        'attribute'=>'order_info',
//        'label' => 'Информация о заказе',
//        'value' => function(OrderLogging $model){
//            $order = $model->order ?? null;
//            $str = '';
//            if ($order){
//                $str = 'Заказ от ' . Yii::$app->formatter->asDate($order->created_at) . '<br>';
//                $str .= 'Заказ на ' . Yii::$app->formatter->asDate($order->created_at) . '<br>';
//                $str .= 'Сумма ' . Yii::$app->formatter->asCurrency($order->total_price) . '<br>';
//            }
//          return $str;
//        },
//        'format' => 'raw',
//    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'action_type',
        'filter' => OrderLogging::getActionList(),
        'value' => function (OrderLogging $model) {
            return $model::getActionList()[$model->action_type];
        },
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'description',
        'value' => function (OrderLogging $model) {
            return $model->isJson($model->description) ? 'Подробности в карточке' : $model->description;
        },
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'vAlign' => 'middle',
        'urlCreator' => function ($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'viewOptions' => ['role' => 'modal-remote', 'title' => 'View', 'data-toggle' => 'tooltip'],
        'updateOptions' => ['role' => 'modal-remote', 'title' => 'Update', 'data-toggle' => 'tooltip'],
        'deleteOptions' => [
            'role' => 'modal-remote',
            'title' => 'Delete',
            'data-confirm' => false,
            'data-method' => false,// for overide yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => 'Are you sure?',
            'data-confirm-message' => 'Are you sure want to delete this item'
        ],
    ],

];   