<?php

use app\models\Order;
use app\models\Settings;
use app\models\Users;
use yii\helpers\Html;
use yii\helpers\Url;

return [
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'id',
        'label' => '№',
        'value' => function (Order $model){
            return 'N' . str_pad($model->id, 5, 0, STR_PAD_LEFT);
        },
        'visible' => Users::isAdmin(),
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'buyer_name',
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
            $str = 'Дата:&nbsp;' . Yii::$app->formatter->asDate($model->target_date) . '<br>';
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
            $str = 'Заказ:&nbsp;' . Yii::$app->formatter->asCurrency($model->total_price) . '<br>';
            $str .= 'Доставка:&nbsp;' . Yii::$app->formatter->asCurrency($model->deliveryCost) . '<br>';
            $str .= '<b>Итого:&nbsp;' . Yii::$app->formatter->asCurrency($model->total_price + $model->deliveryCost) . '</b>';
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

            if ($status == $model::getStatusList()[$model::STATUS_ERROR]) {
                return $status . '<br><small class="text-warning">Повторите формирование документов</small>';
            } else {
                return $status;
            }
        },
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{re-make-documents} {copy-order} {update} {view} {delete}',
        'dropdown' => false,
        'vAlign' => 'middle',
        'urlCreator' => function ($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'buttons' => [
            're-make-documents' => function ($url, Order $model) {
                if ($model->status == $model::STATUS_ERROR) {
                    return Html::a('<i class="glyphicon glyphicon-repeat text-danger"></i>',
                        ['/order/re-make-documents', 'id' => $model->id],
                        [
                            'title' => 'Повторить формирование недостающих документов',
                            'data-pjax' => 0,
                        ]);
                }
                return null;
            },
            'copy-order' => function ($url, Order $model) {
                if ($model->status != $model::STATUS_DRAFT
                    && $model->status != $model::STATUS_ERROR
                    && Settings::checkSettings()['success']
                    && Users::isBuyer()) {
                    return Html::a('<i class="glyphicon glyphicon-copy"></i>',
                        ['/order/copy-order', 'basis_order_id' => $model->id],
                        [
                            'title' => 'Сформировать заказ на основе текущего',
                            'data-pjax' => 0,
                        ]);
                }
                return null;
            },
            'delete' => function ($url, Order $model) {
                if ($model->status == $model::STATUS_DRAFT || $model->status == $model::STATUS_DONE) {
                    if ($model->status == $model::STATUS_DRAFT){
                        $url = Url::toRoute(['/order/delete-draft', 'id' => $model->id]);
                    }
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
                return null;
            },
            'update' =>  function ($url, Order $model) {
                return Html::a('<i class="glyphicon glyphicon-pencil"></i>',
                    ['/order/update-draft', 'id' => $model->id],
                    [
                        'title' => 'Редактирование заказа',
                        'data-pjax' => 0,
                    ]);
            },
        ],
        'viewOptions' => ['role' => 'modal-remote', 'title' => 'View', 'data-toggle' => 'tooltip'],
        'updateOptions' => ['role' => 'modal-remote', 'title' => 'Редактировать', 'data-toggle' => 'tooltip'],
        'deleteOptions' => [
            'role' => 'modal-remote',
            'title' => 'Удалить',
            'data-confirm' => false,
            'data-method' => false,// for overide yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => 'Вы уверены?',
            'data-confirm-message' => 'Действительно удалить заказ?',
            'data-confirm-ok' => 'Удалить',
            'data-confirm-cancel' => 'Отмена',
        ],
        'visibleButtons' => [
            'update' => function (Order $model) {
                return $model->status == $model::STATUS_DRAFT;
            }
        ]
    ],

];   