<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\PriceCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ценовые категории';
$this->params['breadcrumbs'][] = $this->title;

try {
    $this->registerJsFile('/js/price_categories.js', [
        'depends' => [
            'yii\web\YiiAsset',
            'yii\bootstrap\BootstrapAsset',
        ]
    ]);
} catch (\yii\base\InvalidConfigException $e) {
    echo $e->getMessage();
}

?>
<div class="price-category-index">
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
                            Html::button('<i class="glyphicon glyphicon-sort"></i> Синхронизировать цены для ценовых категорий',
                                [
                                    'class' => 'btn btn-info',
                                    'title' => 'Нажмите, чтобы начать синхронизацию',
                                    'id' => 'sync-price-for-pc',
                                    'data-url' => '/site/get-price-for-price-category?force=1',
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
                    'before' => '<div id="before-panel-message" style="display: none;"></div>',
                    'heading' => '<i class="glyphicon glyphicon-list"></i> Список ценовых категорий',
                    'after' => '<div class="clearfix"></div>',
                ]
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        } ?>
    </div>
</div>

