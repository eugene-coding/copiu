<?php

/* @var $this yii\web\View */
/* @var $model app\models\Order */
/* @var int $step Шаг заказа */
/* @var $productsDataProvider \yii\data\ActiveDataProvider */
?>
<div class="order-update">

    <?= $this->render('_form', [
        'model' => $model,
        'step' => $step,
        'productsDataProvider' => $productsDataProvider
    ]) ?>

</div>
