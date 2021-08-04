<?php

use kartik\date\DatePicker;

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Order */
/* @var $form yii\widgets\ActiveForm */
/* @var $productsDataProvider \yii\data\ActiveDataProvider */
/* @var $orderToNomenclatureDataProvider \yii\data\ActiveDataProvider */


if ($model->step == 1) {
    $title = 'Создание заказа. Шаг 1.';
} elseif ($model->step == 2) {
    $title = 'Создание заказа. Шаг 2. (Формируем заказ на ' . Yii::$app->formatter->asDate($model->target_date) . ')';
} elseif ($model->step == 3) {
    $title = 'Создание заказа. Шаг 3. (Формируем заказ на ' . Yii::$app->formatter->asDate($model->target_date) . ')';
} elseif ($model->step == 4) {
    $title = 'Создание заказа. Шаг 4. (Формируем заказ на ' . Yii::$app->formatter->asDate($model->target_date) . ')';
} elseif ($model->step == 5) {
    $title = 'Заказ создан на ' . Yii::$app->formatter->asDate($model->target_date);
}
Yii::info('Шаг: ' . $model->step, 'test');
Yii::info($model->attributes, 'test');

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile('/js/order_form.js', [
    'depends' => [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ]
]);

$this->registerJsFile('/js/mobile_detect.min.js', [
    'depends' => [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ]
]);
?>

    <hr>
    <div class="order-form">
<?php $form = ActiveForm::begin(); ?>
<?php if ($model->step != 5): ?>
    <div class="buttons" style="margin-bottom: 2rem">
    <div class="row">
        <div class="col-md-2 col-xs-4">
            <?= Html::a('Отмена', ['/order/cancel', 'id' => $model->id], [
                'class' => 'btn btn-default btn-block',
                'title' => 'Отменить формирование закзаза',
            ]) ?>
        </div>
        <?php if ($model->step == 2 || $model->step == 3): ?>
        <div class="col-md-2 col-xs-4">
            <?= Html::button('Назад', [
                'class' => 'btn btn-info btn-block to-back',
                'title' => 'Вернуться к предыдущему шагу',
                'onClick' => 'history.go(-1);'
            ]) ?>
        </div>
        <div class="col-md-6 col-xs-4">
            <?php else: ?>
            <div class="col-md-8 col-xs-4">
                <?php endif; ?>
            </div>
            <div class="col-md-2 col-xs-4">
                <?= Html::submitButton('Далее', [
                    'class' => 'btn btn-success btn-block',
                    'id' => 'next-btn',
                    'title' => 'Сохранить и продолжить',
                    'style' => $model->step == 1 ? 'display: none;' : '',
                ]) ?>
                <?= Html::button('<i class="fa fa-spinner fa-pulse fa-fw"></i>',
                    [
                        'class' => 'btn btn-success btn-block disabled',
                        'id' => 'fake-next-btn',
                        'style' => 'display:none'
                    ]); ?>
            </div>
        </div>
        <hr>
    </div>
    <?= $form->field($model, 'status')->hiddenInput(['value' => $model::STATUS_DRAFT])->label(false) ?>
<?php endif; ?>
<?php if ($model->step == 1): ?>
    <div class="row">
        <div class="col-md-3 col-xs-12 text-center"
             style="display: flex; flex-direction: column; align-items: center;">
            <?php
            try {
                echo $form->field($model, 'target_date')->widget(DatePicker::class, [
                    'type' => DatePicker::TYPE_INLINE,
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                        'multidate' => false
                    ],
                    'options' => [
                        // you can hide the input by setting the following
                        'style' => 'display:none'
                    ],
                    'pluginEvents' => [
                        'changeDate' => 'function(e) {  
                                    setTimeout(function(){
                                        $("#confirm-order-date").trigger("click");
                                    }, 500);
                                }',
                    ],
                ])->label('Выберите дату доставки');
            } catch (Exception $e) {
                echo $e->getMessage();
            } ?>
            <?= Html::button('Подтвердить дату ', [
                'class' => 'btn btn-success btn-block',
                'id' => 'confirm-order-date',
                'style' => 'display:none;'
            ]); ?>
            <!--                <input type="text" name="selected_date" id="selected-date" class="hidden">-->
        </div>
        <div class="col-md-9 col-xs-12" style="min-height: 300px;">
            <div class="nomenclature-loader" style="display: none;">
                <div class="preloader" style="color: #3c8dbc;">
                    <i class="fa fa-spinner fa-spin fa-fw fa-5x" aria-hidden="true"></i>
                </div>
            </div>
            <div class="nomenclature" style="display: none;"></div>
        </div>
    </div>
