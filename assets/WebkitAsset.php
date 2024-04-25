<?php

namespace app\assets;

use yii\web\AssetBundle;

class WebkitAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'webkit/css/backend-plugin.min.css',
        'webkit/css/backend.css?v=1.0.1',
        'webkit/vendor/line-awesome/dist/line-awesome/css/line-awesome.min.css',
        'webkit/vendor/remixicon/fonts/remixicon.css',
    ];
    public $js = [
        'webkit/js/backend-bundle.min.js',
        'js/main.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

    public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
}
