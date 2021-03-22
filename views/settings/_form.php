<?php

use app\models\Account;
use app\models\Department;
use app\models\Settings;
use app\models\Store;
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
            Данные организации
        </div>
        <div class="panel-body">
            <table class="table table-hover">
                <tbody>
                <?php /** @var Settings $model */
                foreach ($settings['cms'] as $model): ?>
                    <tr>
                        <td><?= $model->label; ?></td>
                        <td><?php
                            if (mb_strpos($model->key, 'time')) {
                                echo Html::input('time', 'keys[' . $model->key . ']', $model->value, [
                                    'class' => 'form-control',
                                    'title' => $model->description ?: '',
                                ]);
                            } elseif ($model->key == 'department_outer_id') {
                                echo Html::dropDownList('keys[' . $model->key . ']', $model->value,
                                    Department::getList(), [
                                        'class' => 'form-control',
                                        'title' => $model->description ?: '',
                                        'prompt' => 'Выберите отдел',
                                    ]);
                            } elseif ($model->key == 'store_outer_id') {
                                echo Html::dropDownList('keys[' . $model->key . ']', $model->value,
                                    Store::getList(), [
                                        'class' => 'form-control',
                                        'title' => $model->description ?: '',
                                        'prompt' => 'Выберите Склад',
                                    ]);
                            } elseif ($model->key == 'invoice_outer_id') {
                                echo Html::dropDownList('keys[' . $model->key . ']', $model->value,
                                    Account::getList(), [
                                        'class' => 'form-control',
                                        'title' => $model->description ?: '',
                                        'prompt' => 'Выберите аккаунт',
                                    ]);
                            } else {
                                echo Html::textInput('keys[' . $model->key . ']', $model->value, [
                                    'class' => 'form-control',
                                    'title' => $model->description ?: '',
                                ]);
                            }

                            ?></td>
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
