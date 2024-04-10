<?php
/**
 * @var \app\models\UploadForm $model
 */
use yii\widgets\ActiveForm;
use app\models\Settings;

$this->title = 'Загрузка прайс-листа';
$file = Settings::getValueByKey(Settings::KEY_PRICE_LIST);
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

<?= $form->field($model, 'imageFile')->fileInput() ?>

    <button>Загрузить</button>

<?php ActiveForm::end() ?>

<div class="row">
    <div class="col-md-12">
        <br>
        <br>
        <br>
        Скачать текущий файл: <?= \yii\helpers\Html::a($file, '/uploads/' . $file ) ?>
    </div>
</div>
