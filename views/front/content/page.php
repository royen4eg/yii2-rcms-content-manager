<?php

use rcms\contentManager\models\ContentPage;
use yii\helpers\ArrayHelper;
use yii\web\View;

/* @var $this View */
/* @var $page ContentPage */

if($page->content_layout_id != $page::LAYOUT_DEFAULT ){
    $layout = $page->contentLayout;
    $page->content = str_replace($layout::CONTENT_REPLACEMENT, $page->content, $layout->content);

    if (!empty($layout->css_style)) {
        $this->registerCss($layout->css_style);
    }
    if (!empty($layout->js_script)) {
        $this->registerJS($layout->js_script, View::POS_END);
    }

    if (is_array($layout->metadata) && !empty($layout->metadata)) {
        foreach ($layout->metadata as $meta) {
            if(isset($meta['metaAttributes']) && count($meta['metaAttributes']) > 0){
                $metaOptions = ArrayHelper::map($meta['metaAttributes'], 'attribute','value');
                $this->registerMetaTag($metaOptions);
            }
        }
    }
}

if (!empty($page->css_style)) {
    $this->registerCss($page->css_style);
}

if (is_array($page->metadata) && !empty($page->metadata)) {
    foreach ($page->metadata as $meta) {
        if(isset($meta['metaAttributes']) && count($meta['metaAttributes']) > 0){
            $metaOptions = ArrayHelper::map($meta['metaAttributes'], 'attribute','value');
            $this->registerMetaTag($metaOptions);
        }
    }
}
?>
<div class="content" id="rcms-page-content">
    <?= $page->content ?>
</div>
<?php if (!empty($page->js_script)) {
    $this->registerJS($page->js_script, View::POS_END);
} ?>
