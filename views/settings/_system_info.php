<?php

use app\models\Settings;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $settings app\models\Settings[] */
/* @var array $result Результат сохранения */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="settings-form">


    <table class="table table-hover">
        <tbody>
        <?php /** @var Settings $model */
        foreach ($settings as $model): ?>
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
