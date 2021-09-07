<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset;
use johnitvn\ajaxcrud\BulkButtonWidget;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\OrderDraftSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Черновики заказов';
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="order-draft-index">
    <div id="ajaxCrudDatatable">
        <?php
        try {
            echo GridView::widget([
                'id' => 'crud-datatable',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax' => true,
                'columns' => require(__DIR__ . '/_columns.php'),
                'toolbar' => [
                    [
                        'content' =>
                            Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create'],
                                [
//                                    'role' => 'modal-remote',
                                    'title' => 'Добавить черновик',
                                    'class' => 'btn btn-default',
                                    'data-pjax' => 0
                                ]) .
                            Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
                                ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => 'Обновить']) .
                            '{toggleData}' .
                            '{export}'
                    ],
                ],
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'panel' => [
                    'type' => 'primary',
                    'heading' => '<i class="glyphicon glyphicon-list"></i> Список черновиков '
                        . Html::a('<span class="glyphicon glyphicon-question-sign"></span>',
                            ['/order-draft/help'], [
                                'role' => 'modal-remote',
                                'title' => 'Описание работы'
                            ]),
//                    'before' => '<em>* Resize table columns just like a spreadsheet by dragging the column edges.</em>',
                    'after' => '<div class="clearfix"></div>',
                ]
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        } ?>
    </div>
</div>
<?php Modal::begin([
    "id" => "ajaxCrudModal",
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>
