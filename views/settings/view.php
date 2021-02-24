<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Settings */
?>
<div class="settings-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'key',
            'value',
            'label',
            'description:ntext',
            'user_id',
            'is_system',
        ],
    ]) ?>

</div>
