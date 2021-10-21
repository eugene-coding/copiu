<?php

use app\models\OrderLogging;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\OrderLogging */
?>
<div class="order-logging-view">

    <?php
    try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'created_at',
                'user_id',
                'order_id',
                [
                    'attribute' => 'order_info',
                    'value' => function(OrderLogging $model){
                        $order = $model->order ?? null;
                        $str = '';
                        if ($order){
                            $str = 'Заказ от ' . Yii::$app->formatter->asDate($order->created_at) . '<br>';
                            $str .= 'Заказ на ' . Yii::$app->formatter->asDate($order->created_at) . '<br>';
                            $str .= 'Сумма ' . Yii::$app->formatter->asCurrency($order->total_price) . '<br>';
                        }
                        return $str;
                    },
                    'format' => 'raw'
                ],
                'action_type',
                'description:ntext',
            ],
        ]);
    } catch (Exception $e) {
        echo $e->getMessage();
    } ?>

    <?php \yii\helpers\VarDumper::dump(json_decode($model->model, true), 10, true) ?>

</div>
