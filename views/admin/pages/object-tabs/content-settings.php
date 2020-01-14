<?php

use rcms\contentManager\components\ContentParser;
use rcms\contentManager\models\ContentPageForm;
use rcms\core\models\Dictionary;
use rcms\core\widgets\codemirror\Codemirror;
use rcms\core\widgets\codemirror\CodemirrorAsset;
use rcms\core\widgets\summernote\Summernote;
use yii\bootstrap4\Tabs;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\bootstrap4\ActiveForm;

/* @var $this View */
/* @var $model ContentPageForm */
/* @var $form ActiveForm */

$langs = Dictionary::getAvailLanguages();

$cpCommandsArray = array_keys(ContentParser::getCallableFunctions());
$cpCommandsArray = array_map(function ($item){
    return '{{' . $item . '}}';
}, $cpCommandsArray);
$cpCommandsArray[] = '{{@define param = \'var\'}}';
$cpCommandsArray[] = '{{@for arrayName as i, v}} {{@endfor}}';
$cpCommandsArray[] = '{{@endfor}}';

?>
<div class="row">
    <div class="col-sm">
        <?= $form->field($model, 'title') ?>
    </div>
    <div class="col-sm">
        <?= $form->field($model, 'url') ?>
    </div>
    <div class="col-sm">
        <?= $form->field($model, 'language')->dropDownList(array_combine($langs,$langs)) ?>
    </div>
</div>


<?= $form->field($model, 'content')->widget(Summernote::class, [
        'clientOptions' => [
            'callbacks' => [
                'onImageUpload' => new JsExpression('rcmsImgUpload')
            ],
            'hint' => [
                [
                    'match' =>  new JsExpression('/((\$|{{2}).{1,})$/'),
                    'words' => array_merge(['$img:', '$file:'], $cpCommandsArray),
                    'search' => new JsExpression("function(keyword, callback){ console.log(keyword); callback($.grep(this.words, function (item) {return item.indexOf(keyword) === 0;})); }")
                ],
                [
                    'match' =>  new JsExpression('/\$img:([\w_-]+)$/'),
                    'search' => new JsExpression("function(keyword, callback){ callback($.grep(rcmsCmImgList, function (item) {return item.indexOf(keyword) === 0;})); }"),
                    'content' => new JsExpression("function(item){ const u = rcmsCmImgListUrls[item]; if(u) { return $('<img>').attr('src', u)[0]; } return '' }"),
                ],
                [
                    'match' =>  new JsExpression('/\$file:([\w_-]+)$/'),
                    'search' => new JsExpression("function(keyword, callback){ callback($.grep(rcmsCmFileList, function (item) {return item.indexOf(keyword) === 0;})); }"),
                    'content' => new JsExpression("function(item){ const u = rcmsCmFileListUrls[item]; if(u) { return $('<a>').attr('href', u).html('Download Link')[0]; } return '' }"),
                ],
            ]
        ]
]) ?>

<?php
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

$imgListUrl = Url::to(['file-manager/get-img-list']);
$fileListUrl = Url::to(['file-manager/get-file-list']);
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
                insertImgToContent(url);
            }
        });
    });
}

$.ajax({
  url: '{$imgListUrl}',
  async: false 
}).then(function(data) {
  window.rcmsCmImgListUrls = data; 
  window.rcmsCmImgList = Object.keys(data);
});

$.ajax({
  url: '{$fileListUrl}',
  async: false 
}).then(function(data) {
  window.rcmsCmFileListUrls = data; 
  window.rcmsCmFileList = Object.keys(data);
});

$.ajax({
  url: '{$fileListUrl}',
  async: false 
}).then(function(data) {
  window.rcmsCmFileListUrls = data; 
  window.rcmsCmFileList = Object.keys(data);
});

function insertImgToContent(url) {
    if(url){
        let image = $('<img>').attr('src', url);
        $('#contentpageform-content').summernote("insertNode", image[0]);
    }
}

function slugify(text) {
    return text.toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-');
}

$('#contentpageform-title').on('change', function() {
    let tgt = $('#contentpageform-url');
    if(tgt.val() === ''){
        tgt.val(slugify($(this).val()));
    }
});
JS;
$this->registerJS($js, \yii\web\View::POS_READY);
?>
