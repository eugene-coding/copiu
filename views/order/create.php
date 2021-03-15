<?php

/* @var $this yii\web\View */
/* @var $model app\models\Order */
/* @var int $step Шаг заказа */

?>
<div class="order-create">
    <?= $this->render('_form', [
        'model' => $model,
        'step' => $step,
    ]) ?>
</div>
