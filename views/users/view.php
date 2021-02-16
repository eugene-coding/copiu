<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Users */
?>
<div class="users-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'fio',
            'login',
//            'password',
            'role',
            'phone',
            'email:email',
            'avatar',
        ],
    ]) ?>

</div>
