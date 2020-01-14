<?php

use rcms\contentManager\models\ContentLayout;
use rcms\core\base\BaseAdminController as BAC;
use rcms\core\widgets\ControlPanel;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Tabs;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model ContentLayout */

$this->title = Yii::t('rcms-contentManager', $model->isNewRecord ? 'Create Layout' : 'Update Layout')
    . ($model->isNewRecord ? '' : ": " . $model->layout_name);

$form = ActiveForm::begin(['id' => 'contentManagerForm']);

$buttons = [
    [
        'type' => ControlPanel::TYPE_SUBMIT,
        'label' => Yii::t('rcms-core', $model->isNewRecord ? 'Create' : 'Save'),
        'options' => ['class' => 'btn btn-success mr-1']
    ]
];
if (!$model->isNewRecord) {
    $buttons[] = [
        'type' => ControlPanel::TYPE_SUBMIT,
        'label' => Yii::t('rcms-core', 'Save And Exit'),
        'options' => [
            'class' => 'btn btn-outline-secondary mr-1',
            'formaction' => Url::to([BAC::ACTION_UPDATE, 'exit' => true, 'id' => $model->content_layout_id])
        ]
    ];
    $buttons[] = [
        'type' => ControlPanel::TYPE_SUBMIT,
        'label' => Yii::t('rcms-core', 'Create a Copy'),
        'options' => [
            'class' => 'btn btn-outline-secondary mr-1',
            'formaction' => Url::to([BAC::ACTION_CREATE, 'exit' => false])
        ]
    ];

}

$buttons[] = [
    'label' => Yii::t('rcms-core', 'Cancel'),
    'options' => [
        'class' => 'btn btn-danger'
    ],
    'url' => Url::to(['index'])
];


echo ControlPanel::widget([
    'title' => $this->title,
    'leftItems' => $buttons
]);


$tabConf = [
    'id' => 'cm-form-tabs',
    'options' => ['style' => 'margin-bottom: 10px;'],
    'items' => [
        [
            'active' => true,
            'label' => Yii::t('rcms-contentManager', 'Content Settings'),
            'content' => $this->render('object-tabs/content-settings', ['model' => $model, 'form' => $form])
        ],
        [
            'label' => Yii::t('rcms-contentManager', 'Advanced Settings'),
            'content' => $this->render('object-tabs/advanced-settings', ['model' => $model, 'form' => $form])
        ],
        [
            'label' => Yii::t('rcms-contentManager', 'Metadata Settings'),
            'content' => $this->render('object-tabs/meta-settings', ['model' => $model, 'form' => $form]),
        ],
    ],
];
echo Tabs::widget($tabConf);

ActiveForm::end();
