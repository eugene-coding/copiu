<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Order */
?>
<div class="order-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'buyer_id',
            'created_at',
            'target_date',
            'delivery_time_from',
            'delivery_time_to',
            'total_price',
            'comment:ntext',
        ],
    ]) ?>

</div>
