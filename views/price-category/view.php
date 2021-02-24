<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\PriceCategory */
?>
<div class="price-category-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'outer_id',
            'name:ntext',
        ],
    ]) ?>

</div>
