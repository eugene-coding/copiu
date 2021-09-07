<?php

use app\models\OrderDraft;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $searchModel app\models\search\OrderDraftSearch */

return [
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'id',
    // ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'name',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'order_id',
        'label' => 'Заказ на',
        'filter' => Html::input('date', 'OrderDraftSearch[target_date]', $searchModel->target_date,
            ['class' => 'form-control']),
        'value' => function (OrderDraft $model) {
            return $model->order->target_date ?? null;
        },
        'format' => 'date',
    ],
//    [
//        'class' => '\kartik\grid\DataColumn',
//        'attribute' => 'plan_send_date',
//        'filter' => Html::input('date', 'OrderDraftSearch[plan_send_date]', $searchModel->plan_send_date,
//            ['class' => 'form-control']),
//        'label' => 'Дата отправки заказа',
//        'format' => 'date',
//    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'to_work',
        'label' => '',
        'value' => function (OrderDraft $model) {
            if (!$model->plan_send_date) {
                return Html::a('Поставить в очередь', ['to-queue', 'id' => $model->id], [
                    'class' => 'btn btn-success btn-block to-work-btn',
                    'role' => 'modal-remote',
                    'title' => 'Постановка заказа в очередь на отправку',
                ]);
            } elseif($model->plan_send_date && !$model->send_at) {
                $plan_send_date = Yii::$app->formatter->asDate($model->plan_send_date);
                return "В ожидании отправки ({$plan_send_date})";
            } else {
                return "Отправлено " . Yii::$app->formatter->asDateTime($model->send_at);
            }
        },
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'vAlign' => 'middle',
        'template' => '{reset} {view} {update} {delete}',
        'urlCreator' => function ($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'buttons' => [
            'delete' => function ($url, OrderDraft $model) {
                return Html::a('<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>',
                    ['delete-draft', 'id' => $model->id], [
                        'role' => 'modal-remote',
                        'title' => 'Удалить',
                        'data-confirm' => false,
                        'data-method' => false,// for overide yii data api
                        'data-request-method' => 'post',
                        'data-toggle' => 'tooltip',
                        'data-confirm-title' => 'Вы уверены?',
                        'data-confirm-message' => 'Подтвердите удаление черновика <b>"' . $model->name . '"</b>',
                        'data-confirm-ok' => 'Удалить',
                        'data-confirm-cancel' => 'Отмена',
                    ]);
            },
            'reset' => function ($url, OrderDraft $model) {
                return Html::a('<span class="glyphicon glyphicon-ban-circle text-danger" aria-hidden="true"></span>',
                    ['reset-draft', 'id' => $model->id], [
                        'role' => 'modal-remote',
                        'title' => 'Сброс. (Возвращение черновика в изначальное состояние)',
                        'data-confirm' => false,
                        'data-method' => false,// for overide yii data api
                        'data-request-method' => 'post',
                        'data-toggle' => 'tooltip',
                        'data-confirm-title' => 'Вы уверены?',
                        'data-confirm-message' => 'Подтвердите сброс черновика <b>"' . $model->name . '"</b>.<br>Дата Заказа и дата отправки будут сброшены',
                        'data-confirm-ok' => 'Сбросить',
                        'data-confirm-cancel' => 'Отмена',
                    ]);
            }
        ],
        'viewOptions' => ['role' => 'modal-remote', 'title' => 'View', 'data-toggle' => 'tooltip'],
        'updateOptions' => ['data-pjax' => 0, 'title' => 'Update', 'data-toggle' => 'tooltip'],
        'visibleButtons' => [
            'reset' => function(OrderDraft $model){
                if ($model->order->status == \app\models\Order::STATUS_ORDER_WAITING){
                    return true;
                }

                return false;
            },
            'update' => function(OrderDraft $model){
                if ($model->order->status == \app\models\Order::STATUS_ORDER_WAITING){
                    return false;
                }

                return true;
            },
        ]
    ],

];   