<?php elseif ($model->step == 2): ?>
    <h4>Выберите позиции и установите количество</h4>
    <?php if ($model->buyer->min_order_cost): ?>
        <p>Если сумма заказа менее <?= Yii::$app->formatter->asCurrency($model->buyer->min_order_cost) ?>
            будет добавлена услуга
            доставки <?= Yii::$app->formatter->asCurrency($model->buyer->delivery_cost) ?></p>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-4 col-md-push-8">
            <div class="row">
                <div class="col-xs-12 text-center">Укажите временной интервал доставки</div>
                <div class="col-xs-6"><b>C</b><br><?= Html::dropDownList('Order[delivery_time_from]',
                        $model->delivery_time_from,
                        $model->buyer->getDeliveryTimeIntervals('from'), [
                            'class' => 'form-control',
                            'prompt' => '...'

                        ]) ?></div>
                <div class="col-xs-6"><b>По</b><br><?= Html::dropDownList('Order[delivery_time_to]',
                        $model->delivery_time_to,
                        $model->buyer->getDeliveryTimeIntervals('to'), [
                            'class' => 'form-control',
                            'prompt' => '...',
                        ]) ?></div>
                <div class="error-time text-center col-xs-12">
                    <?= $form->field($model, 'error_delivery_time')->hiddenInput()->label(false) ?>
                </div>
                <div class="col-xs-12"><b>Комментарий</b><br><?= Html::textarea('Order[comment]', $model->comment,
                        ['class' => 'form-control', 'rows' => 5]) ?></div>
            </div>
        </div>
        <div class="col-md-8 col-md-pull-4">
            <div class="row step-2-content" style="margin-top: 10px">
                <div class="col-md-offset-3 col-md-6">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100"
                             aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                            <span>Загрузка...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($model->step == 3): ?>
    <?= $this->render('_pre_order_form', [
        'model' => $model,
        'form' => $form,
    ]) ?>
<?php elseif ($model->step == 4): ?>
    <div class="done text-center">
        <?php if ($model->invoice_number && $model->invoice_number != 'error'): ?>
            <h4>Накладная № <?= $model->invoice_number; ?> успешно создана</h4>
        <?php else: ?>
            <h4 class="text-danger">Ошибка создания накладной</h4>
        <?php endif; ?>
        <?php if ($model->deliveryCost): ?>
            <?php if ($model->delivery_act_number && $model->delivery_act_number != 'error'): ?>
                <h4>Акт оказания услуг (доставка) <?= $model->delivery_act_number; ?> успешно создан</h4>
            <?php else: ?>
                <h4 class="text-danger">Ошибка создания акта оказания услуг</h4>
            <?php endif; ?>
        <?php endif; ?>
        <?= Html::a('Завершить', ['/order'], [
            'class' => 'btn btn-success'
        ]) ?>
    </div>
<?php endif; ?>

    <div class="row">
        <div class="col-xs-12">
            <?= $form->field($model, 'buyer_id')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'step')->hiddenInput([
                'id' => 'order-step',
                'data-id' => $model->id,
            ])->label(false) ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>
    </div>
<?php
$script = <<<JS
    $(function() {
        var step_block = $('#order-step');
        var id = step_block.attr('data-id');
        var step = step_block.val();
        var detect = new MobileDetect(window.navigator.userAgent);
      
      if (step == 2 && detect.mobile){
          $.get('/order/get-content', {
              order_id:id,
              is_mobile:detect.mobile()
          })
          .done(function(response) {
              $('.step-2-content').html(response);
          });
      }
  
    $(document).on('click', '#next-btn', function() {
        $(this).hide();
        $('#fake-next-btn').show();
    });
      $(document).on('click', '#next-btn', function() {
        $(this).parents('.buttons').find('button').attr('disabled', true);
        $(this).parents('.buttons').find('a').attr('disabled', true);
        $(this).attr('disabled', false);
        $(this).hide();
        $('#fake-next-btn').show();
    });
});
JS;
$this->registerJs($script);