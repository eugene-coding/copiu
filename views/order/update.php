<?php

/* @var $this yii\web\View */
/* @var $model app\models\Order */
/* @var $productsDataProvider \yii\data\ActiveDataProvider */
/* @var $orderToNomenclatureDataProvider \yii\data\ActiveDataProvider */
?>
<div class="order-update">

    <?= $this->render('_form', [
        'model' => $model,
        'productsDataProvider' => $productsDataProvider,
        'orderToNomenclatureDataProvider' => $orderToNomenclatureDataProvider,
    ]) ?>

</div>
