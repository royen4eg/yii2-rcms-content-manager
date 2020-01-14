<?php
namespace rcms\contentManager\assets;

use yii\bootstrap4\BootstrapPluginAsset;
use yii\web\AssetBundle;

/**
 * RCMS Front Page assets asset bundle
 *
 * @author Andrii Borodin
 * @since 0.1
 */
class FrontPageAssets extends AssetBundle
{
    public $css = [
        'https://use.fontawesome.com/releases/v5.11.1/css/all.css',
    ];
    public $js = [
    ];

    public $depends = [
        'yii\web\YiiAsset',
        BootstrapPluginAsset::class
    ];
}