<?php

use rcms\contentManager\models\ContentPageForm;
use kartik\datetime\DateTimePicker;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model ContentPageForm */
/* @var $form ActiveForm */

$disabledConf = [
    'class' => 'form-control',
    'disabled' => true
];
$datetimeConf = [
    'pluginOptions' => [
        'autoclose' => true,
        'todayHighlight' => true,
        'todayBtn' => true,
        'format' => Yii::$app->formatter->datetimeFormat
    ],
    'convertFormat' => true,
];
?>
<?php if (!$model->isNewRecord): ?>
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="form-group">
                <label class="control-label"><?= $model->getAttributeLabel('created_by') ?></label>
                <?= Html::textInput(null, $model->creator->username, $disabledConf) ?>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="form-group">
                <label class="control-label"><?= $model->getAttributeLabel('updated_by') ?></label>
                <?= Html::textInput(null, $model->updator->username, $disabledConf) ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="form-group">
                <label class="control-label"><?= $model->getAttributeLabel('created_at') ?></label>
                <?= Html::textInput(null, $model->toDatetime($model->created_at), $disabledConf) ?>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="form-group">
                <label class="control-label"><?= $model->getAttributeLabel('updated_at') ?></label>
                <?= Html::textInput(null, $model->toDatetime($model->updated_at), $disabledConf) ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<div class="row">
    <div class="col-sm-6 col-md-3">
        <?= $form->field($model, 'start_publish_date')->widget(DateTimePicker::class, ArrayHelper::merge($datetimeConf, [
            'options' => ['value' => $model->toDatetime($model->start_publish_date)]
        ])) ?>
    </div>
    <div class="col-sm-6 col-md-3">
        <?= $form->field($model, 'end_publish_date')->widget(DateTimePicker::class, ArrayHelper::merge($datetimeConf, [
            'options' => ['value' => $model->toDatetime($model->end_publish_date)]
        ])) ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 col-md-3">
        <?= $form->field($model, 'is_published')->checkbox() ?>
    </div>
</div>

