<?php

use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */


app\assets\WebkitAsset::register($this);

$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class=" color-light ">
<?php $this->beginBody() ?>
<?php CrudAsset::register($this);?>

<div class="wrapper">

    <?= $this->render('left-new.php') ?>

    <?= $this->render('content-new.php', ['content' => $content]) ?>

</div>

<?php $this->endBody() ?>
<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>
<?php Modal::end(); ?>
</body>
</html>
<?php $this->endPage() ?>
