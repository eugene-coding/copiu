<?php

use app\models\Settings;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $settings app\models\Settings[] */
/* @var array $result Результат сохранения */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="settings-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            Системные
        </div>
        <div class="panel-body">
            <table class="table table-hover">
                <tbody>
                <?php /** @var Settings $model */
                foreach ($settings['system'] as $model): ?>
                    <tr>
                        <td><?= $model->label; ?></td>
                        <td><?= Html::textInput('keys[' . $model->key . ']', $model->value, [
                                'class' => 'form-control'
                            ]) ?></td>
                    </tr>

                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            Данные организации для работы с АПИ
        </div>
        <div class="panel-body">
            <table class="table table-hover">
                <tbody>
                <?php /** @var Settings $model */
                foreach ($settings['cms'] as $model): ?>
                    <tr>
                        <td><?= $model->label; ?></td>
                        <td><?= Html::textInput('keys[' . $model->key . ']', $model->value, [
                                'class' => 'form-control'
                            ]) ?></td>
                    </tr>

                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <?php if ($result): ?>
        <div class="message text-center">
            <?php if ($result['success']): ?>
                <div class="text-success"><?= $result['data']; ?></div>
            <?php else: ?>
                <div class="text-danger"><?= $result['data']; ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>