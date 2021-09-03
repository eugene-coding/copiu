<?php

/* @var $this yii\web\View */
/* @var $draft app\models\OrderDraft */
/* @var $order app\models\Order */
/* @var $productsDataProvider \yii\data\ArrayDataProvider */
?>
<div class="order-draft-update">

    <?= $this->render('_form', [
        'draft' => $draft,
        'order' => $order,
        'productsDataProvider' => $productsDataProvider,
    ]) ?>

</div>
