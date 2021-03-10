<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\OrderBlank */
?>
<div class="order-blank-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'number',
            'date',
            'time_limit:datetime',
            'day_limit',
            'synced_at',
        ],
    ]) ?>

</div>
