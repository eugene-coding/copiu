<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Users */

$this->title = $model->fio;
?>
<div class="users-view">

    <?php
    try {
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'fio',
                'login',
            ],
        ]);
    } catch (Exception $e) {
        echo $e->getMessage();
    } ?>

</div>
