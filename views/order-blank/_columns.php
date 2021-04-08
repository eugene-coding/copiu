<?php

use app\models\OrderBlank;
use yii\helpers\Url;

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
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'number',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'date',
        'format' => 'date'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'time_limit',
        'format' => 'time'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'day_limit',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'show_to_all',
        'filter' => [1 => 'Виден всем', 0 => 'Только выбранным'],
        'content' => function (OrderBlank $model){
            return $model->show_to_all ? 'Виден всем' : 'Только выбранным';
        }
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'synced_at',
        'value' => function (OrderBlank $model){
            if (!$model->synced_at){
                return '';
            }
            return date('d.m.Y H:i', strtotime($model->synced_at));
        }
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{update} {delete}',
        'dropdown' => false,
        'vAlign'=>'middle',
        'urlCreator' => function($action, $model, $key, $index) { 
                return Url::to([$action,'id'=>$key]);
        },
        'viewOptions'=>['role'=>'modal-remote','title'=>'View','data-toggle'=>'tooltip'],
        'updateOptions'=>['role'=>'modal-remote','title'=>'Редактировать', 'data-toggle'=>'tooltip'],
        'deleteOptions'=>['role'=>'modal-remote','title'=>'Delete', 
                          'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                          'data-request-method'=>'post',
                          'data-toggle'=>'tooltip',
                          'data-confirm-title'=>'Are you sure?',
                          'data-confirm-message'=>'Are you sure want to delete this item'], 
    ],

];   