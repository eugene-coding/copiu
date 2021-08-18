<?php

/* @var $this yii\web\View */
/* @var $model app\models\Buyer */
/* @var $user_model app\models\Users */
/* @var $addresses app\models\BuyerAddress */
?>
<div class="buyer-update">

    <?= $this->render('_form', [
        'model' => $model,
        'user_model' => $user_model,
        'addresses' => $addresses,
    ]) ?>

</div>
