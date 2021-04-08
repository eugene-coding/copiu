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

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile('/js/order_form.js', [
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
            <div class="col-xs-2">
                <?= Html::a('Отмена', ['/order/cancel', 'id' => $model->id], [
                    'class' => 'btn btn-default btn-block',
                    'title' => 'Отменить формирование закзаза',
                ]) ?>
            </div>
            <?php if ($model->step == 3): ?>
            <div class="col-xs-2">
                <?= Html::a('Назад', ['/order/order-update', 'id' => $model->id, 'step' => 2], [
                    'class' => 'btn btn-info btn-block to-back',
                    'title' => 'Вернуться ко второму шагу',
                ]) ?>
            </div>
            <div class="col-xs-6">
                <?php else: ?>
                <div class="col-xs-8">
                    <?php endif; ?>
                </div>
                <div class="col-xs-2">
                    <?= Html::submitButton('Далее', [
                        'class' => 'btn btn-success btn-block',
                        'title' => 'Сохранить и продолжить',
                    ]) ?>
                </div>
            </div>
            <hr>
        </div>
        <?= $form->field($model, 'status')->hiddenInput(['value' => $model::STATUS_DRAFT])->label(false) ?>
        <?php endif; ?>
        <?php if ($model->step == 1): ?>
            <div class="row">
                <div class="col-xs-3 text-center" style="display: flex; flex-direction: column; align-items: center;">
                    <?= $form->field($model, 'target_date')->widget(DatePicker::class, [
                        'type' => DatePicker::TYPE_INLINE,
                        'pluginOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'multidate' => false
                        ],
                        'options' => [
                            // you can hide the input by setting the following
                            'style' => 'display:none'
                        ],
                    ])->label('Выберите дату доставки'); ?>
                    <?= Html::button('Подтвердить дату ', [
                        'class' => 'btn btn-success btn-block',
                        'id' => 'confirm-order-date',
                    ]); ?>
                    <!--                <input type="text" name="selected_date" id="selected-date" class="hidden">-->
                </div>
                <div class="col-xs-9" style="min-height: 300px;">
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
            <p>Если сумма заказа менее <?= Yii::$app->formatter->asCurrency($model->buyer->min_order_cost) ?>
                будет добавлена услуга
                доставки <?= Yii::$app->formatter->asCurrency($model->buyer->delivery_cost) ?></p>
            <div class="row">
                <div class="col-xs-6">
                    <?= $this->render('_nomenclature', [
                        'model' => $model,
                        'dataProvider' => $productsDataProvider,
                    ]) ?>
                </div>
                <div class="col-xs-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Укажите временной интервал доставки
                            <small>(Не менее двух часов)</small>
                        </div>
                        <div class="panel-body">
                            <div class="time-dropdown"
                                 style="display: flex; align-items: center; justify-content: space-around;">
                                <?= $form->field($model, 'delivery_time_from')
                                    ->dropDownList($model->buyer->getDeliveryTimeIntervals('from'))->label('С') ?>
                                <?= $form->field($model, 'delivery_time_to')
                                    ->dropDownList($model->buyer->getDeliveryTimeIntervals('to'))->label('ДО') ?>
                            </div>
                            <div class="error-time text-center">
                                <?= $form->field($model, 'error_delivery_time')->hiddenInput()->label(false) ?>
                            </div>
                        </div>
                    </div>
                    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>
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
                <?= $form->field($model, 'step')->hiddenInput()->label(false) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
