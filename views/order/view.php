<?php

use app\models\Order;
use app\models\OrderBlankToNomenclature;
use app\models\Users;
use yii\helpers\Html;
use yii\helpers\Url;
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
                        . Yii::$app->formatter->asTime($model->delivery_time_from)
                        . ' до '
                        . Yii::$app->formatter->asTime($model->delivery_time_to),
                    'label' => 'Время доставки',
                    'format' => 'raw'
                ],
                [
                    'attribute' => 'delivery_address_id',
                    'value' => $model->address->address ?? null,
                    'label' => 'Адрес доставки',
                    'visible' => (bool)$model->delivery_address_id,
                ],
                'total_price:currency',
                [
                    'attribute' => 'delivery',
                    'value' => Yii::$app->formatter->asCurrency($model->deliveryCost),
                    'label' => 'Доставка',
                ],
                'comment:ntext',
                [
                    'attribute' => 'invoice_number',
                    'value' => function (Order $model) {
                        if (Users::isAdmin()) {
                            $invoice =  Html::a($model->invoice_number, Url::to(['/uploads/out_invoice/' . $model->invoice_number . '.xml']), [
                                'download' => true,
                                'data-pjax' => 0
                            ]);
                            $response =  Html::a('Ответ сервера', Url::to(['/uploads/out_invoice/' . $model->invoice_number . '_response.xml']), [
                                'download' => true,
                                'data-pjax' => 0
                            ]);
                            return $invoice . '<br>' . $response;
                        } else {
                            return $model->invoice_number;
                        }
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'delivery_act_number',
                    'value' => function (Order $model) {
                        if (Users::isAdmin()) {
                            return Html::a($model->delivery_act_number, Url::to(['/uploads/out_act/' . $model->delivery_act_number . '.xml'], [
                                    'download' => true,
                                    'data-pjax' => 0
                                ]));
                        } else {
                            return $model->delivery_act_number;
                        }
                    },
                    'format' => 'raw',
                ],
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
            <div class="order-view">
                <table class="table table-hover table-responsive">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Наименование</th>
                        <th class="order-view-measure">Ед. изм.</th>
                        <th>Цена</th>
                        <th>Кол-во</th>
                        <th>Сумма</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php /** @var OrderBlankToNomenclature $obtn */
                    foreach ($model->getObtns() as $obtn): ?>
                        <?php
                        //Yii::debug($obtn->attributes, 'test');
                        $product = $obtn->n;
                        //Yii::debug($product->attributes, 'test');
                        $count = $obtn->getCount($model->id);
                        $price = $obtn->getPriceForOrder($model->id);
//                        Yii::debug('Продукт: ' . $product->name, 'test');
//                        Yii::debug('Цена: ' . $price, 'test');
                        ?>
                        <tr>
                            <td><?= $counter; ?></td>
                            <td><?= $product->name ?></td>
                            <td class="order-view-measure"><?= $product->findMeasure($obtn) ?></td>
                            <td><?= $price ?></td>
                            <td><?= $count ?></td>
                            <td><?= $count * $price; ?></td>
                        </tr>
                        <?php
                        $counter++;
                        ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
