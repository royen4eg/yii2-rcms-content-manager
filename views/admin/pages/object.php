<?php

use rcms\contentManager\models\ContentPageForm;
use rcms\core\base\BaseAdminController as BAC;
use rcms\core\widgets\ControlPanel;
use yii\bootstrap4\Html;
use yii\bootstrap4\Modal;
use yii\bootstrap4\Tabs;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;
use yii\bootstrap4\ActiveForm;

/* @var $this View */
/* @var $model ContentPageForm */

$this->title = Yii::t('rcms-contentManager', $model->isNewRecord ? 'Create Page' : 'Update Page')
    . ($model->isNewRecord ? '' : ": " . $model->title);

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
        'label' => Yii::t('rcms-contentManager', 'Save And Exit'),
        'options' => [
            'class' => 'btn btn-outline-secondary mr-1',
            'formaction' => Url::to([BAC::ACTION_UPDATE, 'exit' => true, 'id' => $model->content_page_id])
        ]
    ];
    $buttons[] = [
        'type' => ControlPanel::TYPE_SUBMIT,
        'label' => Yii::t('rcms-contentManager', 'Create a Copy'),
        'options' => [
            'class' => 'btn btn-outline-secondary mr-1',
            'formaction' => Url::to([BAC::ACTION_CREATE, 'exit' => false])
        ]
    ];
    $versions = Html::beginTag('div', ['class' => 'btn-group mr-1']);
    $versions .= Html::button(
        Yii::t('rcms-contentManager', 'Versions') . ' ' . ($_GET['revision'] ?? '') . '<span class="caret"></span>', [
        'class' => 'btn btn-outline-secondary dropdown-toggle',
        'data-toggle' => 'dropdown'
    ]);
    $revisions = ArrayHelper::getColumn($model->contentRevisions, 'revision_number');
    $versions .= Html::ul(array_merge(['latest', ''], $revisions), [
        'class' => 'dropdown-menu',
        'item' => function ($item, $index) use ($model) {
            switch ($item) {
                case 'latest':
                    return Html::a(Yii::t('rcms-contentManager', 'Latest'),
                        Url::to([BAC::ACTION_UPDATE, 'id' => $model->content_page_id]),
                        ['class' => 'dropdown-item']);
                case '':
                    return '<div class="dropdown-divider"></div>';
                default:
                    return Html::a($item,
                        Url::to([BAC::ACTION_UPDATE, 'id' => $model->content_page_id, 'revision' => $item]),
                        ['class' => 'dropdown-item']);
            }
        }
    ]);
    $versions .= Html::endTag('div');
    $buttons[] .= $versions;

    if ($model->type === $model::TYPE_LINK) {
        $buttons[] = [
            'label' => Yii::t('rcms-contentManager', 'Go To Page'),
            'options' => [
                'class' => 'btn btn-outline-info mr-1',
                'target' => '_blank'
            ],
            'url' => $model->url
        ];
    } elseif ($model->type !== $model::TYPE_MENU_ITEM) {
        $buttons[] = [
            'label' => Yii::t('rcms-contentManager', 'Go To Page'),
            'options' => [
                'class' => 'btn btn-outline-info mr-1',
                'target' => '_blank'
            ],
            'url' => Url::to("/$model->url")
        ];
    }

}

$buttons[] = [
    'label' => Yii::t('rcms-contentManager', 'Cancel'),
    'options' => [
        'class' => 'btn btn-danger'
    ],
    'url' => Url::to(['index'])
];


echo ControlPanel::widget([
    'title' => $this->title,
    'leftItems' => $buttons,
    'rightItems' => [
        [
            'label' => Html::tag('i', '', ['class' => 'fas fa-info-circle']),
            'options' => [
                'class' => 'btn btn-info float-right',
                'title' => Yii::t('rcms-contentManager', 'Information'),
                'data-toggle' => 'modal'
            ],
            'url' => '#rcms-cm-modal-info'
        ]
    ]
]);

$tabConf = [
    'id' => 'cm-form-tabs',
    'options' => ['style' => 'margin-bottom: 10px;'],
    'items' => [
        [
            'active' => true,
            'label' => Yii::t('rcms-contentManager', 'Content Settings'),
            'content' => $this->render('object-tabs/content-settings', ['model' => $model, 'form' => $form]),
        ],
        [
            'label' => Yii::t('rcms-contentManager', 'Advanced Settings'),
            'content' => $this->render('object-tabs/advanced-settings', ['model' => $model, 'form' => $form]),
        ],
        [
            'label' => Yii::t('rcms-contentManager', 'Publishing Settings'),
            'content' => $this->render('object-tabs/publishing-settings', ['model' => $model, 'form' => $form]),
        ],
        [
            'label' => Yii::t('rcms-contentManager', 'Metadata Settings'),
            'content' => $this->render('object-tabs/meta-settings', ['model' => $model, 'form' => $form]),
        ],
    ],
];

echo Tabs::widget($tabConf);

ActiveForm::end();

Modal::begin([
    'size' => Modal::SIZE_EXTRA_LARGE,
    'id' => 'rcms-cm-modal-info',
    'title' => Yii::t('rcms-contentManager', 'Information'),
]);
echo $this->render('_info-modal');
Modal::end();
