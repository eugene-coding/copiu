<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\BulkButtonWidget;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Заказы';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('/js/order_form.js', [
        'depends' => [
            'yii\web\YiiAsset',
            'yii\bootstrap\BootstrapAsset',
        ]
])

?>
<div class="order-index">
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
                            Html::a('<i class="glyphicon glyphicon-plus"></i> Добавить заказ', ['order-create'],
                                [
//                                    'role' => 'modal-remote',
                                    'title' => 'Добавить заказ',
                                    'data-pjax' => 0,
                                    'class' => 'btn btn-default'
                                ]) .
                            Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
                                ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => 'Reset Grid']) .
                            '{toggleData}' .
                            '{export}'
                    ],
                ],
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'panel' => [
                    'type' => 'primary',
                    'heading' => '<i class="glyphicon glyphicon-list"></i> Список заказов',
//                    'before' => '<em>* Resize table columns just like a spreadsheet by dragging the column edges.</em>',
                    'after' => BulkButtonWidget::widget([
                            'buttons' => Html::a('<i class="glyphicon glyphicon-trash"></i>&nbsp; Delete All',
                                ["bulk-delete"],
                                [
                                    "class" => "btn btn-danger btn-xs",
                                    'role' => 'modal-remote-bulk',
                                    'data-confirm' => false,
                                    'data-method' => false,// for overide yii data api
                                    'data-request-method' => 'post',
                                    'data-confirm-title' => 'Are you sure?',
                                    'data-confirm-message' => 'Are you sure want to delete this item'
                                ]),
                        ]) .
                        '<div class="clearfix"></div>',
                ]
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        } ?>
    </div>
</div>

