<?php

use rcms\contentManager\models\ContentLayout;
use rcms\contentManager\models\ContentLayoutSearch;
use rcms\core\components\ActionColumn;
use yii\bootstrap4\Html;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/* @var $this View */
/* @var $model ContentLayout */
/* @var $searchModel ContentLayoutSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('rcms-contentManager', 'Layouts');

?>

<?= Html::beginTag('div', ['class' => 'card', 'id' => 'panel-cm-index']); ?>

<?= Html::beginTag('div', ['class' => 'card-header']); ?>

<div class="float-right"><?= (new GridView(['dataProvider' => $dataProvider]))->renderSummary() ?></div>
<?= Html::tag('div', Html::tag('h5', $this->title), ['class' => 'card-title']); ?>

<?= Html::endTag('div'); ?>

<?php Pjax::begin(['id' => 'cm-layout-index-pjax', 'timeout' => 5000]); ?>
<?php
$gridViewConfig = [
    'id' => 'content-page-search',
    'filterModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'emptyText' => Yii::t('rcms-contentManager', 'No layout was created yet'),
    'layout' => "{items}\n{pager}",
    'columns' => [
        [
            'class' => ActionColumn::class,
            'header' => Yii::t('rcms-contentManager', 'Actions'),
            'contentOptions' => ['class' => 'd-flex', 'style' => 'border: none;'],
            'template' => '{update} {delete}',
            'buttons' => [
                'update' => function ($url, $model, $key) {
                    return Html::a('', $url, [
                        'class' => 'btn btn-sm btn-info fas fa-pencil-alt mr-1'
                    ]);
                },
                'delete' => function ($url, $model, $key) {
                    return Html::a('', $url, [
                        'class' => 'btn btn-sm btn-danger fas fa-trash-alt',
                        'title' => Yii::t('yii', 'Delete'),
                        'aria-label' => Yii::t('yii', 'Delete'),
                        'data-confirm' => Yii::t('rcms-contentManager', "Are you sure you want to Delete this item?"),
                        'data-method' => 'post',
                    ]);
                },
            ],
            'filter' => Html::beginTag('div', ['class' => 'btn-group']) .
                Html::a('', Url::to(['create']), [
                    'class' => 'btn btn-success fas fa-plus',
                    'title' => Yii::t('rcms-contentManager', 'Create Layout'),
                    'data-pjax' => '0',
                ]) .
                Html::endTag('div')
        ],
        'layout_name',
        'updated_at:relativeTime'
    ]
];
echo GridView::widget($gridViewConfig);
?>
<?php Pjax::end() ?>

<?= Html::endTag('div') ?>
