<?php

/* @var $this yii\web\View */

$this->title = 'PHP info';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="php-info">
    <?= phpinfo() ?>
</div>
