<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\FavoriteProduct */
?>
<div class="favorite-product-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'buyer_id',
            'obtn_id',
            'count',
            'status',
            'note:ntext',
        ],
    ]) ?>

</div>
