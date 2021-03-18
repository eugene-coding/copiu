<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Order */

$counter = 1;
?>
<div class="order-view">
    <?php
    try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'created_at:datetime',
                'target_date:date',
                [
                    'attribute' => 'delivery_time_from',
                    'value' => 'с '
                        . Yii::$app->formatter->asTime($model->delivery_time_from )
                        . ' до '
                        . Yii::$app->formatter->asTime($model->delivery_time_to ),
                    'label' => 'Время доставки',
                ],
                'total_price:currency',
                [
                    'attribute' => 'delivery',
                    'value' => Yii::$app->formatter->asCurrency($model->deliveryCost),
                    'label' => 'Доставка',
                ],
                'comment:ntext',
                'invoice_number',
                'delivery_act_number'
            ],
        ]);
    } catch (Exception $e) {
        echo $e->getMessage();
    } ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            Список заказанных позиций
        </div>
        <div class="panel-body">
            <table class="table table-hover">
               <thead>
               <tr>
                   <th>#</th>
                   <th>Наименование</th>
                   <th>Цена</th>
                   <th>Кол-во</th>
                   <th>Сумма</th>
               </tr>
               </thead>
                <tbody>
                <?php foreach($model->products as $product):?>
                <?php
                    $count = $product->getCount($model->id);
                    $price = $product->getPriceForOrder($model->id);
                    ?>
                <tr>
                    <td><?= $counter; ?></td>
                    <td><?= $product->name ?></td>
                    <td><?= $price ?></td>
                    <td><?= $count ?></td>
                    <td><?=  $count * $price ; ?></td>
                </tr>
                <?php
                    $counter++;
                    ?>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>

</div>
