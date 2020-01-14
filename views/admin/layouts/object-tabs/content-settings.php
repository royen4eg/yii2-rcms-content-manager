<?php

use rcms\contentManager\models\ContentLayout;
use rcms\core\widgets\codemirror\Codemirror;
use rcms\core\widgets\codemirror\CodemirrorAsset;
use rcms\core\widgets\summernote\Summernote;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Tabs;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/* @var $this View */
/* @var $model ContentLayout */
/* @var $form ActiveForm */

echo $form->field($model, 'layout_name');

echo $form->field($model, 'content')->widget(Summernote::class, [
    'clientOptions' => [
        'callbacks' => [
            'onImageUpload' => new JsExpression('rcmsImgUpload')
        ],
    ]
]);

$cmcommon = [
    'theme' => 'monokai',
    'lineNumbers' => true,
    'lineWrapping' => true,
    'matchBrackets' => true,
    'foldGutter' => true,
    'gutters' => ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
];
$tabConf = [
    'id' => 'codemirror-tabs',
    'items' => [
        [
            'active' => true,
            'label' => Yii::t('rcms-contentManager', 'CSS Style'),
            'content' => $form->field($model, 'css_style')->label(false)->widget(Codemirror::class, [
                'assets' => [
                    CodemirrorAsset::THEME_MONOKAI,
                    CodemirrorAsset::MODE_CLIKE,
                    CodemirrorAsset::MODE_XML,
                    CodemirrorAsset::MODE_HTMLMIXED,
                    CodemirrorAsset::MODE_JAVASCRIPT,
                    CodemirrorAsset::MODE_PHP,
                    CodemirrorAsset::MODE_CSS,
                    CodemirrorAsset::ADDON_EDIT_MATCHBRACKETS,
                    CodemirrorAsset::ADDON_HINT_CSS_HINT,
                    CodemirrorAsset::ADDON_FOLD_FOLDCODE,
                    CodemirrorAsset::ADDON_FOLD_FOLDGUTTER,
                    CodemirrorAsset::ADDON_FOLD_BRACE_FOLD,
                    CodemirrorAsset::ADDON_FOLD_XML_FOLD,
                ],
                'settings' => ArrayHelper::merge($cmcommon, ['mode' => 'text/css']),
            ])
        ],
        [
            'label' => Yii::t('rcms-contentManager', 'JS Script'),
            'content' => $form->field($model, 'js_script')->label(false)->widget(Codemirror::class, [
                'settings' => ArrayHelper::merge($cmcommon, ['mode' => 'javascript']),
            ])
        ]
    ],
    'clientEvents' => [
        'shown.bs.tab' => "function(e){ const t = $(e.target).attr('href'); $(t).find('.CodeMirror')[0].CodeMirror.refresh() }"
    ]
];

echo Tabs::widget($tabConf);

$imgPostUrl = Url::to(['file-manager/upload-file']);
$js = <<<JS
function rcmsImgUpload(files) {
    $.each(files, function (index, file) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            data: data,
            type: "POST",
            url: "{$imgPostUrl}",
            cache: false,
            contentType: false,
            processData: false,
            success: function(url) {
                let image = $('<img>').attr('src', url);
                $('#contentlayout-content').summernote("insertNode", image[0]);
            }
        });
    });
}
JS;
$this->registerJS($js, \yii\web\View::POS_READY);