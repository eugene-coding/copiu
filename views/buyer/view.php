<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Buyer */
?>
<div class="buyer-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'pc_id',
            'user_id',
            'outer_id',
        ],
    ]) ?>

</div>
