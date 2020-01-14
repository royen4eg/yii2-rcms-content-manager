<?php

use rcms\contentManager\models\ContentPage;
use rcms\contentManager\models\ContentPageSearch;
use rcms\core\components\ActionColumn;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\bootstrap4\Modal;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/* @var $this View */
/* @var $model ContentPage */
/* @var $searchModel ContentPageSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('rcms-contentManager', 'Static Pages');

?>

<?= Html::beginTag('div', ['class' => 'card', 'id' => 'panel-cm-index']); ?>

<?= Html::beginTag('div', ['class' => 'card-header']); ?>

<div class="float-right"><?= (new GridView(['dataProvider' => $dataProvider]))->renderSummary() ?></div>
<?= Html::tag('div', Html::tag('h5', $this->title), ['class' => 'card-title']); ?>

<?= Html::endTag('div'); ?>

<?php Pjax::begin(['id' => 'cm-index-pjax', 'timeout' => 5000]); ?>
<?php
$gridViewConfig = [
    'id' => 'content-page-search',
    'filterModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'emptyText' => Yii::t('rcms-contentManager', 'No page was created yet'),
    'layout' => "{items}\n{pager}",
    'columns' => ArrayHelper::merge([
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
                    'title' => Yii::t('rcms-contentManager', 'Create Page'),
                    'data-pjax' => '0',
                ]) .
                Html::a('', '#rcms-table-cong-modal', [
                    'class' => 'btn btn-secondary fas fa-cog',
                    'title' => Yii::t('rcms-contentManager', 'Table Settings'),
                    'data-toggle' => 'modal'
                ]) .
                Html::endTag('div')
        ],
    ], $searchModel->gridViewColumns)
];
echo GridView::widget($gridViewConfig);
?>


<?php Modal::begin([
    'title' => Yii::t('rcms-contentManager', 'Table Configuration'),
    'id' => 'rcms-table-cong-modal',
    'size' => Modal::SIZE_SMALL,
]) ?>
<?php $form = ActiveForm::begin(['method' => 'get']); ?>
<?= $form->field($searchModel, 'selectedColumns')->label(false)->checkboxList($searchModel->attributeLabels()) ?>

<div class="form-group">
    <?= Html::submitButton(Yii::t('rcms-contentManager', 'Apply'), ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>
<?php Modal::end() ?>

<?php Pjax::end() ?>

<?= Html::endTag('div') ?>

