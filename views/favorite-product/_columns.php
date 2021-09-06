<?php

use app\models\FavoriteProduct;
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
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'product_name',
        'value' => function (FavoriteProduct $model) {
            return $model->obtn->n->name ?? '';
        },
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'blank_id',
        'filter' => FavoriteProduct::getBlanks(),
        'value' => function (FavoriteProduct $model) {
            $blank = $model->blank;
            $blank_name = $blank->number;

            return "{$blank_name}";
        },
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'count',
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{update} {delete}',
        'dropdown' => false,
        'vAlign' => 'middle',
        'urlCreator' => function ($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'viewOptions' => ['role' => 'modal-remote', 'title' => 'View', 'data-toggle' => 'tooltip'],
        'updateOptions' => ['role' => 'modal-remote', 'title' => 'Редактировать', 'data-toggle' => 'tooltip'],
        'deleteOptions' => [
            'role' => 'modal-remote',
            'title' => 'Удалить из избранного',
            'data-confirm' => false,
            'data-method' => false,// for overide yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => 'Вы уверены?',
            'data-confirm-message' => 'Подтвердите удаление продукта из избранного',
            'data-confirm-ok' => 'Удалить из избраннного',
            'data-confirm-cancel' => 'Отмена',
        ],
    ],

];   