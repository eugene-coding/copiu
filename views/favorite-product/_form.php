<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\FavoriteProduct */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="favorite-product-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'count')->input('number') ?>

    <?= $form->field($model, 'buyer_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'obtn_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'status')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'note')->hiddenInput()->label(false) ?>

    <?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
