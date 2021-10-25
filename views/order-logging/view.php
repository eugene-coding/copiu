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
                [
                    'attribute' => 'user_id',
                    'value' => '#' . $model->user_id . '. ' . $model->user->fio
                ],
                'order_id',
                [
                    'attribute' => 'order_info',
                    'value' => function (OrderLogging $model) {
                        $order = $model->order ?? null;
                        $str = '';
                        if ($order) {
                            $str = 'Заказ от ' . Yii::$app->formatter->asDate($order->created_at) . '<br>';
                            $str .= 'Заказ на ' . Yii::$app->formatter->asDate($order->target_date) . '<br>';
                            $str .= 'Сумма ' . Yii::$app->formatter->asCurrency($order->total_price) . '<br>';
                        }
                        return $str;
                    },
                    'format' => 'raw'
                ],
                [
                    'attribute' => 'action_type',
                    'value' => OrderLogging::getActionList()[$model->action_type],
                ],
                [
                    'attribute' => 'description',
                    'value' => function (OrderLogging $model){
                        if (!$model->isJson($model->description)){
                            return $model->description;
                        } else {
                            return 'Данные в JSON';
                        }
                    },
                ],
            ],
        ]);
    } catch (Exception $e) {
        echo $e->getMessage();
    } ?>

    <?php \yii\helpers\VarDumper::dump(json_decode($model->model, true), 10, true) ?>
    <?php
    if ($model->isJson($model->description)){
        \yii\helpers\VarDumper::dump(json_decode($model->description, true), 10, true);
    }
    ?>
</div>
