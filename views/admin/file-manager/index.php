<?php

use rcms\contentManager\models\ContentFileStorage;
use rcms\contentManager\models\ContentFileStorageSearch;
use rcms\core\components\ActionColumn;
use kartik\file\FileInput;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\bootstrap4\Modal;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/* @var $this View */
/* @var $model ContentFileStorage */
/* @var $searchModel ContentFileStorageSearch */
/* @var $dataProvider ActiveDataProvider */

/* @todo Move zoom CSS to separate asset and fix size of zoom preview */

$this->title = Yii::t('rcms-contentManager', 'File Manager');

?>

<?= Html::beginTag('div', ['class' => 'card', 'id' => 'panel-cm-index']); ?>

<?= Html::beginTag('div', ['class' => 'card-header']); ?>
<div class="float-right"><?= (new GridView(['dataProvider' => $dataProvider]))->renderSummary() ?></div>
<?= Html::tag('div', Html::tag('h5', $this->title), ['class' => 'card-title']); ?>
<?= Html::endTag('div'); ?>
<style type="text/css">
    .preview-icon:hover + .zoomicon {
        width: auto;
        height: auto;
        max-width: 256px;
        max-height: 256px;
        margin-top: -28px;
        border: 2px solid black;
        z-index: 1;
    }
    .zoomicon {
        position: absolute;
        width: 0;
        height: 0;
        margin-left: 32px;
        transition: width .2s ease, height .2s ease, margin .2s ease;
    }
</style>
<?php Pjax::begin(['id' => 'cm-index-pjax', 'timeout' => 5000]); ?>

<?php
$gridViewConfig = [
    'id' => 'content-page-search',
    'filterModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'emptyText' => Yii::t('rcms-contentManager', 'File Storage is Empty'),
    'tableOptions' => [ 'class' => 'table table-striped' ],
    'layout' => "{items}\n{pager}",
    'columns' => [
        [
            'class' => ActionColumn::class,
            'header' => Yii::t('rcms-contentManager', 'Actions'),
            'contentOptions' => ['class' => 'd-flex'],
            'template' => '{delete} {download}',
            'buttons' => [
                'delete' => function ($url, $model, $key) {
                    return Html::a('', $url, [
                        'class' => 'btn btn-sm btn-danger fas fa-trash-alt',
                        'title' => Yii::t('yii', 'Delete'),
                        'aria-label' => Yii::t('yii', 'Delete'),
                        'data-confirm' => Yii::t('rcms-contentManager', "Are you sure you want to Delete this item?"),
                        'data-method' => 'post',
                    ]);
                },
                'download' => function ($url, $model, $key) {
                    $url = Url::toRoute(['/file-manager/get-file', 'n' => $model->file_hash, 'd' => true]);
                    return Html::a('', $url, [
                        'class' => 'btn btn-sm btn-info fas fa-file-download ml-1',
                        'title' => Yii::t('rcms-contentManager', 'Download'),
                        'aria-label' => Yii::t('rcms-contentManager', 'Download'),
                        'target' => '_blank',
                        'data-pjax' => '0',
                    ]);
                },
            ],
            'filter' => Html::beginTag('div', ['class' => 'btn-group']) .
                Html::a('', '#rcms-upload-file-modal', [
                    'class' => 'btn btn-success fas fa-plus',
                    'title' => Yii::t('rcms-contentManager', 'Upload Files'),
                    'data-toggle' => 'modal',
                    'data-pjax' => '0',
                ]) .
                Html::endTag('div')
        ],
        [
            'attribute' => 'name',
            'format' => 'raw',
            'value' => function (ContentFileStorage $model) {
                $url = Url::toRoute(['/file-manager/get-file', 'n' => $model->file_hash]);
                return ContentFileStorageSearch::getTypeIcon($model->type, $url) . ' ' .
                    Html::a($model->name, $url, ['target' => '_blank', 'data-pjax' => '0']);
            }
        ],
        'ext',
        'size:shortSize',
        'is_available:boolean',
        'created_by',
        'created_at:relativeTime'
    ]
];
echo GridView::widget($gridViewConfig);
?>

<?php Pjax::end() ?>

<?= Html::endTag('div') ?>

<?php
Modal::begin([
    'id' => 'rcms-upload-file-modal',
    'title' => Yii::t('rcms-contentManager', 'Upload Files'),
    'size' => Modal::SIZE_EXTRA_LARGE,
]);

$form = ActiveForm::begin([
    'action' => Url::to(['upload-files']),
    'options'=>['enctype'=>'multipart/form-data']
]);

echo FileInput::widget(['name'=>'file[]', 'options' => ['multiple' => true]]);

ActiveForm::end();

Modal::end();
