<?php


/* @var $productsDataProvider \yii\data\ActiveDataProvider */

use app\models\OrderBlank;

?>

<div class="col-md-12">
    <div>
        <!-- Навигационные вкладки -->
        <ul class="nav nav-tabs" role="tablist">
            <?php
            //                            Yii::warning($productsDataProvider->getModels());
            foreach ($productsDataProvider->getModels() as $tab_name => $products): ?>
                <?php $tab_model = OrderBlank::findOne(['number' => $tab_name]); ?>
                <li role="presentation">
                    <a href="#tab-<?= $tab_model->id ?>" aria-controls="<?= $tab_model->id; ?>"
                       role="tab" data-toggle="tab">
                        <?= $tab_model->number; ?>
                    </a>
                </li>
            <?php endforeach; ?>

        </ul>

        <!-- Вкладки панелей -->
        <div class="tab-content">
            <?php foreach ($productsDataProvider->getModels() as $tab_name => $products): ?>
                <?php $tab_model = OrderBlank::findOne(['number' => $tab_name]); ?>
                <div role="tabpanel" class="tab-pane" id="tab-<?= $tab_model->id ?>">
                    <div class="row">
                        <div class="col-sm-12 tab-nomenclature-list">
                            <?= $this->render('_nomenclature', [
                                'model' => $model,
                                'dataProvider' => $products,
                            ]) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>
