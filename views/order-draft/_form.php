<?php

use app\models\BlankTab;
use app\models\Order;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $draft app\models\OrderDraft */
/* @var $form yii\widgets\ActiveForm */
/* @var $order app\models\Order */
/* @var $productsDataProvider \yii\data\ArrayDataProvider */

$this->title = $draft->isNewRecord ? 'Добавление черновика заказа' : 'Редактирование черновика заказа';
$this->params['breadcrumbs'][] = 'Черновики заказов ';
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="order-draft-form">

        <?php $form = ActiveForm::begin(); ?>
        <?php if (!Yii::$app->request->isAjax) { ?>
            <div class="col-md-2 col-sm-12 col-md-offset-10">
                <div class="form-group">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success btn-block']) ?>
                </div>
            </div>
        <?php } ?>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($draft, 'name')->textInput(['placeholder' => 'Введите наименование черновика']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 required form-group">
                <div class="comment-label control-label">
                    <label class="control-label">Комментарий
                        <span class="count-symbol"><?= mb_strlen($order->comment) ? '(' . mb_strlen($order->comment) . ' симв.)' : '' ?></span>
                    </label>
                </div>
                <br><?= Html::textarea('Order[comment]', $order->comment,
                    [
                        'id' => 'order-comment',
                        'class' => 'form-control',
                        'rows' => 5,
                        'placeholder' => 'Комментарий должен содержать не более 255 символов'
                    ]) ?>
            </div>
        </div>


        <?= $form->field($order, 'buyer_id')->hiddenInput()->label(false) ?>
        <?= $form->field($order, 'status')->hiddenInput(['value' => Order::STATUS_ORDER_DRAFT])->label(false) ?>


        <div class="col-md-12">
            <h4>Выберите позиции и установите количество</h4>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div>
                    <!-- Навигационные вкладки -->
                    <ul class="nav nav-pills nav-justified" role="tablist">
                        <?php
                        foreach ($productsDataProvider->getModels() as $tab_id => $products): ?>
                            <li role="presentation" style="border: 1px solid grey;">
                                <a href="#tab-<?= $tab_id ?>" aria-controls="<?= $tab_id; ?>"
                                   role="tab" data-toggle="tab">
                                    <?= $tab_id; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                    <!-- Вкладки панелей -->
                    <div class="tab-content">
                        <?php foreach ($productsDataProvider->getModels() as $tab_id => $products): ?>
                            <div role="tabpanel" class="tab-pane" id="tab-<?= $tab_id ?>">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?= $this->render('/order/_nomenclature', [
                                            'model' => $order,
                                            'dataProvider' => $products,
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>


        <?php if (!Yii::$app->request->isAjax) { ?>
            <div class="row" style="margin-top: 1rem">
                <div class="col-md-2 col-sm-12 col-md-offset-10">
                    <div class="form-group">
                        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success btn-block']) ?>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php ActiveForm::end(); ?>

    </div>
<?php
$script = <<<JS
$(function() {
   var price_all = $('#price-all').val();
   $('.total').html('Итого: ' + price_all + 'р.');
});
 $(document).on('keyup', '#order-comment', function() {
   let length = $(this).val().length; 
   let c_symbols = $('.count-symbol');
   if (length > 255){
        c_symbols.addClass('text-danger');
        c_symbols.html('(' + $(this).val().length + ' симв.)');
   } else if(length > 0) {
        c_symbols.removeClass('text-danger');
        c_symbols.html('(' + $(this).val().length + ' симв.)');
   } else {
       c_symbols.html('');
   }
});      
JS;
$this->registerJS($script);
