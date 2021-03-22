<?php

use app\models\Order;
use app\models\Users;
use yii\helpers\Html;
use yii\helpers\Url;

return [
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
//    [
//        'class' => '\kartik\grid\DataColumn',
//        'attribute' => 'id',
//    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'buyer_id',
        'content' => function (Order $model) {
            return $model->buyer->name;
        },
        'visible' => \app\models\Users::isAdmin(),
    ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'created_at',
    // ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'target_date',
        'label' => 'Дата и время доставки',
        'content' => function (Order $model) {
            $str = 'Дата: ' . Yii::$app->formatter->asDate($model->target_date) . '<br>';
            $str .= 'c ' . Yii::$app->formatter->asTime($model->delivery_time_from);
            $str .= ' до ' . Yii::$app->formatter->asTime($model->delivery_time_to);
            return $str;
        },
        'format' => 'raw',
    ],
//    [
//        'class' => '\kartik\grid\DataColumn',
//        'attribute' => 'delivery_time_from',
//    ],
//    [
//        'class'=>'\kartik\grid\DataColumn',
//        'attribute'=>'delivery_time_to',
//    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'total_price',
        'content' => function (Order $model) {
            $str = 'Заказ: ' . Yii::$app->formatter->asCurrency($model->total_price) . '<br>';
            $str .= 'Доставка: ' . Yii::$app->formatter->asCurrency($model->deliveryCost) . '<br>';
            $str .= '<b>Итого: ' . Yii::$app->formatter->asCurrency($model->total_price + $model->deliveryCost) . '</b>';
            return $str;
        },
        'format' => 'raw',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'comment',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'status',
        'filter' => Order::getStatusList(),
        'content' => function (Order $model) {
            if (Users::isAdmin()) {
                return Html::dropDownList('statuses', $model->status, Order::getStatusList(), [
                    'class' => 'form-control status-dropbox',
                    'data-id' => $model->id,
                ]);
            }

            $status = Order::getStatusList()[$model->status];

            if ($status == $model::getStatusList()[$model::STATUS_DRAFT]){
                return $status . '<br><small class="text-warning">Для принятия заказа в работу повторите формирование документов</small>';
            } else {
                return $status;
            }
        },
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{re-make-documents} {copy-order} {view} {delete}',
        'dropdown' => false,
        'vAlign' => 'middle',
        'urlCreator' => function ($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'buttons' => [
            're-make-documents' => function ($url, Order $model) {
                if ($model->status == $model::STATUS_DRAFT &&
                    ($model->invoice_number == 'error' || $model->delivery_act_number == 'error')) {
                    return Html::a('<i class="glyphicon glyphicon-repeat text-danger"></i>',
                        ['/order/re-make-documents', 'id' => $model->id],
                        [
                            'title' => 'Повторить формирование недостающих документов',
                            'data-pjax' => 0,
                        ]);
                }
            },
            'copy-order' => function ($url, Order $model) {
                if ($model->status != $model::STATUS_DRAFT) {
                    return Html::a('<i class="glyphicon glyphicon-copy"></i>',
                        ['/order/copy-order', 'id' => $model->id],
                        [
                            'title' => 'Сформировать заказ на основе текущего',
                            'data-pjax' => 0,
                        ]);
                }
            },
            'delete' => function ($url, Order $model) {
                if ($model->status == $model::STATUS_DRAFT || $model->status == $model::STATUS_DONE) {
                    return Html::a('<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>',
                        $url, [
                            'role' => 'modal-remote',
                            'title' => 'Delete',
                            'data-confirm' => false,
                            'data-method' => false,// for overide yii data api
                            'data-request-method' => 'post',
                            'data-toggle' => 'tooltip',
                            'data-confirm-title' => 'Вы уверены?',
                            'data-confirm-message' => 'Подтвердите удаление заказа',
                            'data-confirm-ok' => 'Удалить',
                            'data-confirm-cancel' => 'Отмена',
                        ]);
                }
            },
            'update' => function ($url, Order $model) {
                if ($model->status == $model::STATUS_DRAFT) {
                    return Html::a('<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>',
                        $url);
                }
            }
        ],
        'viewOptions' => ['role' => 'modal-remote', 'title' => 'View', 'data-toggle' => 'tooltip'],
        'updateOptions' => ['role' => 'modal-remote', 'title' => 'Редактировать', 'data-toggle' => 'tooltip'],
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